<?php
// api/cache.php

class Cache {
    private $cacheDir;
    private $expiry; // Seconds

    public function __construct($expiry = 43200) { // Default 12 hours
        $this->cacheDir = __DIR__ . '/../cache/';
        $this->expiry = $expiry;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    private function getFileName($key) {
        return $this->cacheDir . md5($key) . '.json';
    }

    public function get($key) {
        $file = $this->getFileName($key);
        if (file_exists($file)) {
            if (time() - filemtime($file) < $this->expiry) {
                return json_decode(file_get_contents($file), true);
            } else {
                unlink($file); // Delete expired
            }
        }
        return null;
    }

    public function set($key, $data) {
        $file = $this->getFileName($key);
        file_put_contents($file, json_encode($data));
    }
}
?>
