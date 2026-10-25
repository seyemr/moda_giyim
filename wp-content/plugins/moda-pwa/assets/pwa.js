(function () {
	'use strict';

	var config = window.modaPwa;
	if (!config) {
		return;
	}

	var DISMISS_KEY = 'moda-pwa-dismissed';
	var deferredPrompt = null;
	var bar = null;

	function urlBase64ToUint8Array(base64) {
		var padding = '='.repeat((4 - (base64.length % 4)) % 4);
		var raw = atob((base64 + padding).replace(/-/g, '+').replace(/_/g, '/'));
		var output = new Uint8Array(raw.length);
		for (var i = 0; i < raw.length; i++) {
			output[i] = raw.charCodeAt(i);
		}
		return output;
	}

	function isStandalone() {
		return (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) || window.navigator.standalone === true;
	}

	function isIos() {
		return /iphone|ipad|ipod/i.test(window.navigator.userAgent) || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);
	}

	function canPush() {
		return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
	}

	function isDismissed() {
		try {
			return window.localStorage.getItem(DISMISS_KEY) === '1';
		} catch (error) {
			console.warn('Moda PWA dismissal preference could not be read.', error);
			return false;
		}
	}

	function dismiss() {
		try {
			window.localStorage.setItem(DISMISS_KEY, '1');
		} catch (error) {
			console.warn('Moda PWA dismissal preference could not be saved.', error);
		}
	}

	function post(path, payload) {
		return fetch(config.restUrl + path, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(payload),
		}).then(function (response) {
			if (!response.ok) {
				throw new Error('Moda PWA request failed with status ' + response.status);
			}
			return response;
		});
	}

	function subscribePush() {
		return navigator.serviceWorker.ready.then(function (registration) {
			return Notification.requestPermission().then(function (permission) {
				if (permission !== 'granted') {
					throw new Error('denied');
				}
				return registration.pushManager.getSubscription().then(function (existing) {
					return existing || registration.pushManager.subscribe({
						userVisibleOnly: true,
						applicationServerKey: urlBase64ToUint8Array(config.publicKey),
					});
				});
			});
		}).then(function (subscription) {
			return post('subscribe', { endpoint: subscription.endpoint });
		});
	}

	function hideBar() {
		if (bar) {
			bar.remove();
			bar = null;
		}
	}

	function render() {
		var installable = !isStandalone() && (deferredPrompt || isIos());
		var pushable = canPush() && config.publicKey && Notification.permission === 'default' && (isStandalone() || !isIos());

		if (bar || isDismissed() || (!installable && !pushable)) {
			return;
		}

		bar = document.createElement('div');
		bar.className = 'moda-pwa-bar';
		bar.setAttribute('role', 'region');
		bar.setAttribute('aria-label', 'Uygulama');

		var text = document.createElement('p');
		text.setAttribute('aria-live', 'polite');
		text.textContent = installable && isIos() && !deferredPrompt
			? 'Uygulamayı yüklemek için Paylaş menüsünden “Ana Ekrana Ekle”yi seçin.'
			: 'Moda Chicago’yu uygulama olarak kullanın.';
		bar.appendChild(text);

		var actions = document.createElement('div');
		actions.className = 'moda-pwa-actions';

		if (deferredPrompt) {
			var install = document.createElement('button');
			install.type = 'button';
			install.textContent = 'Yükle';
			install.addEventListener('click', function () {
				var promptEvent = deferredPrompt;
				promptEvent.prompt();
				promptEvent.userChoice.then(function () {
					deferredPrompt = null;
					hideBar();
					render();
				}).catch(function (error) {
					console.error('Moda PWA installation prompt failed.', error);
				});
			});
			actions.appendChild(install);
		}

		if (pushable) {
			var push = document.createElement('button');
			push.type = 'button';
			push.textContent = 'Bildirimleri aç';
			push.addEventListener('click', function () {
				subscribePush().then(hideBar).catch(function (error) {
					console.error('Moda PWA push subscription failed.', error);
					text.textContent = Notification.permission === 'denied'
						? 'Bildirim izni verilmedi. İzinleri tarayıcı ayarlarından değiştirebilirsiniz.'
						: 'Bildirimler açılamadı. Lütfen bağlantınızı kontrol edip tekrar deneyin.';
				});
			});
			actions.appendChild(push);
		}

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'moda-pwa-close';
		close.setAttribute('aria-label', 'Kapat');
		close.textContent = '×';
		close.addEventListener('click', function () {
			dismiss();
			hideBar();
		});

		bar.appendChild(actions);
		bar.appendChild(close);
		document.body.appendChild(bar);
	}

	function setupFooterInstall() {
		var button = document.querySelector('[data-moda-pwa-install]');
		var hint = document.querySelector('[data-moda-pwa-hint]');
		if (!button || isStandalone()) {
			return;
		}

		button.hidden = false;
		button.addEventListener('click', function () {
			if (deferredPrompt) {
				var promptEvent = deferredPrompt;
				promptEvent.prompt();
				promptEvent.userChoice.then(function () {
					deferredPrompt = null;
				}).catch(function (error) {
					console.error('Moda PWA installation prompt failed.', error);
				});
				return;
			}

			if (hint) {
				hint.textContent = isIos()
					? 'Safari’de Paylaş menüsünü açın ve “Ana Ekrana Ekle”yi seçin.'
					: 'Tarayıcı menüsünden (⋮) “Uygulamayı yükle” veya “Ana ekrana ekle”yi seçin.';
				hint.hidden = false;
			}
		});
		window.addEventListener('appinstalled', function () {
			button.hidden = true;
			if (hint) {
				hint.hidden = true;
			}
		});
	}

	window.addEventListener('beforeinstallprompt', function (event) {
		event.preventDefault();
		deferredPrompt = event;
		hideBar();
		render();
	});

	window.addEventListener('appinstalled', hideBar);

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', setupFooterInstall);
	} else {
		setupFooterInstall();
	}

	if ('serviceWorker' in navigator) {
		navigator.serviceWorker.register(config.swUrl, { scope: '/' }).then(function () {
			window.setTimeout(render, 4000);
		}).catch(function (error) {
			console.error('Moda PWA service worker registration failed.', error);
		});
	}
})();
