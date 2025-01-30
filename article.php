<?php
require_once 'config/database.php';
require_once 'api/auth.php';

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: articles.php');
    exit;
}

$conn = connectDB();
$stmt = $conn->prepare("
    SELECT a.*, u.username as author_name, 
    (SELECT COUNT(*) FROM comments WHERE content_type = 'article' AND content_id = a.id) as comments_count 
    FROM articles a 
    LEFT JOIN users u ON a.author_id = u.id 
    WHERE a.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

if (!$article) {
    header('Location: articles.php');
    exit;
}

// Получаем комментарии
$stmt = $conn->prepare("
    SELECT c.*, u.username, u.class 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.content_type = 'article' AND c.content_id = ? 
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
    <title><?php echo htmlspecialchars($article['title']); ?> - Лицейск News</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main">
        <div class="container">
            <article class="article fade-in" id="article-<?php echo $article['id']; ?>" data-track-view data-type="article" data-id="<?php echo $article['id']; ?>">
                <h1 class="article__title"><?php echo htmlspecialchars($article['title']); ?></h1>
                
                <?php if ($article['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                         class="article__image">
                <?php endif; ?>
                
                <div class="article__meta">
                    <span class="article__author">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($article['author_name']); ?>
                    </span>
                    <span class="article__date">
                        <i class="fas fa-calendar"></i> 
                        <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
                    </span>
                    <div class="article__stats">
                        <span class="article__stat" id="views-count-<?php echo $article['id']; ?>">
                            <i class="fas fa-eye"></i> <?php echo $article['views']; ?>
                        </span>
                        <button class="article__stat" onclick="handleReaction('article', 'like', <?php echo $article['id']; ?>)">
                            <i class="fas fa-thumbs-up"></i> 
                            <span id="like-count-<?php echo $article['id']; ?>"><?php echo $article['likes']; ?></span>
                        </button>
                        <button class="article__stat" onclick="handleReaction('article', 'dislike', <?php echo $article['id']; ?>)">
                            <i class="fas fa-thumbs-down"></i> 
                            <span id="dislike-count-<?php echo $article['id']; ?>"><?php echo $article['dislikes']; ?></span>
                        </button>
                    </div>
                </div>

                <div class="article__content">
                    <?php echo $article['content']; ?>
                </div>

                <section class="comments">
                    <h2 class="comments__title">Комментарии (<?php echo $article['comments_count']; ?>)</h2>
                    
                    <?php if (isAuthenticated()): ?>
                        <form class="comment-form" data-type="article" data-id="<?php echo $article['id']; ?>">
                            <textarea class="comment-form__input" name="content" placeholder="Ваш комментарий..." required></textarea>
                            <button type="submit" class="button button--primary">Отправить</button>
                        </form>
                    <?php else: ?>
                        <p class="comments__login-message">
                            <a href="login.php">Войдите</a>, чтобы оставить комментарий
                        </p>
                    <?php endif; ?>

                    <div class="comments__list" id="comments-<?php echo $article['id']; ?>">
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