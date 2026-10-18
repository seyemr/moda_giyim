(() => {
	'use strict';

	const defaults = window.MODA_SITE_DEFAULTS;
	const allowedArt = ['art-shirt', 'art-pants', 'art-coat', 'art-bag', 'art-knit'];
	const allowedHeroTargets = ['#koleksiyon', '#kategoriler', '#hikaye', '#demo-bilgi'];

	function isSafeImageData(value) {
		return typeof value === 'string' && /^data:image\/(?:webp|png|jpeg);base64,[A-Za-z0-9+/]+=*$/i.test(value);
	}

	function safeColor(value, fallback) {
		return typeof value === 'string' && /^#[0-9a-f]{6}$/i.test(value) ? value : fallback;
	}

	function normalizeProducts(value) {
		if (!Array.isArray(value)) return [];
		const ids = new Set();
		return value.filter((product) => {
			if (!product || typeof product.id !== 'string' || !product.id.trim() || ids.has(product.id.trim())
				|| typeof product.name !== 'string' || !product.name.trim()
				|| typeof product.category !== 'string' || !product.category.trim()
				|| !Number.isInteger(product.price) || product.price < 1
				|| !Number.isInteger(product.stock) || product.stock < 0
				|| !Array.isArray(product.sizes) || !product.sizes.some((size) => typeof size === 'string' && size.trim())) return false;
			ids.add(product.id.trim());
			return true;
		}).map((product) => {
			const images = (Array.isArray(product.images) ? product.images : []).filter(isSafeImageData);
			if (!images.length && isSafeImageData(product.image)) images.push(product.image);
			return {
				...product,
				id: product.id.trim(),
				name: product.name.trim(),
				category: product.category.trim(),
				color: typeof product.color === 'string' && product.color.trim() ? product.color.trim() : 'Belirtilmemiş',
				colorHex: safeColor(product.colorHex, '#35332f'),
				bg: safeColor(product.bg, '#ded6c9'),
				styleId: typeof product.styleId === 'string' && product.styleId.trim() ? product.styleId.trim() : product.id.trim(),
				sizes: [...new Set(product.sizes.filter((size) => typeof size === 'string' && size.trim()).map((size) => size.trim()))],
				oldPrice: Number.isInteger(product.oldPrice) && product.oldPrice > product.price ? product.oldPrice : null,
				sales: Number.isFinite(product.sales) ? product.sales : 0,
				newest: Number.isFinite(product.newest) ? product.newest : 0,
				badge: typeof product.badge === 'string' ? product.badge : '',
				art: allowedArt.includes(product.art) ? product.art : 'art-shirt',
				images: images.slice(0, 6)
			};
		});
	}

	function normalizeSiteConfig(value) {
		const config = { ...defaults, ...(value && typeof value === 'object' ? value : {}) };
		Object.keys(config).forEach((key) => {
			if (typeof defaults[key] === 'string' && typeof config[key] !== 'string') config[key] = defaults[key];
		});
		config.categoryNames = Array.from({ length: 4 }, (_, index) => {
			const name = Array.isArray(config.categoryNames) ? config.categoryNames[index] : '';
			return typeof name === 'string' && name.trim() ? name.trim() : defaults.categoryNames[index];
		});
		config.categoryImages = Array.from({ length: 4 }, (_, index) => {
			const image = Array.isArray(config.categoryImages) ? config.categoryImages[index] : '';
			return isSafeImageData(image) ? image : '';
		});
		['showCategories', 'showStory', 'showInfo'].forEach((key) => {
			if (typeof config[key] !== 'boolean') config[key] = defaults[key];
		});
		config.heroImage = isSafeImageData(config.heroImage) ? config.heroImage : '';
		config.storyImage = isSafeImageData(config.storyImage) ? config.storyImage : '';
		config.blue = safeColor(config.blue, defaults.blue);
		config.red = safeColor(config.red, defaults.red);
		if (!allowedHeroTargets.includes(config.heroCtaTarget)) config.heroCtaTarget = '#koleksiyon';
		return config;
	}

	window.MODA_DEMO_UTILS = Object.freeze({ isSafeImageData, normalizeProducts, normalizeSiteConfig, safeColor });
})();
