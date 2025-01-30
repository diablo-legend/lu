<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$conn = connectDB();
$query = "SELECT a.*, u.username as author_name 
          FROM articles a 
          LEFT JOIN users u ON a.author_id = u.id 
          ORDER BY a.created_at DESC 
          LIMIT 10";

$result = $conn->query($query);
$articles = [];

while ($row = $result->fetch_assoc()) {
    $articles[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'excerpt' => mb_substr(strip_tags($row['content']), 0, 150) . '...',
        'image_url' => $row['image_url'],
        'author' => $row['author_name'],
        'views' => $row['views'],
        'likes' => $row['likes'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode($articles);
$conn->close();