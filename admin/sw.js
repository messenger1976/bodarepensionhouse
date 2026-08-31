/**
 * BODARE Admin service worker
 * - Precache static shell assets
 * - Network-first for pages (never cache authenticated HTML)
 * - Skip API / poll endpoints
 */
'use strict';

var CACHE_NAME = 'bodare-admin-shell-v2';
var SHELL_ASSETS = [
  './assets/css/admin-mobile.css',
  './assets/js/admin-mobile.js',
  './assets/icons/admin-icon-192.png',
  './assets/icons/admin-icon-512.png',
  './manifest.json'
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(SHELL_ASSETS).catch(function () {
        /* ignore individual failures */
      });
    }).then(function () {
      return self.skipWaiting();
    })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.filter(function (k) { return k !== CACHE_NAME; }).map(function (k) {
          return caches.delete(k);
        })
      );
    }).then(function () {
      return self.clients.claim();
    })
  );
});

self.addEventListener('fetch', function (event) {
  var req = event.request;
  if (req.method !== 'GET') {
    return;
  }

  var url = req.url;
  if (/\/api\//i.test(url) || url.indexOf('inquiries/poll') !== -1) {
    event.respondWith(fetch(req));
    return;
  }

  var accept = req.headers.get('accept') || '';
  var isHtml = req.mode === 'navigate' || accept.indexOf('text/html') !== -1;

  if (isHtml) {
    // Always network for admin HTML (session-bound)
    event.respondWith(
      fetch(req).catch(function () {
        return new Response(
          '<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width,initial-scale=1">' +
          '<title>Offline</title></head><body style="font-family:sans-serif;padding:2rem;text-align:center">' +
          '<h1>Offline</h1><p>Admin requires a network connection.</p></body></html>',
          { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
      })
    );
    return;
  }

  // Static assets: network-first, fall back to shell cache
  event.respondWith(
    fetch(req).then(function (res) {
      return res;
    }).catch(function () {
      return caches.match(req).then(function (cached) {
        return cached || new Response('', { status: 503 });
      });
    })
  );
});
