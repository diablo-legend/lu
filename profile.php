<?php
require_once 'config/database.php';
require_once 'api/auth.php';

requireAuth();

$conn = connectDB();
$user_id = $_SESSION['user_id'];

// Получаем информацию о пользователе
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Получаем статистику активности
$stats = [
    'comments' => $conn->query("SELECT COUNT(*) as count FROM comments WHERE user_id = $user_id")->fetch_assoc()['count'],
    'likes' => $conn->query("SELECT COUNT(*) as count FROM user_reactions WHERE user_id = $user_id AND reaction_type = 'like'")->fetch_assoc()['count'],
    'views' => $conn->query("SELECT COUNT(*) as count FROM user_views WHERE user_id = $user_id")->fetch_assoc()['count']
];

// Последние комментарии пользователя
$comments = $conn->query("
    SELECT c.*, 
           CASE c.content_type 
               WHEN 'article' THEN a.title 
               WHEN 'video' THEN v.title 
           END as content_title,
           c.content_type
    FROM comments c
    LEFT JOIN articles a ON c.content_type = 'article' AND c.content_id = a.id
    LEFT JOIN videos v ON c.content_type = 'video' AND c.content_id = v.id
    WHERE c.user_id = $user_id
    ORDER BY c.created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль - Лицейск News</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main">
        <div class="container">
            <div class="profile fade-in">
                <h1 class="section-title">Профиль пользователя</h1>
                
                <div class="profile__info">
                    <div class="profile__header">
                        <h2 class="profile__name"><?php echo htmlspecialchars($user['username']); ?></h2>
                        <?php if ($user['class']): ?>
                            <span class="profile__class"><?php echo htmlspecialchars($user['class']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile__stats">
                        <div class="profile__stat">
                            <i class="fas fa-comment"></i>
                            <span class="profile__stat-value"><?php echo $stats['comments']; ?></span>
                            <span class="profile__stat-label">комментариев</span>
                        </div>
                        <div class="profile__stat">
                            <i class="fas fa-thumbs-up"></i>
                            <span class="profile__stat-value"><?php echo $stats['likes']; ?></span>
                            <span class="profile__stat-label">лайков</span>
                        </div>
                        <div class="profile__stat">
                            <i class="fas fa-eye"></i>
                            <span class="profile__stat-value"><?php echo $stats['views']; ?></span>
                            <span class="profile__stat-label">просмотров</span>
                        </div>
                    </div>
                </div>

                <section class="profile__section">
                    <h3 class="profile__section-title">Последние комментарии</h3>
                    <div class="profile__comments">
                        <?php if ($comments->num_rows > 0): ?>
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                                <div class="profile__comment">
                                    <div class="profile__comment-header">
                                        <a href="<?php echo $comment['content_type']; ?>.php?id=<?php echo $comment['content_id']; ?>" 
                                           class="profile__comment-title">
                                            <?php echo htmlspecialchars($comment['content_title']); ?>
                                        </a>
                                        <span class="profile__comment-date">
                                            <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="profile__comment-content">
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="profile__empty">У вас пока нет комментариев</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>