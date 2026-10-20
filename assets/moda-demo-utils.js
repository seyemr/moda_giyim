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
		const previousProducts = {
			'linen-shirt': { name: 'Keten karışımlı gömlek', updatedName: 'Chicago fit keten gömlek', category: 'Gömlek & Tişört', oldSizes: ['XS', 'S', 'M', 'L'], newSizes: ['S', 'M', 'L', 'XL'] },
			'relaxed-trouser': { name: 'Rahat kesim pantolon', updatedName: 'Şehir kesim chino pantolon', category: 'Pantolon', oldSizes: ['S', 'M', 'L', 'XL'], newSizes: ['30', '32', '34', '36'] },
			'soft-knit': { name: 'Yumuşak dokulu triko', updatedName: 'Dokulu erkek triko', category: 'Gömlek & Tişört', oldSizes: ['XS', 'S', 'M', 'L'], newSizes: ['S', 'M', 'L', 'XL'] },
			'long-coat': { name: 'Yün karışımlı uzun kaban', updatedName: 'Chicago yün karışımlı kaban', category: 'Ceket & Mont', oldSizes: ['S', 'M', 'L'], newSizes: ['S', 'M', 'L', 'XL'] },
			'daily-tshirt': { name: 'Günlük pamuklu tişört', updatedName: 'Ağır gramaj pamuklu tişört', category: 'Gömlek & Tişört', oldSizes: ['XS', 'S', 'M', 'L', 'XL'], newSizes: ['S', 'M', 'L', 'XL', 'XXL'] },
			'straight-jean': { name: 'Düz kesim denim', updatedName: 'Straight fit denim pantolon', category: 'Pantolon', oldSizes: ['S', 'M', 'L', 'XL'], newSizes: ['30', '32', '34', '36'] },
			'leather-bag': { name: 'Günlük deri görünümlü çanta', updatedName: 'Günlük şehir sırt çantası', category: 'Ayakkabı & Aksesuar' },
			'light-jacket': { name: 'Hafif dokulu ceket', updatedName: 'Hafif şehir ceketi', category: 'Ceket & Mont', oldSizes: ['S', 'M', 'L'], newSizes: ['S', 'M', 'L', 'XL'] }
		};
		const migrated = value.map((product) => {
			const previous = product && previousProducts[product.id];
			if (!previous || product.name !== previous.name) return product;
			const sameSizes = previous.oldSizes
				&& JSON.stringify(product.sizes) === JSON.stringify(previous.oldSizes);
			return {
				...product,
				name: previous.updatedName,
				category: previous.category,
				...(sameSizes ? { sizes: previous.newSizes } : {})
			};
		});
		const ids = new Set();
		return migrated.filter((product) => {
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
		const previousDefaults = {
			siteTitle: 'Moda Chicago — Zamansız parçalar',
			siteDescription: 'Moda Chicago demo mağazası — zamansız parçalar, modern bir stil.',
			navSeason: 'Yeni sezon',
			navCategories: 'Koleksiyonlar',
			navStory: 'Hikâyemiz',
			navFavorites: 'Favori demo ürünler',
			heroEyebrow: '2026 / Yeni sezon',
			heroTitleTop: 'Az ama',
			heroTitleBottom: 'özgün.',
			heroDescription: 'Her gün yeniden giymek isteyeceğin, özenle seçilmiş zamansız parçalar.',
			heroCta: 'Koleksiyonu keşfet',
			heroFootnote: 'Daha az, daha iyi.',
			heroCaptionLeft: 'STUDIO',
			heroCaptionRight: '01 / 26',
			categoryEyebrow: 'Kendine göre keşfet',
			categoryTitle: 'Gardırobun özü',
			categoryNames: ['Üst giyim', 'Alt giyim', 'Dış giyim', 'Aksesuar'],
			collectionEyebrow: 'Özenle seçildi',
			collectionTitle: 'Sezonun favorileri',
			collectionNote: 'İyi hissettiren, uzun süre seninle kalan parçalar.',
			storyEyebrow: 'Moda Chicago yaklaşımı',
			storyTitleTop: 'Seçerek yaşa.',
			storyTitleBottom: 'Uzun süre giy.',
			storyBody: 'Bu örnek mağaza deneyimi, koleksiyonu ve alışveriş arayüzünü göstermek için hazırlandı. Ürün ve stok bilgileri demo verisidir.',
			infoEyebrow: 'Önizleme mağazası',
			infoTitleTop: 'Gerçek alışveriş için',
			infoTitleBottom: 'mağaza kurulumu gerekir.',
			infoBody: 'Hesap, bülten, stok, sipariş, kargo ve ödeme sistemleri bu statik demoda bağlı değildir.',
			infoLink: 'Demo ürünlere dön',
			footerDescription: 'Zamansız parçalar.\nModern bir gardırop.',
			footerCollection: 'Koleksiyon',
			footerInfo: 'Demo bilgisi',
			blue: '#177eaa',
			red: '#c74448'
		};
		Object.entries(previousDefaults).forEach(([key, previousValue]) => {
			const isPreviousValue = Array.isArray(previousValue)
				? JSON.stringify(config[key]) === JSON.stringify(previousValue)
				: config[key] === previousValue;
			if (isPreviousValue) config[key] = defaults[key];
		});
		['#177eaa', '#4aa8c8'].forEach((previousColor) => {
			if (typeof config.blue === 'string' && config.blue.toLowerCase() === previousColor) config.blue = defaults.blue;
		});
		['#c74448', '#c7464f'].forEach((previousColor) => {
			if (typeof config.red === 'string' && config.red.toLowerCase() === previousColor) config.red = defaults.red;
		});
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
