<?php
// user/dashboard.php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user = $pdo->query("SELECT * FROM users WHERE id = " . $_SESSION['user_id'])->fetch();
?>

<div class="section" style="padding-top: 100px;">
    <div class="dashboard-container" style="display: flex; gap: 30px; max-width: 1200px; margin: 0 auto;">
        
        <!-- Sidebar -->
        <aside style="width: 250px; flex-shrink: 0;">
            <div style="background: var(--bg-secondary); padding: 30px; border-radius: 12px; border: var(--glass-border); text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--accent-color); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold;">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h3 style="margin-bottom: 5px;"><?php echo htmlspecialchars($user['username']); ?></h3>
                <p style="color: #888; font-size: 0.9rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                <div style="margin-top: 20px; text-align: left;">
                    <a href="#profile" class="dash-link active">Profile Settings</a>
                    <a href="#favorites" class="dash-link">My Favorites</a>
                    <a href="#history" class="dash-link">Watch History</a>
                    <a href="api/logout.php" class="dash-link" style="color: var(--accent-color);">Logout</a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div style="flex-grow: 1;">
            <!-- Profile Section -->
            <div id="profile" class="dash-section">
                <h2 class="section-title">Profile Settings</h2>
                <div style="background: var(--bg-secondary); padding: 30px; border-radius: 12px; border: var(--glass-border);">
                    <form>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 10px; color: #ccc;">Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" style="width: 100%; padding: 12px; background: #000; border: 1px solid #333; color: white; border-radius: 6px;">
                        </div>
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 10px; color: #ccc;">Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; color: #888; border-radius: 6px; cursor: not-allowed;">
                        </div>
                        <button class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Favorites (My List) -->
            <div id="favorites" class="dash-section" style="display: none;">
                <h2 class="section-title">My List</h2>
                <?php
                    // Fetch Watchlist
                    $watchlist = $pdo->prepare("SELECT w.*, 0 as is_local FROM watchlist w WHERE user_id = ? ORDER BY created_at DESC");
                    $watchlist->execute([$_SESSION['user_id']]);
                    $list = $watchlist->fetchAll();
                ?>
                
                <?php if (count($list) > 0): ?>
                    <div class="media-grid">
                        <?php foreach ($list as $item): 
                            // Fetch details from TMDB on the fly (or cache ideally) - simpler for now: use JS or fetch basic info
                            // Ideally we should store title/poster in watchlist table for speed. 
                            // For now, let's just fetch from API quickly or store minimal data. 
                            // Actually, let's use a helper if possible. 
                            // WAIT: Doing 20 api calls here is bad. 
                            // BETTER: Just show ID list OR update watchlist table to store metadata.
                            // Let's UPDATE watchlist table structure effectively? No, too risky.
                            // Let's just fetch for now, caching handles it properly.
                            
                            require_once 'api/tmdb.php'; // Ensure loaded
                            $tmdb = new TMDB();
                            $details = $tmdb->getDetails($item['tmdb_id'], $item['type']); 
                            $img = 'https://image.tmdb.org/t/p/w300' . ($details['poster_path'] ?? '');
                            $title = $details['title'] ?? $details['name'] ?? 'Unknown';
                        ?>
                        <a href="index.php?page=watch&type=<?php echo $item['type']; ?>&id=<?php echo $item['tmdb_id']; ?>" class="media-card">
                            <img src="<?php echo $img; ?>" alt="<?php echo htmlspecialchars($title); ?>" loading="lazy">
                            <div class="info">
                                <h3><?php echo htmlspecialchars($title); ?></h3>
                                <span><?php echo ucfirst($item['type']); ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div style="padding: 40px; text-align: center; background: var(--bg-secondary); border-radius: 12px; border: var(--glass-border);">
                        <p style="color: #888; margin-bottom: 20px;">Your list is empty.</p>
                        <a href="index.php?page=home" class="btn btn-primary">Browse Movies</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- History (LocalStorage) -->
            <div id="history" class="dash-section" style="display: none;">
                <h2 class="section-title">Watch History</h2>
                <div class="media-grid" id="history-grid">
                    <!-- Populated by JS -->
                </div>
                <p id="no-history" style="color: #888; display: none;">No history found on this device.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .dash-link {
        display: block;
        padding: 10px 0;
        color: #ccc;
        border-bottom: 1px solid #333;
        transition: 0.2s;
    }
    .dash-link:hover, .dash-link.active { color: white; border-color: white; }
    
    @media (max-width: 768px) {
        .dashboard-container { flex-direction: column; }
        aside { width: 100%; }
    }
</style>

<script>
    // Simple Tab Switcher
    document.querySelectorAll('.dash-link').forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                document.querySelectorAll('.dash-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
                
                document.querySelectorAll('.dash-section').forEach(s => s.style.display = 'none');
                document.querySelector(href).style.display = 'block';

                // Load History if tab is history
                if (href === '#history') loadHistory();
            }
        });
    });

    // Hash navigation support
    if (window.location.hash) {
        document.querySelector(`.dash-link[href="${window.location.hash}"]`)?.click();
    }

    function loadHistory() {
        const grid = document.getElementById('history-grid');
        const noHist = document.getElementById('no-history');
        const history = JSON.parse(localStorage.getItem('continue_watching') || '[]');

        grid.innerHTML = '';
        if (history.length === 0) {
            noHist.style.display = 'block';
            return;
        }

        noHist.style.display = 'none';
        history.forEach(item => {
             const img = item.poster ? 'https://image.tmdb.org/t/p/w300' + item.poster : 'https://via.placeholder.com/300x450?text=No+Poster';
             const link = `index.php?page=watch&type=${item.type}&id=${item.id}`;
             const date = new Date(item.timestamp).toLocaleDateString();
             
             const html = `
                <a href="${link}" class="media-card">
                    <img src="${img}" alt="${item.title}" loading="lazy">
                    <div class="info">
                        <h3>${item.title}</h3>
                        <span style="font-size: 0.8rem; color: #888;">Watched on ${date}</span>
                    </div>
                </a>
             `;
             grid.innerHTML += html;
        });
    }
</script>
