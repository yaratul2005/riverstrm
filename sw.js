// sw.js
const CACHE_NAME = 'great10-v1';
const ASSETS = [
    '/',
    '/index.php',
    '/assets/css/style.css',
    '/assets/js/main.js'
];

// Install Event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(ASSETS);
        })
    );
});

// Fetch Event (Offline Fallback)
self.addEventListener('fetch', event => {
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});
