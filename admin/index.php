<?php
require_once '../config/database.php';
require_once '../api/auth.php';

requireAdmin();

$conn = connectDB();

// Получаем статистику
$stats = [
    'articles' => $conn->query("SELECT COUNT(*) as count FROM articles")->fetch_assoc()['count'],
    'videos' => $conn->query("SELECT COUNT(*) as count FROM videos")->fetch_assoc()['count'],
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'comments' => $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count']
];

// Последние действия
$recent_activities = $conn->query("
    (SELECT 'article' as type, title, created_at FROM articles ORDER BY created_at DESC LIMIT 5)
    UNION ALL
    (SELECT 'video' as type, title, created_at FROM videos ORDER BY created_at DESC LIMIT 5)
    ORDER BY created_at DESC LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Лицейск News</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar__header">
                <h2 class="admin-sidebar__title">Админ-панель</h2>
            </div>
            <nav class="admin-nav">
                <ul class="admin-nav__list">
                    <li class="admin-nav__item">
                        <a href="index.php" class="admin-nav__link active">
                            <i class="fas fa-home"></i> Главная
                        </a>
                    </li>
                    <li class="admin-nav__item">
                        <a href="articles.php" class="admin-nav__link">
                            <i class="fas fa-newspaper"></i> Статьи
                        </a>
                    </li>
                    <li class="admin-nav__item">
                        <a href="videos.php" class="admin-nav__link">
                            <i class="fas fa-video"></i> Видео
                        </a>
                    </li>
                    <li class="admin-nav__item">
                        <a href="users.php" class="admin-nav__link">
                            <i class="fas fa-users"></i> Пользователи
                        </a>
                    </li>
                    <li class="admin-nav__item">
                        <a href="comments.php" class="admin-nav__link">
                            <i class="fas fa-comments"></i> Комментарии
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header__content">
                    <h1 class="admin-header__title">Панель управления</h1>
                    <div class="admin-header__user">
                        <span class="admin-header__username">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                        <a href="../logout.php" class="admin-header__logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </header>

            <div class="admin-content">
                <div class="admin-stats">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <h3 class="admin-stat-card__title">Статьи</h3>
                            <p class="admin-stat-card__value"><?php echo $stats['articles']; ?></p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <h3 class="admin-stat-card__title">Видео</h3>
                            <p class="admin-stat-card__value"><?php echo $stats['videos']; ?></p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <h3 class="admin-stat-card__title">Пользователи</h3>
                            <p class="admin-stat-card__value"><?php echo $stats['users']; ?></p>
                        </div>
                    </div>
                    <div class="admin-stat-card">
                        <div class="admin-stat-card__icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="admin-stat-card__content">
                            <h3 class="admin-stat-card__title">Комментарии</h3>
                            <p class="admin-stat-card__value"><?php echo $stats['comments']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="admin-recent">
                    <h2 class="admin-recent__title">Последние действия</h2>
                    <div class="admin-recent__list">
                        <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                            <div class="admin-recent__item">
                                <div class="admin-recent__icon">
                                    <i class="fas fa-<?php echo $activity['type'] === 'article' ? 'newspaper' : 'video'; ?>"></i>
                                </div>
                                <div class="admin-recent__content">
                                    <h4 class="admin-recent__item-title">
                                        <?php echo htmlspecialchars($activity['title']); ?>
                                    </h4>
                                    <p class="admin-recent__date">
                                        <?php echo date('d.m.Y H:i', strtotime($activity['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>