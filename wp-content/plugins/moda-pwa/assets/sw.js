/* Moda Chicago PWA service worker. Placeholders are replaced by PHP. */
const VERSION = '__VERSION__';
const STATIC_CACHE = 'moda-static-' + VERSION;
const PAGE_CACHE = 'moda-pages-' + VERSION;
const OFFLINE_URL = '__OFFLINE_URL__';
const LATEST_URL = '__LATEST_URL__';
const ICON_URL = '__ICON_URL__';
const BYPASS = /\/(wp-admin|wp-json|wp-login\.php|sepet|odeme|hesabim|cart|checkout|my-account)(\/|$|\?)|[?&](wc-ajax|add-to-cart|moda_pwa)=|\/(moda-sw\.js|manifest\.webmanifest)/;

self.addEventListener('install', (event) => {
	event.waitUntil(
		caches.open(PAGE_CACHE).then((cache) => cache.add(OFFLINE_URL)).then(() => self.skipWaiting())
	);
});

self.addEventListener('activate', (event) => {
	event.waitUntil(
		caches.keys()
			.then((keys) => Promise.all(keys.filter((key) => key.startsWith('moda-') && key !== STATIC_CACHE && key !== PAGE_CACHE).map((key) => caches.delete(key))))
			.then(() => self.clients.claim())
	);
});

self.addEventListener('fetch', (event) => {
	const request = event.request;
	const url = new URL(request.url);

	if (request.method !== 'GET' || url.origin !== self.location.origin || BYPASS.test(url.pathname + url.search)) {
		return;
	}

	if (request.mode === 'navigate') {
		event.respondWith(
			fetch(request).catch(() => caches.match(OFFLINE_URL))
		);
		return;
	}

	if (/\.(?:css|js|png|jpe?g|webp|svg|gif|woff2?)$/i.test(url.pathname)) {
		event.respondWith(
			fetch(request).then((response) => {
				if (!response.ok) {
					return response;
				}
				return caches.open(STATIC_CACHE)
					.then((cache) => cache.put(request, response.clone()))
					.then(() => response, () => response);
			}).catch(() => caches.match(request))
		);
	}
});

self.addEventListener('push', (event) => {
	event.waitUntil(
		fetch(LATEST_URL, { cache: 'no-store' })
			.then((response) => response.json())
			.catch(() => ({}))
			.then((data) => self.registration.showNotification(data.title || 'Moda Chicago', {
				body: data.body || 'Yeni bir bildiriminiz var.',
				icon: ICON_URL,
				badge: ICON_URL,
				tag: 'moda-chicago',
				data: { url: data.url || '/' },
			}))
	);
});

self.addEventListener('notificationclick', (event) => {
	event.notification.close();
	const targetUrl = new URL(event.notification.data && event.notification.data.url ? event.notification.data.url : '/', self.location.origin);
	if (targetUrl.origin !== self.location.origin) {
		return;
	}
	const target = targetUrl.href;

	event.waitUntil(
		self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
			for (const client of windows) {
				if ('focus' in client) {
					client.navigate(target);
					return client.focus();
				}
			}
			return self.clients.openWindow(target);
		})
	);
});
