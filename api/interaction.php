<?php
require_once '../config/database.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

$type = $_POST['type'] ?? '';
$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$conn = connectDB();

switch ($action) {
    case 'like':
    case 'dislike':
        $table = ($type === 'article') ? 'articles' : 'videos';
        $field = ($action === 'like') ? 'likes' : 'dislikes';
        
        // Проверяем, не ставил ли пользователь уже лайк/дизлайк
        $stmt = $conn->prepare("SELECT id FROM user_reactions WHERE user_id = ? AND content_type = ? AND content_id = ? AND reaction_type = ?");
        $stmt->bind_param('isis', $user_id, $type, $id, $action);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Добавляем реакцию
            $stmt = $conn->prepare("INSERT INTO user_reactions (user_id, content_type, content_id, reaction_type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isis', $user_id, $type, $id, $action);
            $stmt->execute();
            
            // Обновляем счетчик
            $stmt = $conn->prepare("UPDATE $table SET $field = $field + 1 WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Реакция добавлена']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Вы уже поставили реакцию']);
        }
        break;
        
    case 'comment':
        $content = $_POST['content'] ?? '';
        
        if (empty($content)) {
            http_response_code(400);
            echo json_encode(['error' => 'Комментарий не может быть пустым']);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO comments (content, user_id, content_type, content_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('sisi', $content, $user_id, $type, $id);
        
        if ($stmt->execute()) {
            // Получаем информацию о пользователе для ответа
            $stmt = $conn->prepare("SELECT username, class FROM users WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'comment' => [
                    'content' => $content,
                    'username' => $user['username'],
                    'class' => $user['class'],
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Ошибка при добавлении комментария']);
        }
        break;
        
    case 'view':
        $table = ($type === 'article') ? 'articles' : 'videos';
        
        // Проверяем, не засчитан ли уже просмотр
        $stmt = $conn->prepare("SELECT id FROM user_views WHERE user_id = ? AND content_type = ? AND content_id = ?");
        $stmt->bind_param('isi', $user_id, $type, $id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            // Добавляем запись о просмотре
            $stmt = $conn->prepare("INSERT INTO user_views (user_id, content_type, content_id) VALUES (?, ?, ?)");
            $stmt->bind_param('isi', $user_id, $type, $id);
            $stmt->execute();
            
            // Обновляем счетчик просмотров
            $stmt = $conn->prepare("UPDATE $table SET views = views + 1 WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Просмотр уже засчитан']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Неверное действие']);
}

$conn->close();