// Service Worker for BODARE Pension House PWA
// Bump CACHE_NAME whenever icons / splash branding change so old caches drop.
const CACHE_NAME = 'bodare-pwa-v8';
const urlsToCache = [
  '/',
  '/index.php',
  '/rooms.php',
  '/style.css',
  '/app-shell.css',
  '/script.js',
  '/manifest.json'
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
      .catch((error) => {
        console.log('Cache install failed:', error);
      })
  );
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

// Fetch event
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  const requestUrl = new URL(event.request.url);

  // Third-party assets (fonts, ads, CDNs) must bypass the SW — intercepting them
  // causes rejected respondWith() promises when fetch is blocked or offline.
  if (requestUrl.origin !== self.location.origin) {
    return;
  }

  // Skip API requests
  if (event.request.url.includes('/api/')) {
    return;
  }

  // Always fetch critical runtime scripts / brand assets from network to avoid stale UI.
  // Includes PWA icons + manifest so splash / home-screen logos update after deploys.
  const criticalRuntimeFiles = [
    '/api-config.js',
    '/booking-api.js',
    '/script.js',
    '/native-bridge.js',
    '/app-shell.css',
    '/manifest.json',
    '/img/logo.png',
    '/img/logo-square.png',
    '/img/main-logo.jpg',
    '/img/icon-96.png',
    '/img/icon-192.png',
    '/img/icon-512.png',
    '/img/apple-touch-icon.png',
    '/img/favicon-16.png',
    '/img/favicon-32.png'
  ];
  if (criticalRuntimeFiles.some((file) => requestUrl.pathname.endsWith(file))) {
    event.respondWith(
      fetch(event.request).catch(() => caches.match(event.request))
    );
    return;
  }

  const isDocumentRequest =
    event.request.mode === 'navigate' ||
    event.request.destination === 'document' ||
    event.request.url.endsWith('.php');

  // For pages, always try network first to avoid stale HTML/UI state.
  if (isDocumentRequest) {
    event.respondWith(
      fetch(event.request)
        .then((response) => {
          if (response && response.status === 200 && response.type === 'basic') {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          return caches.match(event.request).then((cached) => {
            return cached || caches.match('/index.php');
          });
        })
    );
    return;
  }

  // For same-origin static assets, cache-first with network fallback.
  event.respondWith(
    caches.match(event.request)
      .then((response) => {
        if (response) {
          return response;
        }

        return fetch(event.request).then((networkResponse) => {
          if (!networkResponse || networkResponse.status !== 200 || networkResponse.type !== 'basic') {
            return networkResponse;
          }

          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache);
          });

          return networkResponse;
        });
      })
      .catch(() => fetch(event.request))
  );
});
