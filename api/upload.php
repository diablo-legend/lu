<?php
require_once '../config/database.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

$type = $_POST['type'] ?? '';
$conn = connectDB();

switch ($type) {
    case 'video':
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $videoType = $_POST['video_type'] ?? 'full';
        
        if (empty($title) || empty($_FILES['video'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Не все поля заполнены']);
            exit;
        }
        
        $file = $_FILES['video'];
        $allowedTypes = ['video/mp4', 'video/avi', 'video/x-matroska'];
        $maxSize = 52428800; // 50 МБ
        
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Неподдерживаемый формат видео']);
            exit;
        }
        
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['error' => 'Размер файла превышает 50 МБ']);
            exit;
        }
        
        $uploadDir = '../uploads/videos/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $stmt = $conn->prepare("INSERT INTO videos (title, description, video_url, type, author_id) VALUES (?, ?, ?, ?, ?)");
            $videoUrl = 'uploads/videos/' . $fileName;
            $stmt->bind_param('ssssi', $title, $description, $videoUrl, $videoType, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'video_id' => $conn->insert_id]);
            } else {
                unlink($filePath);
                http_response_code(500);
                echo json_encode(['error' => 'Ошибка при сохранении в базу данных']);
            }
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка при загрузке файла']);
        }
        break;
        
    case 'article':
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        
        if (empty($title) || empty($content)) {
            http_response_code(400);
            echo json_encode(['error' => 'Не все поля заполнены']);
            exit;
        }
        
        // Загрузка изображения для статьи
        $imageUrl = '';
        if (!empty($_FILES['image'])) {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (in_array($file['type'], $allowedTypes)) {
                $uploadDir = '../uploads/images/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = uniqid() . '_' . basename($file['name']);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $imageUrl = 'uploads/images/' . $fileName;
                }
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO articles (title, content, image_url, author_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sssi', $title, $content, $imageUrl, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'article_id' => $conn->insert_id]);
        } else {
            if (!empty($imageUrl)) {
                unlink('../' . $imageUrl);
            }
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка при сохранении в базу данных']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Неверный тип загрузки']);
}

$conn->close();