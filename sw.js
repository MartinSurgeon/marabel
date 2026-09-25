/**
 * Service Worker — Uaddara Basic School SBA PWA
 * Caching Strategy:
 *  - Dynamic navigation (HTML): Network-First with offline.html fallback
 *  - Static assets (CSS, JS, Images, Fonts): Stale-While-Revalidate
 *  - POST requests: Always bypass directly to network
 */

const CACHE_NAME = 'uaddara-sba-v2';

const STATIC_PRECACHE = [
  './offline.html',
  './assets/css/app.css',
  './assets/img/school-logo.png',
  './assets/img/icons/icon-192x192.png',
  './assets/img/icons/icon-512x512.png',
  './assets/img/icons/apple-touch-icon.png'
];

// Install: Pre-cache offline shell and core assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_PRECACHE);
    }).then(() => self.skipWaiting())
  );
});

// Activate: Clean up old cache versions
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.map((key) => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// Fetch: Strategy depending on request type
self.addEventListener('fetch', (event) => {
  const request = event.request;

  // Only handle GET requests; POST/PUT/DELETE must go directly to server
  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);

  // 1. Navigation Requests (Page HTML): Network-First -> Fallback to offline.html
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((networkResponse) => {
          // If valid response from server, return it
          return networkResponse;
        })
        .catch(() => {
          // If offline or network unreachable, serve the offline page
          return caches.match('./offline.html').then((offlineRes) => {
            return offlineRes || caches.match('/offline.html');
          });
        })
    );
    return;
  }

  // 2. Static Assets (CSS, JS, Images, Fonts): Stale-While-Revalidate
  const isStaticAsset = (
    url.pathname.includes('/assets/') ||
    url.pathname.endsWith('.css') ||
    url.pathname.endsWith('.js') ||
    url.pathname.endsWith('.png') ||
    url.pathname.endsWith('.jpg') ||
    url.pathname.endsWith('.jpeg') ||
    url.pathname.endsWith('.svg') ||
    url.pathname.endsWith('.woff2')
  );

  if (isStaticAsset) {
    event.respondWith(
      caches.match(request).then((cachedResponse) => {
        const fetchPromise = fetch(request).then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseToCache = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => {
              cache.put(request, responseToCache);
            });
          }
          return networkResponse;
        }).catch(() => cachedResponse);

        return cachedResponse || fetchPromise;
      })
    );
    return;
  }

  // Default: Network with cache fallback
  event.respondWith(
    fetch(request).catch(() => caches.match(request))
  );
});
