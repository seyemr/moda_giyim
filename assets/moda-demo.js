(() => {
	'use strict';

	const { isSafeImageData, normalizeProducts, normalizeSiteConfig, safeColor } = window.MODA_DEMO_UTILS;
	const storageKeys = { cart: 'moda-demo-cart-v1', favorites: 'moda-demo-favorites-v1', products: 'moda-demo-products-v1', site: 'moda-site-config-v1' };
	const memoryStore = {};
	const isDraftPreview = new URLSearchParams(window.location.search).has('draft-preview');
	const grid = document.querySelector('#product-grid');
	const cartDialog = document.querySelector('#cart-dialog');
	const messageDialog = document.querySelector('#message-dialog');
	const productDetailsDialog = document.querySelector('#product-details-dialog');
	const announcer = document.querySelector('#announcer');
	const filters = {
		search: document.querySelector('#header-search'),
		category: document.querySelector('#category-filter'),
		price: document.querySelector('#price-filter'),
		stock: document.querySelector('#stock-filter'),
		sort: document.querySelector('#sort-filter')
	};
	let products = loadCatalog();
	let siteConfig = loadSiteConfig();
	const selectedVariants = new Map();
	let detailsProductId = '';
	const storedCart = readStored(storageKeys.cart, []);
	const storedFavorites = readStored(storageKeys.favorites, []);
	let cart = Array.isArray(storedCart) ? storedCart.filter((item) => {
		const product = item && getProduct(item.productId);
		return product && product.sizes.includes(item.size) && Number.isInteger(item.quantity) && item.quantity > 0 && item.quantity <= product.stock;
	}) : [];
	let favorites = Array.isArray(storedFavorites) ? storedFavorites.filter((id) => Boolean(getProduct(id))) : [];
	let storageWarningShown = false;
	let favoritesOnly = false;

	function readStored(key, fallback) {
		try {
			const value = localStorage.getItem(key);
			return value ? JSON.parse(value) : fallback;
		} catch (error) {
			return memoryStore[key] || fallback;
		}
	}

	function loadCatalog() {
		const stored = readStored(storageKeys.products, null);
		const source = Array.isArray(stored) ? stored : window.MODA_DEMO_PRODUCTS;
		return normalizeProducts(source);
	}

	function loadSiteConfig() {
		try {
			const stored = localStorage.getItem(storageKeys.site);
			if (stored) return normalizeSiteConfig(JSON.parse(stored));
		} catch (error) {
			announce('Site ayarları okunamadı; varsayılan Moda Chicago içeriği gösteriliyor.');
		}
		return normalizeSiteConfig({});
	}

	function writeStored(key, value) {
		try {
			localStorage.setItem(key, JSON.stringify(value));
			memoryStore[key] = value;
		} catch (error) {
			memoryStore[key] = value;
			if (!storageWarningShown) {
				storageWarningShown = true;
				announce('Tarayıcı depolaması kullanılamıyor; demo sepeti bu sayfa açıkken saklanacak.');
			}
		}
	}

	function announce(message) {
		announcer.textContent = '';
		window.setTimeout(() => { announcer.textContent = message; }, 20);
	}

	function formatPrice(amount) {
		return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', maximumFractionDigits: 0 }).format(amount);
	}

	function escapeText(value) {
		return String(value).replace(/[&<>"']/g, (character) => ({
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#39;'
		})[character]);
	}

	function productStyleId(product) {
		return product.styleId || product.id;
	}

	function styleVariants(product) {
		const styleId = productStyleId(product);
		return products.filter((variant) => productStyleId(variant) === styleId);
	}

	function productImages(product) {
		const images = Array.isArray(product.images) ? product.images : [product.image];
		return images.filter(isSafeImageData).slice(0, 6);
	}

	function applySiteConfig(config) {
		siteConfig = config;
		const setText = (selector, value) => {
			const element = document.querySelector(selector);
			if (element && typeof value === 'string') element.textContent = value;
		};
		document.querySelector('#site-announcement').textContent = config.announcement;
		document.querySelectorAll('[data-site-brand-name]').forEach((element) => { element.textContent = config.brandName; });
		document.querySelectorAll('[data-site-brand-subtitle]').forEach((element) => { element.textContent = config.brandSubtitle; });
		document.querySelectorAll('.brand[aria-label]').forEach((element) => { element.setAttribute('aria-label', `${config.brandName} ${config.brandSubtitle} ana sayfa`); });
		document.title = config.siteTitle || `${config.brandName} ${config.brandSubtitle} — Zamansız parçalar`;
		document.querySelector('meta[name="description"]').content = config.siteDescription || `${config.brandName} ${config.brandSubtitle} demo mağazası — ${config.heroDescription}`;
		document.querySelector('meta[name="theme-color"]').content = safeColor(config.blue, '#4aa8c8');
		document.querySelectorAll('[data-site-nav]').forEach((element) => {
			const key = { season: 'navSeason', categories: 'navCategories', story: 'navStory', favorites: 'navFavorites' }[element.dataset.siteNav];
			if (key) element.textContent = config[key];
		});
		[
			['#site-hero-eyebrow', config.heroEyebrow],
			['#site-hero-title-top', config.heroTitleTop],
			['#site-hero-title-bottom', config.heroTitleBottom],
			['#site-hero-description', config.heroDescription],
			['#site-hero-footnote', config.heroFootnote],
			['#site-hero-caption-left', config.heroCaptionLeft],
			['#site-hero-caption-right', config.heroCaptionRight],
			['#site-category-eyebrow', config.categoryEyebrow],
			['#category-title', config.categoryTitle],
			['#site-category-link-text', config.categoryLink],
			['#site-collection-eyebrow', config.collectionEyebrow],
			['#collection-title', config.collectionTitle],
			['#site-collection-note', config.collectionNote],
			['#site-story-eyebrow', config.storyEyebrow],
			['#site-story-title-top', config.storyTitleTop],
			['#site-story-title-bottom', config.storyTitleBottom],
			['#site-story-body', config.storyBody],
			['#site-story-link-text', config.storyLink],
			['#site-info-eyebrow', config.infoEyebrow],
			['#site-info-title-top', config.infoTitleTop],
			['#site-info-title-bottom', config.infoTitleBottom],
			['#site-info-body', config.infoBody],
			['#site-info-link-text', config.infoLink],
			['#site-hero-cta-text', config.heroCta]
		].forEach(([selector, value]) => setText(selector, value));
		config.categoryNames.forEach((name, index) => setText(`[data-site-category="${index}"]`, name));
		filters.category.querySelectorAll('option[data-site-category-option]').forEach((option, index) => {
			option.textContent = config.categoryNames[index];
		});
		[
			['#kategoriler', config.showCategories],
			['#site-nav-categories', config.showCategories],
			['#hikaye', config.showStory],
			['#site-nav-story', config.showStory],
			['#demo-bilgi', config.showInfo],
			['#site-footer-info', config.showInfo]
		].forEach(([selector, visible]) => {
			const element = document.querySelector(selector);
			if (element) element.hidden = visible === false;
		});
		document.querySelector('#site-footer-description').textContent = config.footerDescription;
		document.querySelectorAll('[data-site-footer-link]').forEach((element) => {
			const key = element.dataset.siteFooterLink;
			if (typeof config[key] === 'string') element.textContent = config[key];
		});
		document.documentElement.style.setProperty('--chicago-blue', safeColor(config.blue, '#4aa8c8'));
		document.documentElement.style.setProperty('--chicago-red', safeColor(config.red, '#c7464f'));
		const targets = { '#koleksiyon': '#koleksiyon', '#kategoriler': '#kategoriler', '#hikaye': '#hikaye', '#demo-bilgi': '#demo-bilgi' };
		document.querySelector('#site-hero-cta').setAttribute('href', targets[config.heroCtaTarget] || '#koleksiyon');
		const heroImage = isSafeImageData(config.heroImage) ? config.heroImage : '';
		document.querySelector('.hero-art').classList.toggle('has-custom-image', Boolean(heroImage));
		document.querySelector('.hero-art').style.backgroundImage = heroImage
			? `linear-gradient(0deg,rgba(16,24,32,.24),rgba(16,24,32,.08)),url("${heroImage}")`
			: '';
		const storyImage = isSafeImageData(config.storyImage) ? config.storyImage : '';
		document.querySelector('.story-art').classList.toggle('has-custom-image', Boolean(storyImage));
		document.querySelector('.story-art').style.backgroundImage = storyImage
			? `linear-gradient(0deg,rgba(16,24,32,.2),rgba(16,24,32,.05)),url("${storyImage}")`
			: '';
		document.querySelectorAll('.category-tile').forEach((tile, index) => {
			const image = Array.isArray(config.categoryImages) && isSafeImageData(config.categoryImages[index])
				? config.categoryImages[index]
				: '';
			tile.classList.toggle('has-custom-image', Boolean(image));
			tile.style.backgroundImage = image ? `linear-gradient(0deg,rgba(16,24,32,.72),rgba(16,24,32,.04)),url("${image}")` : '';
		});
	}

	function filteredProducts() {
		const term = filters.search.value.trim().toLocaleLowerCase('tr-TR');
		let result = products.filter((product) => {
			const matchesTerm = !term || `${product.name} ${product.category} ${product.color}`.toLocaleLowerCase('tr-TR').includes(term);
			const matchesCategory = !filters.category.value || product.category === filters.category.value;
			const matchesStock = !filters.stock.checked || product.stock > 0;
			const matchesFavorites = !favoritesOnly || favorites.includes(product.id);
			let matchesPrice = true;
			if (filters.price.value) {
				const [minimum, maximum] = filters.price.value.split('-').map(Number);
				matchesPrice = product.price >= minimum && product.price < maximum;
			}
			return matchesTerm && matchesCategory && matchesStock && matchesPrice && matchesFavorites;
		});

		switch (filters.sort.value) {
			case 'price-asc':
				result = result.sort((a, b) => a.price - b.price);
				break;
			case 'price-desc':
				result = result.sort((a, b) => b.price - a.price);
				break;
			case 'popular':
				result = result.sort((a, b) => b.sales - a.sales);
				break;
			case 'newest':
				result = result.sort((a, b) => b.newest - a.newest);
				break;
			default:
				result = result.sort((a, b) => b.newest - a.newest);
		}
		return result;
	}

	function renderProducts() {
		const result = filteredProducts();
		const resultCount = document.querySelector('#result-count');
		const emptyState = document.querySelector('#empty-state');
		resultCount.textContent = favoritesOnly ? `${result.length} favori demo ürün` : `${result.length} demo ürün`;
		emptyState.hidden = result.length !== 0;
		emptyState.textContent = favoritesOnly && favorites.length === 0
			? 'Henüz favori eklemedin. Ürün kartlarındaki kalp simgesini kullan.'
			: 'Bu seçimle eşleşen demo ürün yok. Filtreleri değiştirip tekrar dene.';

		const styles = new Map();
		result.forEach((product) => {
			const styleId = productStyleId(product);
			if (!styles.has(styleId)) styles.set(styleId, []);
			styles.get(styleId).push(product);
		});
		resultCount.textContent = favoritesOnly ? `${styles.size} favori demo ürün` : `${styles.size} demo ürün`;
		grid.innerHTML = [...styles.entries()].map(([styleId, variants]) => {
			const selectedId = selectedVariants.get(styleId);
			const product = variants.find((variant) => variant.id === selectedId) || variants[0];
			selectedVariants.set(styleId, product.id);
			const isFavorite = favorites.includes(product.id);
			const badge = product.badge ? `<span class="product-badge">${escapeText(product.badge)}</span>` : '';
			const price = product.oldPrice
				? `<span>${formatPrice(product.price)}</span><del>${formatPrice(product.oldPrice)}</del>`
				: `<span>${formatPrice(product.price)}</span>`;
			const sizes = product.sizes.map((size) => `<option value="${escapeText(size)}">${escapeText(size)}</option>`).join('');
			const firstImage = productImages(product)[0];
			const image = firstImage
				? `<img class="product-photo" src="${firstImage}" alt="${escapeText(product.name)} ürün fotoğrafı" loading="lazy">`
				: `<div class="product-visual ${escapeText(product.art)}" role="img" aria-label="${escapeText(product.name)} için demo illüstrasyonu" style="--product-color:${safeColor(product.colorHex, '#35332f')};--product-bg:${safeColor(product.bg, '#ded6c9')}"></div>`;
			return `
				<article class="product-card" data-product-style="${escapeText(styleId)}">
					<div class="product-image">
						<button class="product-image-trigger" type="button" data-product-details="${escapeText(product.id)}" aria-label="${escapeText(product.name)} ürün bilgilerini göster">${image}</button>
						${badge}
						<span class="product-stock-badge">${product.stock > 0 ? `Demo stoğu: ${product.stock}` : 'Demo stoğunda yok'}</span>
						<button class="favorite-toggle" type="button" data-favorite="${escapeText(product.id)}" aria-pressed="${isFavorite}" aria-label="${isFavorite ? 'Favorilerden çıkar' : 'Favorilere ekle'}: ${escapeText(product.name)}">${isFavorite ? '♥' : '♡'}</button>
					</div>
					<div class="product-info">
						<p class="product-category">${escapeText(product.category)}</p>
						<h3 class="product-title">${escapeText(product.name)}</h3>
						<p class="product-color">${variants.map((variant) => `<button class="variant-swatch" type="button" data-select-variant="${escapeText(styleId)}" data-variant-id="${escapeText(variant.id)}" style="--swatch-color:${safeColor(variant.colorHex, '#35332f')}" aria-label="${escapeText(variant.color)} rengini seç" aria-pressed="${variant.id === product.id}" title="${escapeText(variant.color)}"></button>`).join('')}<span>${escapeText(product.color)}${variants.length > 1 ? ` · ${variants.length} renk` : ' · demo rengi'}</span></p>
						<div class="product-buy-row">
							<span class="product-price">${price}</span>
							<label><span class="sr-only">${escapeText(product.name)} beden seçimi</span><select class="size-select" data-size="${escapeText(product.id)}" aria-label="${escapeText(product.name)} beden seçimi">${sizes}</select></label>
							<button class="add-button" type="button" data-add="${escapeText(product.id)}" ${product.stock < 1 ? 'disabled' : ''}>${product.stock > 0 ? 'Çantaya ekle +' : 'Stokta yok'}</button>
						</div>
					</div>
				</article>`;
		}).join('');
	}

	function getProduct(id) {
		return products.find((product) => product.id === id);
	}

	function showProductDetails(productId) {
		const product = getProduct(productId);
		if (!product) return;
		detailsProductId = product.id;
		selectedVariants.set(productStyleId(product), product.id);

		const variants = styleVariants(product);
		const images = productImages(product);
		const image = images.length
			? `<div class="product-details-gallery">
				<div class="product-details-main-image">
					<img class="product-details-photo" id="details-gallery-image" src="${images[0]}" alt="${escapeText(product.name)} ürün görseli 1">
					${images.length > 1 ? `<button class="gallery-step gallery-step-prev" type="button" data-gallery-step="-1" aria-label="Önceki ürün görseli">‹</button><button class="gallery-step gallery-step-next" type="button" data-gallery-step="1" aria-label="Sonraki ürün görseli">›</button>` : ''}
				</div>
				${images.length > 1 ? `<div class="product-gallery-thumbnails" aria-label="Ürün fotoğrafları">${images.map((src, index) => `<button class="product-gallery-thumbnail" type="button" data-gallery-image="${index}" aria-label="Ürün görseli ${index + 1} göster" aria-pressed="${index === 0}"><img src="${src}" alt=""></button>`).join('')}</div>` : ''}
			</div>`
			: `<div class="product-details-visual ${escapeText(product.art)}" role="img" aria-label="${escapeText(product.name)} için demo illüstrasyonu" style="--product-color:${safeColor(product.colorHex, '#35332f')};--product-bg:${safeColor(product.bg, '#ded6c9')}"></div>`;
		const previousPrice = product.oldPrice ? `<del>${formatPrice(product.oldPrice)}</del>` : '';
		const badge = product.badge ? `<span class="product-details-badge">${escapeText(product.badge)}</span>` : '';
		document.querySelector('#product-details-content').innerHTML = `
			<div class="product-details-art">${image}${badge}</div>
			<div class="product-details-copy">
				<p class="eyebrow">${escapeText(product.category)} · demo ürün</p>
				<h2 id="product-details-title">${escapeText(product.name)}</h2>
				<p class="product-details-description">Bu demo katalog kaydı için görüntülenen ürün bilgileri örnektir; ürün içeriği ve stok gerçek mağaza verisi değildir.</p>
				<div class="product-details-price"><strong>${formatPrice(product.price)}</strong>${previousPrice}</div>
				${variants.length > 1 ? `<div class="product-details-colors"><span>Renk: ${escapeText(product.color)}</span><div>${variants.map((variant) => `<button class="variant-swatch" type="button" data-detail-variant="${escapeText(variant.id)}" style="--swatch-color:${safeColor(variant.colorHex, '#35332f')}" aria-label="${escapeText(variant.color)} rengini göster" aria-pressed="${variant.id === product.id}" title="${escapeText(variant.color)}"></button>`).join('')}</div></div>` : ''}
				<dl class="product-details-facts">
					<div><dt>Renk</dt><dd><span class="color-swatch" style="background:${safeColor(product.colorHex, '#35332f')}" aria-hidden="true"></span>${escapeText(product.color)} · demo rengi</dd></div>
					<div><dt>Beden seçenekleri</dt><dd>${escapeText(product.sizes.join(', '))}</dd></div>
					<div><dt>Stok bilgisi</dt><dd>${product.stock > 0 ? `${product.stock} adet (demo)` : 'Stokta yok (demo)'}</dd></div>
				</dl>
				<button class="button button-dark product-details-close" type="button" data-close-product-details>Alışverişe dön <span aria-hidden="true">↗</span></button>
			</div>`;
		if (!productDetailsDialog.open) productDetailsDialog.showModal();
		document.body.classList.add('dialog-open');
	}

	function setGalleryImage(index) {
		const product = getProduct(detailsProductId);
		if (!product) return;
		const images = productImages(product);
		if (!images.length) return;
		const activeIndex = (index + images.length) % images.length;
		const image = document.querySelector('#details-gallery-image');
		image.src = images[activeIndex];
		image.alt = `${product.name} ürün görseli ${activeIndex + 1}`;
		document.querySelectorAll('[data-gallery-image]').forEach((button) => {
			button.setAttribute('aria-pressed', String(Number(button.dataset.galleryImage) === activeIndex));
		});
	}

	function addToCart(productId) {
		const product = getProduct(productId);
		if (!product || product.stock < 1) {
			announce('Bu demo ürün şu anda stokta yok.');
			return;
		}
		const card = grid.querySelector(`[data-product-style="${CSS.escape(productStyleId(product))}"]`);
		if (!card) return;
		const size = card.querySelector(`[data-size="${CSS.escape(productId)}"]`).value;
		const line = cart.find((item) => item.productId === productId && item.size === size);
		if (line) {
			if (line.quantity >= product.stock) {
				announce(`Demo stok sınırı: ${product.stock} adet.`);
				return;
			}
			line.quantity += 1;
		} else {
			cart.push({ productId, size, quantity: 1 });
		}
		saveCart();
		announce(`${product.name}, ${size} beden olarak demo çantasına eklendi.`);
	}

	function saveCart() {
		writeStored(storageKeys.cart, cart);
		renderCart();
	}

	function cartQuantity() {
		return cart.reduce((total, item) => total + item.quantity, 0);
	}

	function renderCart() {
		const count = cartQuantity();
		document.querySelector('#cart-count').textContent = count;
		document.querySelector('#cart-count').setAttribute('aria-label', `Sepette ${count} demo ürün`);
		document.querySelector('#cart-empty').hidden = cart.length > 0;
		document.querySelector('#cart-summary').hidden = cart.length === 0;

		const cartItems = document.querySelector('#cart-items');
		const validItems = cart.filter((item) => getProduct(item.productId));
		cart = validItems;
		cartItems.innerHTML = cart.map((item) => {
			const product = getProduct(item.productId);
			const thumbImage = productImages(product)[0];
			const thumb = thumbImage
				? `<img src="${thumbImage}" alt="" loading="lazy">`
				: '<span></span>';
			return `
				<article class="cart-line">
					<div class="cart-thumb" style="--product-color:${safeColor(product.colorHex, '#35332f')};--product-bg:${safeColor(product.bg, '#ded6c9')}" aria-hidden="true">${thumb}</div>
					<div class="cart-line-info">
						<h3>${escapeText(product.name)}</h3>
						<p>${escapeText(item.size)} beden · ${escapeText(product.color)} · demo</p>
						<div class="quantity-control" aria-label="${escapeText(product.name)} demo miktarı">
							<button type="button" data-quantity="-1" data-line="${escapeText(item.productId)}" data-size="${escapeText(item.size)}" aria-label="Adedi azalt">−</button>
							<span>${item.quantity}</span>
							<button type="button" data-quantity="1" data-line="${escapeText(item.productId)}" data-size="${escapeText(item.size)}" aria-label="Adedi artır">+</button>
						</div>
					</div>
					<div class="cart-line-side"><strong>${formatPrice(product.price * item.quantity)}</strong><button class="remove-item" type="button" data-remove="${escapeText(item.productId)}" data-size="${escapeText(item.size)}">Kaldır</button></div>
				</article>`;
		}).join('');

		const subtotal = cart.reduce((total, item) => total + getProduct(item.productId).price * item.quantity, 0);
		document.querySelector('#cart-subtotal').textContent = formatPrice(subtotal);
	}

	function toggleFavorite(productId) {
		favorites = favorites.includes(productId)
			? favorites.filter((id) => id !== productId)
			: [...favorites, productId];
		writeStored(storageKeys.favorites, favorites);
		document.querySelector('#favorite-count').textContent = favorites.length;
		renderProducts();
		announce(`Favori listende ${favorites.length} demo ürün var.`);
	}

	function showMessage(title, copy) {
		document.querySelector('#message-title').textContent = title;
		document.querySelector('#message-copy').textContent = copy;
		messageDialog.showModal();
		document.body.classList.add('dialog-open');
	}

	function closeDialog(dialog) {
		if (dialog.open) dialog.close();
		syncDialogScrollLock();
	}

	function syncDialogScrollLock() {
		const hasOpenDialog = [cartDialog, messageDialog, productDetailsDialog].some((dialog) => dialog.open);
		document.body.classList.toggle('dialog-open', hasOpenDialog);
	}

	grid.addEventListener('click', (event) => {
		const addButton = event.target.closest('[data-add]');
		const favoriteButton = event.target.closest('[data-favorite]');
		const detailsButton = event.target.closest('[data-product-details]');
		const variantButton = event.target.closest('[data-select-variant]');
		if (variantButton) {
			const { selectVariant: styleId, variantId } = variantButton.dataset;
			selectedVariants.set(styleId, variantId);
			renderProducts();
			[...grid.querySelectorAll('[data-select-variant]')]
				.find((button) => button.dataset.variantId === variantId)
				?.focus();
		}
		if (addButton) addToCart(addButton.dataset.add);
		if (favoriteButton) toggleFavorite(favoriteButton.dataset.favorite);
		if (detailsButton) showProductDetails(detailsButton.dataset.productDetails);
	});

	productDetailsDialog.addEventListener('click', (event) => {
		const variantButton = event.target.closest('[data-detail-variant]');
		const imageButton = event.target.closest('[data-gallery-image]');
		const stepButton = event.target.closest('[data-gallery-step]');
		if (variantButton) {
			const variantId = variantButton.dataset.detailVariant;
			const variant = getProduct(variantId);
			if (variant) selectedVariants.set(productStyleId(variant), variant.id);
			renderProducts();
			showProductDetails(variantId);
			[...productDetailsDialog.querySelectorAll('[data-detail-variant]')]
				.find((button) => button.dataset.detailVariant === variantId)
				?.focus();
		} else if (imageButton) {
			setGalleryImage(Number(imageButton.dataset.galleryImage));
		} else if (stepButton) {
			const activeIndex = Number(document.querySelector('[data-gallery-image][aria-pressed="true"]')?.dataset.galleryImage || 0);
			setGalleryImage(activeIndex + Number(stepButton.dataset.galleryStep));
		}
	});

	document.querySelector('#cart-items').addEventListener('click', (event) => {
		const quantityButton = event.target.closest('[data-quantity]');
		const removeButton = event.target.closest('[data-remove]');
		if (quantityButton) {
			const item = cart.find((entry) => entry.productId === quantityButton.dataset.line && entry.size === quantityButton.dataset.size);
			const product = item && getProduct(item.productId);
			if (!item || !product) return;
			const nextQuantity = item.quantity + Number(quantityButton.dataset.quantity);
			if (nextQuantity < 1) {
				cart = cart.filter((entry) => entry !== item);
			} else if (nextQuantity > product.stock) {
				announce(`Demo stok sınırı: ${product.stock} adet.`);
				return;
			} else {
				item.quantity = nextQuantity;
			}
			saveCart();
		}
		if (removeButton) {
			cart = cart.filter((entry) => !(entry.productId === removeButton.dataset.remove && entry.size === removeButton.dataset.size));
			saveCart();
			announce('Ürün demo çantasından kaldırıldı.');
		}
	});

	Object.values(filters).forEach((filter) => {
		filter.addEventListener(filter === filters.search ? 'input' : 'change', renderProducts);
	});

	document.querySelectorAll('[data-category-link]').forEach((link) => {
		link.addEventListener('click', () => {
			favoritesOnly = false;
			document.querySelector('#collection-title').textContent = siteConfig.collectionTitle;
			document.querySelector('#favorite-shortcut').setAttribute('aria-pressed', 'false');
			filters.category.value = link.dataset.categoryLink;
			renderProducts();
		});
	});

	document.querySelector('#open-cart').addEventListener('click', () => {
		renderCart();
		cartDialog.showModal();
		document.body.classList.add('dialog-open');
	});

	document.querySelectorAll('[data-close-dialog]').forEach((button) => {
		button.addEventListener('click', () => {
			closeDialog(button.closest('dialog'));
			if (button.closest('dialog') === cartDialog && button.getAttribute('href')) {
				document.querySelector(button.getAttribute('href'))?.scrollIntoView();
			}
		});
	});

	productDetailsDialog.addEventListener('click', (event) => {
		if (event.target === productDetailsDialog || event.target.closest('[data-close-product-details]')) {
			closeDialog(productDetailsDialog);
		}
	});

	[cartDialog, messageDialog, productDetailsDialog].forEach((dialog) => {
		dialog.addEventListener('click', (event) => {
			if (event.target === dialog) closeDialog(dialog);
		});
		dialog.addEventListener('keydown', (event) => {
			if (event.key === 'Escape') {
				event.preventDefault();
				closeDialog(dialog);
			}
		});
		dialog.addEventListener('close', syncDialogScrollLock);
	});

	document.querySelector('#demo-checkout').addEventListener('click', () => {
		closeDialog(cartDialog);
		showMessage('Sipariş oluşturulmadı.', 'Bu statik demo ödeme almaz, gerçek sipariş veya müşteri hesabı oluşturmaz. Gerçek satış için WooCommerce ve bir ödeme sağlayıcısı kurulmalıdır.');
	});

	function toggleFavoritesView() {
		setFavoritesView(!favoritesOnly);
	}

	function setFavoritesView(active) {
		favoritesOnly = active;
		document.querySelector('#favorite-shortcut').setAttribute('aria-pressed', String(favoritesOnly));
		filters.search.value = '';
		filters.category.value = '';
		filters.price.value = '';
		filters.stock.checked = false;
		filters.sort.value = 'featured';
		document.querySelector('#collection-title').textContent = favoritesOnly ? `${siteConfig.collectionTitle} · Favoriler` : siteConfig.collectionTitle;
		renderProducts();
		document.querySelector('#koleksiyon').scrollIntoView({ behavior: 'smooth' });
	}
	document.querySelector('#favorite-shortcut').addEventListener('click', toggleFavoritesView);

	document.querySelector('#mobile-favorites-link').addEventListener('click', (event) => {
		event.preventDefault();
		toggleMobileMenu(false);
		setFavoritesView(true);
	});

	function toggleMobileMenu(forceOpen) {
		const button = document.querySelector('#mobile-menu-button');
		const nav = document.querySelector('#primary-nav');
		const open = typeof forceOpen === 'boolean' ? forceOpen : button.getAttribute('aria-expanded') !== 'true';
		button.setAttribute('aria-expanded', String(open));
		button.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
		nav.classList.toggle('is-open', open);
		document.querySelector('#mobile-nav-backdrop').hidden = !open;
	}

	document.querySelector('#mobile-menu-button').addEventListener('click', () => toggleMobileMenu());

	document.querySelectorAll('#primary-nav a').forEach((link) => {
		link.addEventListener('click', () => {
			toggleMobileMenu(false);
		});
	});

	document.querySelector('#mobile-nav-backdrop').addEventListener('click', () => {
		toggleMobileMenu(false);
	});

	document.querySelector('.search-trigger').addEventListener('click', () => {
		const search = document.querySelector('.search-box');
		search.classList.toggle('is-open');
		if (search.classList.contains('is-open')) filters.search.focus();
	});
	filters.search.addEventListener('keydown', (event) => {
		if (event.key === 'Enter') document.querySelector('#koleksiyon').scrollIntoView({ behavior: 'smooth' });
	});

	function refreshCatalog(value) {
		if (!Array.isArray(value)) return;
		products = normalizeProducts(value);
		cart = cart.filter((item) => {
			const product = getProduct(item.productId);
			return product && product.sizes.includes(item.size) && item.quantity <= product.stock;
		});
		writeStored(storageKeys.cart, cart);
		favorites = favorites.filter((id) => Boolean(getProduct(id)));
		writeStored(storageKeys.favorites, favorites);
		if (detailsProductId && !getProduct(detailsProductId)) {
			closeDialog(productDetailsDialog);
			detailsProductId = '';
		}
		document.querySelector('#favorite-count').textContent = favorites.length;
		renderProducts();
		renderCart();
	}

	window.addEventListener('storage', (event) => {
		if (event.key === storageKeys.site) {
			try {
				applySiteConfig(normalizeSiteConfig(event.newValue ? JSON.parse(event.newValue) : {}));
			} catch (error) {
				announce('Yönetim panelinden gelen site içeriği okunamadı.');
			}
			return;
		}
		if (event.key !== storageKeys.products) return;
		try {
			refreshCatalog(event.newValue ? JSON.parse(event.newValue) : window.MODA_DEMO_PRODUCTS);
		} catch (error) {
			announce('Yönetim panelinden gelen demo ürün değişikliği okunamadı.');
		}
	});
	window.addEventListener('message', (event) => {
		if (!isDraftPreview || event.source !== window.parent || event.origin !== window.location.origin) return;
		if (event.data?.type !== 'moda-site-draft-preview' || !event.data.config) return;
		applySiteConfig(normalizeSiteConfig(event.data.config));
	});
	window.addEventListener('moda-demo-catalog-updated', (event) => refreshCatalog(event.detail));

	document.querySelector('#draft-preview-notice').hidden = !isDraftPreview;
	applySiteConfig(siteConfig);
	document.querySelector('#favorite-count').textContent = favorites.length;
	renderProducts();
	renderCart();
})();
