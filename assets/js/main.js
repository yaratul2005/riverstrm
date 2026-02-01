// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // 1. Header Scroll Effect
    const header = document.querySelector('header');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }

        // Parallax for Hero
        const heroBg = document.querySelector('.hero-bg');
        if (heroBg) {
            let offset = window.scrollY * 0.5;
            heroBg.style.transform = `translateY(${offset}px)`;
        }
    });

    // 2. Comments System (AJAX)
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(commentForm);
            
            try {
                const response = await fetch('api/comment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Prepend new comment
                    const list = document.getElementById('commentList');
                    const newComment = document.createElement('div');
                    newComment.className = 'comment-item';
                    newComment.innerHTML = `
                        <div class="comment-header">
                            <span class="comment-user">${result.username}</span>
                            <span>Just now</span>
                        </div>
                        <div class="comment-text">${formData.get('comment').replace(/</g, "&lt;")}</div>
                    `;
                    list.insertBefore(newComment, list.firstChild);
                    commentForm.reset();
                } else if (result.error === 'Unauthorized') {
                    alert('Please login to comment.');
                    window.location.href = 'index.php?page=login';
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (err) {
                console.error(err);
            }
        });

        // Load Comments
        loadComments();
    }
});

async function loadComments() {
    const list = document.getElementById('commentList');
    if (!list) return;
    
    const tmdbId = list.dataset.tmdbId;
    
    try {
        const res = await fetch(`api/comment.php?tmdb_id=${tmdbId}`);
        const comments = await res.json();
        
        list.innerHTML = '';
        comments.forEach(c => {
            const div = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = `
                <div class="comment-header">
                    <span class="comment-user">${c.username}</span>
                    <span>${new Date(c.created_at).toLocaleDateString()}</span>
                </div>
                <div class="comment-text">${c.comment.replace(/</g, "&lt;")}</div>
            `;
            list.appendChild(div);
        });
        
        if (comments.length === 0) {
            list.innerHTML = '<p style="color:#666; margin-top:20px;">Be the first to comment!</p>';
        }
    } catch (err) {
        console.error(err);
    }
}
