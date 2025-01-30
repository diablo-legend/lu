<?php
require_once 'config/database.php';
require_once 'api/auth.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: videos.php');
    exit;
}

$conn = connectDB();
$stmt = $conn->prepare("
    SELECT v.*, u.username as author_name,
    (SELECT COUNT(*) FROM comments WHERE content_type = 'video' AND content_id = v.id) as comments_count 
    FROM videos v 
    LEFT JOIN users u ON v.author_id = u.id 
    WHERE v.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$video = $stmt->get_result()->fetch_assoc();

if (!$video) {
    header('Location: videos.php');
    exit;
}

// Получаем комментарии
$stmt = $conn->prepare("
    SELECT c.*, u.username, u.class 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.content_type = 'video' AND c.content_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->bind_param('i', $id);
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($video['title']); ?> - Лицейск News</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main">
        <div class="container">
            <article class="video fade-in" id="video-<?php echo $video['id']; ?>" data-track-view data-type="video" data-id="<?php echo $video['id']; ?>">
                <h1 class="video__title"><?php echo htmlspecialchars($video['title']); ?></h1>
                
                <div class="video__player">
                    <video controls class="video__content" poster="<?php echo $video['thumbnail_url'] ?? ''; ?>">
                        <source src="<?php echo htmlspecialchars($video['video_url']); ?>" type="video/mp4">
                        Ваш браузер не поддерживает видео.
                    </video>
                </div>
                
                <div class="video__meta">
                    <span class="video__author">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($video['author_name']); ?>
                    </span>
                    <span class="video__date">
                        <i class="fas fa-calendar"></i> 
                        <?php echo date('d.m.Y', strtotime($video['created_at'])); ?>
                    </span>
                    <div class="video__stats">
                        <span class="video__stat" id="views-count-<?php echo $video['id']; ?>">
                            <i class="fas fa-eye"></i> <?php echo $video['views']; ?>
                        </span>
                        <button class="video__stat" onclick="handleReaction('video', 'like', <?php echo $video['id']; ?>)">
                            <i class="fas fa-thumbs-up"></i> 
                            <span id="like-count-<?php echo $video['id']; ?>"><?php echo $video['likes']; ?></span>
                        </button>
                        <button class="video__stat" onclick="handleReaction('video', 'dislike', <?php echo $video['id']; ?>)">
                            <i class="fas fa-thumbs-down"></i> 
                            <span id="dislike-count-<?php echo $video['id']; ?>"><?php echo $video['dislikes']; ?></span>
                        </button>
                    </div>
                </div>

                <div class="video__description">
                    <?php echo nl2br(htmlspecialchars($video['description'])); ?>
                </div>

                <section class="comments">
                    <h2 class="comments__title">Комментарии (<?php echo $video['comments_count']; ?>)</h2>
                    
                    <?php if (isAuthenticated()): ?>
                        <form class="comment-form" data-type="video" data-id="<?php echo $video['id']; ?>">
                            <textarea class="comment-form__input" name="content" placeholder="Ваш комментарий..." required></textarea>
                            <button type="submit" class="button button--primary">Отправить</button>
                        </form>
                    <?php else: ?>
                        <p class="comments__login-message">
                            <a href="login.php">Войдите</a>, чтобы оставить комментарий
                        </p>
                    <?php endif; ?>

                    <div class="comments__list" id="comments-<?php echo $video['id']; ?>">
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="comment">
                                <div class="comment__header">
                                    <span class="comment__author"><?php echo htmlspecialchars($comment['username']); ?></span>
                                    <?php if ($comment['class']): ?>
                                        <span class="comment__class"><?php echo htmlspecialchars($comment['class']); ?></span>
                                    <?php endif; ?>
                                    <span class="comment__date">
                                        <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="comment__content">
                                    <?php echo htmlspecialchars($comment['content']); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
            </article>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>