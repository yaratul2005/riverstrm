<?php
// includes/footer.php
$pdo = getDB();
// Fetch Custom Pages for Footer
$footerPages = $pdo->query("SELECT * FROM custom_pages WHERE is_published = 1 ORDER BY title ASC")->fetchAll();
?>
<footer style="background: #0a0a0a; border-top: 1px solid #222; padding: 60px 0; margin-top: 80px; font-size: 0.9rem; color: #888;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;">
        
        <!-- Brand -->
        <div>
            <h3 style="color: white; font-size: 1.5rem; margin-bottom: 20px;">Great10</h3>
            <p>The best place to stream your favorite movies and TV shows for free.</p>
            <div style="margin-top: 20px;">
                &copy; <?php echo date('Y'); ?> Great10 Streaming.
            </div>
        </div>

        <!-- Links -->
        <div>
            <h4 style="color: white; margin-bottom: 20px;">Quick Links</h4>
            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 10px;"><a href="index.php" style="color: #888; text-decoration: none;">Home</a></li>
                <li style="margin-bottom: 10px;"><a href="index.php?page=search" style="color: #888; text-decoration: none;">Search</a></li>
                <li style="margin-bottom: 10px;"><a href="index.php?page=dashboard" style="color: #888; text-decoration: none;">My List</a></li>
            </ul>
        </div>

        <!-- Pages -->
        <div>
            <h4 style="color: white; margin-bottom: 20px;">Legal & Info</h4>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($footerPages as $fp): ?>
                <li style="margin-bottom: 10px;">
                    <a href="page.php?slug=<?php echo $fp['slug']; ?>" style="color: #888; text-decoration: none;"><?php echo htmlspecialchars($fp['title']); ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Contact Form -->
        <div>
            <h4 style="color: white; margin-bottom: 20px;">Contact Us</h4>
            <form id="contactForm" onsubmit="sendContact(event)">
                <input type="email" name="email" placeholder="Your Email" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #222; border: 1px solid #333; color: white; border-radius: 4px;">
                <textarea name="message" placeholder="Message..." rows="3" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #222; border: 1px solid #333; color: white; border-radius: 4px;"></textarea>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px; font-size: 0.9rem;">Send Message</button>
                <p id="contactMsg" style="margin-top: 10px; font-size: 0.8rem;"></p>
            </form>
        </div>
    </div>
</footer>

<script>
async function sendContact(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button');
    const msg = document.getElementById('contactMsg');
    
    const formData = new FormData(form);
    
    btn.disabled = true;
    btn.innerText = 'Sending...';
    msg.innerText = '';
    msg.style.color = '#888';

    try {
        const res = await fetch('api/contact.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            msg.innerText = data.message;
            msg.style.color = 'green';
            form.reset();
        } else {
            msg.innerText = data.error || 'Failed to send.';
            msg.style.color = 'red';
        }
    } catch (err) {
        msg.innerText = 'Network Error.';
        msg.style.color = 'red';
    } finally {
        btn.disabled = false;
        btn.innerText = 'Send Message';
    }
}
</script>
