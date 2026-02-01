<?php
// api/smtp.php

class SMTPService {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;

    public function __construct() {
        $pdo = getDB();
        $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $this->host = $settings['smtp_host'] ?? '';
        $this->port = $settings['smtp_port'] ?? 587;
        $this->username = $settings['smtp_user'] ?? '';
        $this->password = $settings['smtp_pass'] ?? '';
        $this->fromEmail = $settings['smtp_from'] ?? 'noreply@great10.xyz';
        $this->fromName = $settings['site_name'] ?? 'Great10 Streaming';
    }

    public function send($to, $subject, $body) {
        if (!$this->host) return false; // SMTP not configured

        try {
            $socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);
            if (!$socket) throw new Exception("Connection failed: $errstr");

            $this->read($socket);
            $this->cmd($socket, "EHLO " . $_SERVER['SERVER_NAME']);
            
            if ($this->username) {
                $this->cmd($socket, "AUTH LOGIN");
                $this->cmd($socket, base64_encode($this->username));
                $this->cmd($socket, base64_encode($this->password));
            }

            $this->cmd($socket, "MAIL FROM: <{$this->fromEmail}>");
            $this->cmd($socket, "RCPT TO: <$to>");
            $this->cmd($socket, "DATA");
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
            $headers .= "Subject: $subject\r\n";
            
            $this->cmd($socket, "$headers\r\n$body\r\n.");
            $this->cmd($socket, "QUIT");
            fclose($socket);
            
            return true;
        } catch (Exception $e) {
            error_log("SMTP Error: " . $e->getMessage());
            return false;
        }
    }

    private function cmd($socket, $cmd) {
        fwrite($socket, $cmd . "\r\n");
        return $this->read($socket);
    }

    private function read($socket) {
        $response = "";
        while ($str = fgets($socket, 515)) {
            $response .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $response;
    }
}
?>
