document.addEventListener('DOMContentLoaded', function() {
    // Загрузка видео
    const videoForm = document.getElementById('videoUploadForm');
    if (videoForm) {
        videoForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(videoForm);
            formData.append('type', 'video');
            
            try {
                const response = await fetch('/api/upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Видео успешно загружено');
                    videoForm.reset();
                } else {
                    alert(data.error || 'Ошибка при загрузке видео');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при загрузке');
            }
        });
    }
    
    // Загрузка статьи
    const articleForm = document.getElementById('articleUploadForm');
    if (articleForm) {
        articleForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(articleForm);
            formData.append('type', 'article');
            
            try {
                const response = await fetch('/api/upload.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Статья успешно опубликована');
                    articleForm.reset();
                } else {
                    alert(data.error || 'Ошибка при публикации статьи');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при публикации');
            }
        });
    }
    
    // Отслеживание просмотров
    function trackContentView(type, id) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    let viewTimer = 0;
                    const interval = setInterval(() => {
                        if (entry.isIntersecting) {
                            viewTimer++;
                            if (viewTimer >= 5) { // 5 секунд для демонстрации
                                clearInterval(interval);
                                fetch('/api/interaction.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `type=${type}&action=view&id=${id}`
                                });
                                observer.disconnect();
                            }
                        } else {
                            clearInterval(interval);
                        }
                    }, 1000);
                }
            });
        }, { threshold: 0.5 });
        
        const content = document.querySelector(`#${type}-${id}`);
        if (content) {
            observer.observe(content);
        }
    }
    
    // Обработка лайков и дизлайков
    function handleReaction(type, action, id) {
        fetch('/api/interaction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=${type}&action=${action}&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const counter = document.querySelector(`#${action}-count-${id}`);
                if (counter) {
                    counter.textContent = parseInt(counter.textContent) + 1;
                }
            } else {
                alert(data.message || 'Вы уже поставили оценку');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при обработке реакции');
        });
    }
    
    // Добавление комментариев
    const commentForms = document.querySelectorAll('.comment-form');
    commentForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const type = form.dataset.type;
            const id = form.dataset.id;
            const content = form.querySelector('textarea').value;
            
            try {
                const response = await fetch('/api/interaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `type=${type}&action=comment&id=${id}&content=${encodeURIComponent(content)}`
                });
                
                const data = await response.json();
                if (data.success) {
                    const commentsList = document.querySelector(`#comments-${id}`);
                    const newComment = document.createElement('div');
                    newComment.className = 'comment';
                    newComment.innerHTML = `
                        <div class="comment__header">
                            <span class="comment__author">${data.comment.username}</span>
                            <span class="comment__class">${data.comment.class}</span>
                            <span class="comment__date">${data.comment.created_at}</span>
                        </div>
                        <div class="comment__content">${data.comment.content}</div>
                    `;
                    commentsList.prepend(newComment);
                    form.reset();
                } else {
                    alert(data.error || 'Ошибка при добавлении комментария');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Произошла ошибка при отправке комментария');
            }
        });
    });
    
    // Инициализация отслеживания просмотров для всего контента на странице
    document.querySelectorAll('[data-track-view]').forEach(element => {
        const type = element.dataset.type;
        const id = element.dataset.id;
        trackContentView(type, id);
    });
});