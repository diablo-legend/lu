<header class="header">
    <div class="container">
        <div class="header__content">
            <a href="/" class="logo">
                <img src="images/logo.svg" alt="Лицейск News" class="logo__img">
                <span class="logo__text">Лицейск News</span>
            </a>
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
            <div class="header__controls">
                <button class="theme-toggle" id="themeToggle" aria-label="Переключить тему">
                    <span class="theme-toggle__icon">🌙</span>
                </button>
                <button class="burger-menu" aria-label="Меню">
                    <span class="burger-menu__line"></span>
                    <span class="burger-menu__line"></span>
                    <span class="burger-menu__line"></span>
                </button>
            </div>
        </div>
    </div>
</header>