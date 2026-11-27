/* Service worker KotakRasa: aset statis saja. Data aplikasi tetap online. */
const NAMA_CACHE = 'kotakrasa-v1';
const HALAMAN_OFFLINE = '/offline';

const ASET_AWAL = [
    HALAMAN_OFFLINE,
    '/manifest.webmanifest',
    '/favicon.png',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(NAMA_CACHE)
            .then((cache) => cache.addAll(ASET_AWAL))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((nama) => Promise.all(
                nama.filter((item) => item !== NAMA_CACHE).map((item) => caches.delete(item)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Jangan pernah caching POST, termasuk login, pembayaran, dan form lain.
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) {
        return;
    }

    // Navigasi halaman: ambil dari jaringan dahulu. Saat offline tampilkan halaman khusus.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(HALAMAN_OFFLINE)),
        );

        return;
    }

    const asetStatis = url.pathname.startsWith('/build/')
        || url.pathname.startsWith('/icons/')
        || url.pathname === '/favicon.png'
        || url.pathname === '/favicon.ico'
        || url.pathname === '/manifest.webmanifest';

    if (!asetStatis) {
        return;
    }

    event.respondWith(
        caches.match(request).then((tersimpan) => tersimpan || fetch(request).then((response) => {
            if (response.ok) {
                const salinan = response.clone();

                caches.open(NAMA_CACHE).then((cache) => cache.put(request, salinan));
            }

            return response;
        })),
    );
});
