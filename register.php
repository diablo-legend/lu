<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } else {
        $conn = connectDB();
        
        // Проверяем, не занят ли email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Этот email уже зарегистрирован';
        } else {
            // Создаем нового пользователя
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = 'Регистрация успешна! Теперь вы можете войти.';
            } else {
                $error = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Лицейск News</title>
    <link rel="stylesheet" href="css/style.css">
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
                        <li><a href="login.php" class="nav__link">Войти</a></li>
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
            <form class="form fade-in" method="POST" action="register.php">
                <h1 class="section-title">Регистрация</h1>
                
                <?php if ($error): ?>
                    <div class="form__error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="form__success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="form__group">
                    <label class="form__label" for="username">Имя пользователя</label>
                    <input class="form__input" type="text" id="username" name="username" required>
                </div>
                
                <div class="form__group">
                    <label class="form__label" for="email">Email</label>
                    <input class="form__input" type="email" id="email" name="email" required>
                </div>
                
                <div class="form__group">
                    <label class="form__label" for="password">Пароль</label>
                    <input class="form__input" type="password" id="password" name="password" required>
                </div>
                
                <div class="form__group">
                    <label class="form__label" for="password_confirm">Подтверждение пароля</label>
                    <input class="form__input" type="password" id="password_confirm" name="password_confirm" required>
                </div>
                
                <button type="submit" class="button button--primary">Зарегистрироваться</button>
                
                <p class="form__footer">
                    Уже есть аккаунт? <a href="login.php">Войти</a>
                </p>
            </form>
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