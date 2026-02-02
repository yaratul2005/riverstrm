<?php
// api/contact.php
require_once 'config.php';
require_once 'smtp.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = trim($_POST['message']);

    if (!$email || !$message) {
        echo json_encode(['success' => false, 'error' => 'Email and Message are required.']);
        exit;
    }

    $smtp = new SMTPService();
    $subject = "New Contact Message from " . $email;
    $body = "<h3>New Message from Great10 User</h3>";
    $body .= "<p><strong>Email:</strong> $email</p>";
    $body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";

    // Send to Admin (From Email is usually used as 'noreply', so we send TO the 'From Email' or a specific admin email)
    // For now, let's send it to the configured 'From Email' as the Admin receiver.
    // Or we could query the first admin user.
    // Let's use the SMTP 'From Email' as the recipient for admin notifications.
    
    // Retrieve settings again to get the admin email
    $pdo = getDB();
    $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
    $adminEmail = $settings['smtp_from'] ?? 'admin@great10.xyz';

    if ($smtp->send($adminEmail, $subject, $body)) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send email. Check SMTP settings.']);
    }
}
?>
