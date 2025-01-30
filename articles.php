<?php
require_once 'config/database.php';
require_once 'api/auth.php';

$conn = connectDB();
$query = "SELECT a.*, u.username as author_name, 
          (SELECT COUNT(*) FROM comments WHERE content_type = 'article' AND content_id = a.id) as comments_count 
          FROM articles a 
          LEFT JOIN users u ON a.author_id = u.id 
          ORDER BY a.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статьи - Лицейск News</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header__content">
                <a href="/" class="logo">Лицейск News</a>
                <nav class="nav">
                    <ul class="nav__list">
                        <li><a href="/" class="nav__link">Главная</a></li>
                        <li><a href="articles.php" class="nav__link">Статьи</a></li>
                        <li><a href="videos.php" class="nav__link">Видео</a></li>
                        <?php if (isAuthenticated()): ?>
                            <li><a href="profile.php" class="nav__link">Профиль</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a href="admin/" class="nav__link">Админ-панель</a></li>
                            <?php endif; ?>
                            <li><a href="logout.php" class="nav__link">Выйти</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="nav__link">Войти</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <button class="theme-toggle" id="themeToggle">
                    <span class="theme-toggle__icon">🌙</span>
                </button>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
            <h1 class="section-title fade-in">Статьи</h1>
            
            <div class="articles-grid">
                <?php while ($article = $result->fetch_assoc()): ?>
                    <article class="article-card fade-in">
                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($article['title']); ?>" 
                             class="article-card__image">
                        <div class="article-card__content">
                            <h2 class="article-card__title">
                                <?php echo htmlspecialchars($article['title']); ?>
                            </h2>
                            <p class="article-card__excerpt">
                                <?php echo mb_substr(strip_tags($article['content']), 0, 150) . '...'; ?>
                            </p>
                            <div class="article-card__meta">
                                <div class="article-card__info">
                                    <span class="article-card__author">
                                        <i class="fas fa-user"></i> 
                                        <?php echo htmlspecialchars($article['author_name']); ?>
                                    </span>
                                    <span class="article-card__date">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
                                    </span>
                                </div>
                                <div class="article-card__stats">
                                    <span class="article-card__stat">
                                        <i class="fas fa-eye"></i> <?php echo $article['views']; ?>
                                    </span>
                                    <span class="article-card__stat">
                                        <i class="fas fa-thumbs-up"></i> <?php echo $article['likes']; ?>
                                    </span>
                                    <span class="article-card__stat">
                                        <i class="fas fa-comments"></i> <?php echo $article['comments_count']; ?>
                                    </span>
                                </div>
                                <a href="article.php?id=<?php echo $article['id']; ?>" 
                                   class="button button--primary">Читать</a>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__section">
                    <h3 class="footer__title">О нас</h3>
                    <p>Лицейск News - ваш источник новостей о школьной экономической игре.</p>
                </div>
                <div class="footer__section">
                    <h3 class="footer__title">Разделы</h3>
                    <ul class="footer__links">
                        <li class="footer__link"><a href="/">Главная</a></li>
                        <li class="footer__link"><a href="articles.php">Статьи</a></li>
                        <li class="footer__link"><a href="videos.php">Видео</a></li>
                    </ul>
                </div>
                <div class="footer__section">
                    <h3 class="footer__title">Контакты</h3>
                    <ul class="footer__links">
                        <li class="footer__link"><a href="mailto:info@liceysk-news.ru">info@liceysk-news.ru</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>