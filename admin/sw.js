/* BODARE Admin service worker — network-first, no API caching */
'use strict';

self.addEventListener('install', function (event) {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', function (event) {
  var req = event.request;
  var url = req.url;

  if (req.method !== 'GET') {
    return;
  }

  if (/\/api\//i.test(url) || url.indexOf('inquiries/poll') !== -1) {
    event.respondWith(fetch(req));
    return;
  }

  event.respondWith(
    fetch(req).catch(function () {
      return caches.match(req);
    })
  );
});
