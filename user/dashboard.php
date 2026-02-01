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

            <!-- Favorites Placeholder -->
            <div id="favorites" class="dash-section" style="display: none;">
                <h2 class="section-title">My Favorites</h2>
                <p style="color: #888;">You haven't saved any movies yet.</p>
            </div>

            <!-- History Placeholder -->
            <div id="history" class="dash-section" style="display: none;">
                <h2 class="section-title">Watch History</h2>
                <p style="color: #888;">No history available.</p>
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
            }
        });
    });
</script>
