/* MedBandhu service worker — offline shell + asset cache.
   Bump CACHE_VERSION on any change to this file to force an update. */
const CACHE_VERSION = 'v1';
const CACHE = `medbandhu-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline.html';

const PRECACHE = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE).then((cache) => cache.addAll(PRECACHE)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim())
    );
});

function isCacheableAsset(url) {
    return url.origin === self.location.origin
        ? /^\/(build|icons)\//.test(url.pathname)
        : /fonts\.(bunny\.net|gstatic\.com)$/.test(url.hostname);
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);

    // Page navigations: network-first, fall back to the offline page.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // Fingerprinted assets & fonts: stale-while-revalidate.
    if (isCacheableAsset(url)) {
        event.respondWith(
            caches.open(CACHE).then(async (cache) => {
                const cached = await cache.match(request);
                const network = fetch(request).then((res) => {
                    if (res && res.status === 200) cache.put(request, res.clone());
                    return res;
                }).catch(() => cached);
                return cached || network;
            })
        );
    }
});

self.addEventListener('message', (event) => {
    if (event.data === 'skip-waiting') self.skipWaiting();
});
