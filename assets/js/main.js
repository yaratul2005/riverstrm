// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // 0. Preloader
    const preloader = document.getElementById('preloader');
    if (preloader) {
        window.addEventListener('load', () => {
            preloader.style.opacity = '0';
            setTimeout(() => preloader.remove(), 500);
        });
        // Fallback incase load event misfires
        setTimeout(() => {
            if (document.body.contains(preloader)) {
                preloader.style.opacity = '0';
                setTimeout(() => preloader.remove(), 500);
            }
        }, 3000);
    }

    // 1. Header Scroll Effect
    const header = document.querySelector('header');

    window.addEventListener('scroll', () => {
        if (!header) return;
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // 2. Hero Slider
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length > 0) {
        let currentSlide = 0;

        // Initial setup
        slides[0].classList.add('active');

        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 6000); // Change every 6 seconds
    }

    // 3. Comments System (AJAX)
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
        loadComments();
    }

    // 4. Live Search Logic
    const searchInput = document.querySelector('input[name="query"]');
    if (searchInput) {
        // Creating Popup Container dynamically if not exists
        let popup = document.getElementById('searchPopup');
        if (!popup) {
            popup = document.createElement('div');
            popup.id = 'searchPopup';
            popup.className = 'search-popup';
            // Insert after input's parent (form)
            searchInput.closest('form').style.position = 'relative';
            searchInput.closest('form').appendChild(popup);
        }

        let debounceTimer;
        searchInput.addEventListener('keyup', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();

            if (query.length < 2) {
                popup.style.display = 'none';
                return;
            }

            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch(`api/browse.php?query=${encodeURIComponent(query)}`);
                    const data = await res.json();

                    if (data.results && data.results.length > 0) {
                        popup.innerHTML = data.results.slice(0, 5).map(item => `
                            <a href="index.php?page=watch&type=${item.type}&id=${item.id}" class="search-result-item">
                                <img src="https://image.tmdb.org/t/p/w92${item.poster_path}" alt="${item.title}">
                                <div>
                                    <h4>${item.title}</h4>
                                    <span>${item.year} • ${item.type.toUpperCase()}</span>
                                </div>
                            </a>
                        `).join('') + `<a href="index.php?page=search&query=${encodeURIComponent(query)}" class="view-all">View all results</a>`;
                        popup.style.display = 'block';
                    } else {
                        popup.innerHTML = '<div style="padding:10px; color:#888;">No results found</div>';
                        popup.style.display = 'block';
                    }
                } catch (err) { console.error(err); }
            }, 300); // 300ms Delay
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !popup.contains(e.target)) {
                popup.style.display = 'none';
            }
        });
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
        if (comments.length === 0) list.innerHTML = '<p style="color:#666;">Be the first to comment!</p>';
    } catch (err) { console.error(err); }
}
