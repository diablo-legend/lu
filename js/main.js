document.addEventListener('DOMContentLoaded', function() {
    // Переключение темы
    const themeToggle = document.getElementById('themeToggle');
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        themeToggle.querySelector('.theme-toggle__icon').textContent = 
            theme === 'dark' ? '☀️' : '🌙';
    }
    
    // Проверяем сохраненную тему или системные настройки
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        setTheme(savedTheme);
    } else if (prefersDarkScheme.matches) {
        setTheme('dark');
    }
    
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        setTheme(currentTheme === 'dark' ? 'light' : 'dark');
    });

    // Инициализация бургер-меню
    const burgerMenu = document.querySelector('.burger-menu');
    const navList = document.querySelector('.nav__list');
    
    if (burgerMenu && navList) {
        function toggleMenu() {
            burgerMenu.classList.toggle('active');
            navList.classList.toggle('active');
            document.body.style.overflow = navList.classList.contains('active') ? 'hidden' : '';
        }
        
        burgerMenu.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });
        
        // Закрываем меню при клике на ссылку
        const navLinks = navList.querySelectorAll('.nav__link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navList.classList.remove('active');
                burgerMenu.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Закрываем меню при клике вне его
        document.addEventListener('click', (e) => {
            if (navList.classList.contains('active') && 
                !e.target.closest('.nav__list') && 
                !e.target.closest('.burger-menu')) {
                navList.classList.remove('active');
                burgerMenu.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // Загрузка новостей
    async function loadNews() {
        try {
            const response = await fetch('api/get_news.php');
            const news = await response.json();
            
            const newsContainer = document.getElementById('newsContainer');
            if (!newsContainer) return;

            newsContainer.innerHTML = news.map(item => `
                <article class="news-card fade-in">
                    <img src="${item.image_url}" alt="${item.title}" class="news-card__image">
                    <div class="news-card__content">
                        <h2 class="news-card__title">${item.title}</h2>
                        <p class="news-card__text">${item.excerpt}</p>
                        <div class="news-card__meta">
                            <div class="news-card__stats">
                                <span class="news-card__stat">
                                    <i class="fas fa-eye"></i> ${item.views}
                                </span>
                                <span class="news-card__stat">
                                    <i class="fas fa-thumbs-up"></i> ${item.likes}
                                </span>
                            </div>
                            <a href="article.php?id=${item.id}" class="button button--primary">Читать далее</a>
                        </div>
                    </div>
                </article>
            `).join('');
        } catch (error) {
            console.error('Ошибка загрузки новостей:', error);
            const newsContainer = document.getElementById('newsContainer');
            if (newsContainer) {
                newsContainer.innerHTML = '<p class="error-message">Не удалось загрузить новости. Пожалуйста, попробуйте позже.</p>';
            }
        }
    }

    // Загружаем новости при загрузке страницы
    loadNews();
});