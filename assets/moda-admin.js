(() => {
	'use strict';

	const { isSafeImageData, normalizeProducts, normalizeSiteConfig, safeColor } = window.MODA_DEMO_UTILS;
	const catalogKey = 'moda-demo-products-v1';
	const siteKey = 'moda-site-config-v1';
	const currency = new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY', maximumFractionDigits: 0 });
	const body = document.querySelector('#admin-product-rows');
	const formDialog = document.querySelector('#product-dialog');
	const form = document.querySelector('#product-form');
	const messageDialog = document.querySelector('#admin-message');
	const announcer = document.querySelector('#admin-announcer');
	let products = loadProducts();
	let selectedImages = [];
	const maxImageBytes = 8 * 1024 * 1024;
	const maxStoredImageBytes = 350 * 1024;
	const maxProductImages = 6;

	function defaultProducts() {
		return window.MODA_DEMO_PRODUCTS.map((product) => ({ ...product, sizes: [...product.sizes] }));
	}

	function renderImagePreviews() {
		document.querySelector('#image-gallery-preview').innerHTML = selectedImages.map((image, index) => `
			<div class="image-preview-item">
				<img src="${image}" alt="Ürün görseli ${index + 1} önizlemesi">
				<button class="row-action is-delete" type="button" data-remove-image="${index}">Görseli kaldır</button>
			</div>`).join('');
	}

	function prepareImage(file, storedLimit = maxStoredImageBytes) {
		return new Promise((resolve, reject) => {
			if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
				reject(new Error('Yalnızca JPG, PNG veya WebP görsel yükleyebilirsin.'));
				return;
			}
			if (file.size > maxImageBytes) {
				reject(new Error('Görsel 8 MB sınırını aşıyor. Daha küçük bir dosya seç.'));
				return;
			}

			const reader = new FileReader();
			reader.onerror = () => reject(new Error('Görsel okunamadı. Dosyayı kontrol edip tekrar dene.'));
			reader.onload = () => {
				const image = new Image();
				image.onerror = () => reject(new Error('Seçilen dosya geçerli bir görsel olarak açılamadı.'));
				image.onload = () => {
					const scale = Math.min(1, 1200 / Math.max(image.naturalWidth, image.naturalHeight));
					const canvas = document.createElement('canvas');
					canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
					canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
					const context = canvas.getContext('2d');
					if (!context) {
						reject(new Error('Görsel küçültme bu tarayıcıda desteklenmiyor.'));
						return;
					}

					context.fillStyle = '#ffffff';
					context.fillRect(0, 0, canvas.width, canvas.height);
					context.drawImage(image, 0, 0, canvas.width, canvas.height);
					let data = canvas.toDataURL('image/webp', 0.8);
					if (!data.startsWith('data:image/webp;')) data = canvas.toDataURL('image/jpeg', 0.8);
					if (!isSafeImageData(data)) {
						reject(new Error('Görsel güvenli biçimde dönüştürülemedi.'));
						return;
					}
					if (data.length > storedLimit) {
						reject(new Error('Küçültülmüş görsel bile yerel depolama sınırına sığmıyor. Daha küçük/düşük çözünürlüklü görsel seç.'));
						return;
					}
					resolve(data);
				};
				image.src = reader.result;
			};
			reader.readAsDataURL(file);
		});
	}

	function defaultSiteConfig() {
		return { ...window.MODA_SITE_DEFAULTS, categoryNames: [...window.MODA_SITE_DEFAULTS.categoryNames], categoryImages: [...window.MODA_SITE_DEFAULTS.categoryImages] };
	}

	function loadSiteConfig() {
		try {
			const stored = localStorage.getItem(siteKey);
			if (stored) {
				return normalizeSiteConfig(JSON.parse(stored));
			}
		} catch (error) {
			showNotice('Site ayarları okunamadı. Varsayılan Moda Chicago içeriği düzenlenebilir.');
		}
		return defaultSiteConfig();
	}

	let siteConfig = loadSiteConfig();
	const siteForm = document.querySelector('#site-content-form');
	const sitePreviewDialog = document.querySelector('#site-preview-dialog');
	const sitePreviewFrame = document.querySelector('#site-preview-frame');
	const pendingSiteImages = {};
	const siteImageLimit = 250 * 1024;
	let previewUpdateTimer;

	function collectSiteDraft() {
		const updated = { ...siteConfig, categoryNames: [...siteConfig.categoryNames], categoryImages: [...siteConfig.categoryImages] };
		for (const [key, value] of new FormData(siteForm).entries()) {
			if (key.startsWith('categoryName')) updated.categoryNames[Number(key.slice('categoryName'.length))] = String(value).trim();
			else if (typeof value === 'string') updated[key] = value.trim();
		}
		siteForm.querySelectorAll('input[type="checkbox"]').forEach((field) => {
			updated[field.name] = field.checked;
		});
		return updated;
	}

	function sendSiteDraftPreview() {
		if (!sitePreviewDialog.open || !sitePreviewFrame.contentWindow) return;
		const targetOrigin = window.location.origin === 'null' ? '*' : window.location.origin;
		sitePreviewFrame.contentWindow.postMessage({
			type: 'moda-site-draft-preview',
			config: collectSiteDraft()
		}, targetOrigin);
	}

	function queueSiteDraftPreview() {
		window.clearTimeout(previewUpdateTimer);
		previewUpdateTimer = window.setTimeout(sendSiteDraftPreview, 150);
	}

	function getSiteImage(key) {
		const categoryMatch = key.match(/^categoryImage([0-3])$/);
		return categoryMatch ? siteConfig.categoryImages[Number(categoryMatch[1])] : siteConfig[key];
	}

	function renderSiteImagePreview(key) {
		const preview = document.querySelector(`[data-site-preview="${key}"]`);
		const image = getSiteImage(key);
		preview.innerHTML = isSafeImageData(image)
			? `<img src="${image}" alt="${escapeText(key)} önizlemesi"><button class="row-action is-delete" type="button" data-remove-site-image="${escapeText(key)}">Görseli kaldır</button>`
			: '<span>Görsel seçilmedi</span>';
	}

	function fillSiteForm(config) {
		config = normalizeSiteConfig(config);
		const values = { ...config };
		config.categoryNames.forEach((name, index) => { values[`categoryName${index}`] = name; });
		Object.entries(values).forEach(([key, value]) => {
			const field = siteForm.elements.namedItem(key);
			if (!field || /^categoryImage[0-3]$/.test(key) || ['heroImage', 'storyImage'].includes(key)) return;
			if (field.type === 'checkbox') field.checked = value !== false;
			else if (typeof value === 'string') field.value = value;
		});
		siteForm.elements.namedItem('heroCtaTarget').value = ['#koleksiyon', '#kategoriler', '#hikaye', '#demo-bilgi'].includes(config.heroCtaTarget) ? config.heroCtaTarget : '#koleksiyon';
		siteForm.querySelectorAll('[data-site-image]').forEach((input) => {
			input.value = '';
			renderSiteImagePreview(input.dataset.siteImage);
		});
		applyAdminSiteBranding(config);
	}

	function applyAdminSiteBranding(config) {
		document.querySelectorAll('.admin-topbar [data-site-brand-name]').forEach((element) => { element.textContent = config.brandName; });
		document.querySelectorAll('.admin-topbar [data-site-brand-subtitle]').forEach((element) => { element.textContent = config.brandSubtitle; });
		document.querySelector('#admin-site-brand').textContent = `${config.brandName} ${config.brandSubtitle} / Mağaza`;
		document.querySelector('.admin-topbar .brand').setAttribute('aria-label', `${config.brandName} ${config.brandSubtitle} yönetim paneli`);
		document.title = `Yönetim demosu — ${config.brandName} ${config.brandSubtitle}`;
		document.documentElement.style.setProperty('--chicago-blue', safeColor(config.blue, '#4aa8c8'));
		document.documentElement.style.setProperty('--chicago-red', safeColor(config.red, '#c7464f'));
	}

	fillSiteForm(siteConfig);

	siteForm.querySelectorAll('[data-site-image]').forEach((input) => {
		input.addEventListener('change', async () => {
			const file = input.files && input.files[0];
			if (!file) return;
			const error = document.querySelector('#site-content-error');
			error.hidden = true;
			try {
				pendingSiteImages[input.dataset.siteImage] = await prepareImage(file, siteImageLimit);
				const categoryMatch = input.dataset.siteImage.match(/^categoryImage([0-3])$/);
				if (categoryMatch) siteConfig.categoryImages[Number(categoryMatch[1])] = pendingSiteImages[input.dataset.siteImage];
				else siteConfig[input.dataset.siteImage] = pendingSiteImages[input.dataset.siteImage];
				renderSiteImagePreview(input.dataset.siteImage);
				document.querySelector('#site-content-status').textContent = 'Görsel önizlemesi hazır; kaydettiğinde mağazada yayınlanır.';
				sendSiteDraftPreview();
			} catch (imageError) {
				input.value = '';
				error.textContent = imageError.message;
				error.hidden = false;
			}
		});
	});

	document.querySelector('#site-content-form').addEventListener('click', (event) => {
		const removeButton = event.target.closest('[data-remove-site-image]');
		if (!removeButton) return;
		const key = removeButton.dataset.removeSiteImage;
		const categoryMatch = key.match(/^categoryImage([0-3])$/);
		if (categoryMatch) siteConfig.categoryImages[Number(categoryMatch[1])] = '';
		else siteConfig[key] = '';
		delete pendingSiteImages[key];
		document.querySelector(`[data-site-image="${key}"]`).value = '';
		renderSiteImagePreview(key);
		sendSiteDraftPreview();
	});

	document.querySelector('#preview-site-content').addEventListener('click', () => {
		sitePreviewDialog.showModal();
		sendSiteDraftPreview();
	});
	document.querySelector('#close-site-preview').addEventListener('click', () => sitePreviewDialog.close());
	sitePreviewFrame.addEventListener('load', sendSiteDraftPreview);
	siteForm.addEventListener('input', queueSiteDraftPreview);
	siteForm.addEventListener('change', queueSiteDraftPreview);

	siteForm.addEventListener('submit', (event) => {
		event.preventDefault();
		const error = document.querySelector('#site-content-error');
		const status = document.querySelector('#site-content-status');
		error.hidden = true;
		const updated = { ...siteConfig, categoryImages: [...siteConfig.categoryImages] };
		const data = new FormData(siteForm);
		for (const [key, value] of data.entries()) {
			if (key.startsWith('categoryName')) {
				updated.categoryNames[Number(key.slice('categoryName'.length))] = String(value).trim();
			} else if (typeof value === 'string') {
				updated[key] = value.trim();
			}
		}
		siteForm.querySelectorAll('input[type="checkbox"]').forEach((field) => {
			updated[field.name] = field.checked;
		});
		Object.entries(pendingSiteImages).forEach(([key, image]) => {
			const categoryMatch = key.match(/^categoryImage([0-3])$/);
			if (categoryMatch) updated.categoryImages[Number(categoryMatch[1])] = image;
			else updated[key] = image;
		});
		updated.categoryImages = updated.categoryImages.slice(0, 4).map((image) => isSafeImageData(image) ? image : '');
		const textFields = [...siteForm.querySelectorAll('input:not([type="file"]):not([type="color"]), textarea')];
		const invalid = textFields.some((field) => !field.value.trim())
			|| !/^#[0-9a-f]{6}$/i.test(updated.blue)
			|| !/^#[0-9a-f]{6}$/i.test(updated.red);
		if (invalid) {
			error.textContent = 'Zorunlu metin alanlarını doldur ve marka renklerini kontrol et.';
			error.hidden = false;
			return;
		}
		try {
			localStorage.setItem(siteKey, JSON.stringify(updated));
		} catch (storageError) {
			error.textContent = 'Site içeriği tarayıcı depolamasına kaydedilemedi. Kullanılabilir alanı artırıp daha küçük görsellerle tekrar dene.';
			error.hidden = false;
			return;
		}
		siteConfig = updated;
		Object.keys(pendingSiteImages).forEach((key) => { delete pendingSiteImages[key]; });
		window.dispatchEvent(new CustomEvent('moda-site-content-updated', { detail: updated }));
		applyAdminSiteBranding(updated);
		status.textContent = 'Site değişiklikleri kaydedildi. Mağaza sayfasını yenile veya açık mağaza sekmesine geç.';
		sendSiteDraftPreview();
	});

	document.querySelector('#reset-site-content').addEventListener('click', () => {
		if (!window.confirm('Ana sayfa metinleri, renkler ve görseller varsayılan Moda Chicago ayarlarına döndürülsün mü?')) return;
		const defaults = defaultSiteConfig();
		try {
			localStorage.setItem(siteKey, JSON.stringify(defaults));
		} catch (storageError) {
			showNotice('Varsayılan site içeriği tarayıcı depolamasına kaydedilemedi.');
			return;
		}
		siteConfig = defaults;
		Object.keys(pendingSiteImages).forEach((key) => { delete pendingSiteImages[key]; });
		fillSiteForm(siteConfig);
		document.querySelector('#site-content-error').hidden = true;
		document.querySelector('#site-content-status').textContent = 'Varsayılan site içeriği geri yüklendi.';
		sendSiteDraftPreview();
	});

	function loadProducts() {
		try {
			const stored = localStorage.getItem(catalogKey);
			if (stored) {
				const parsed = JSON.parse(stored);
				if (Array.isArray(parsed)) return normalizeProducts(parsed);
			}
		} catch (error) {
			showNotice('Tarayıcı depolaması okunamadı. Bu tarayıcıda ürün değişiklikleri kalıcı olmayabilir.');
		}
		return defaultProducts();
	}

	function saveProducts() {
		try {
			localStorage.setItem(catalogKey, JSON.stringify(products));
		} catch (error) {
			showNotice('Tarayıcı depolamasına yazılamadı. Değişiklikler sayfayı yenileyince kaybolabilir.');
			return false;
		}
		window.dispatchEvent(new CustomEvent('moda-demo-catalog-updated', { detail: products }));
		return true;
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

	function showNotice(message) {
		document.querySelector('#admin-message-title').textContent = 'Bilgi';
		document.querySelector('#admin-message-copy').textContent = message;
		if (!messageDialog.open) messageDialog.showModal();
	}

	function render() {
		const term = document.querySelector('#admin-search').value.trim().toLocaleLowerCase('tr-TR');
		const visible = products.filter((product) => `${product.name} ${product.category}`.toLocaleLowerCase('tr-TR').includes(term));
		body.innerHTML = visible.map((product) => {
			const image = Array.isArray(product.images) ? product.images.find(isSafeImageData) : product.image;
			const photo = isSafeImageData(image)
				? `<img src="${image}" alt="" loading="lazy">`
				: '<span></span>';
			const productBg = safeColor(product.bg, '#ded6c9');
			const productColor = safeColor(product.colorHex, '#35332f');
			const siblingCount = products.filter((item) => (item.styleId || item.id) === (product.styleId || product.id)).length;
			return `
			<tr data-row="${escapeText(product.id)}">
				<td><div class="admin-product-name"><span class="admin-product-thumb" style="--product-bg:${productBg};--product-color:${productColor}" aria-hidden="true">${photo}</span><span><strong>${escapeText(product.name)}</strong><small>${escapeText(product.color)} · ${escapeText(product.sizes.join(', '))}</small></span></div></td>
				<td>${escapeText(product.category)}</td>
				<td>${currency.format(product.price)}${product.oldPrice ? `<br><small>Eski: ${currency.format(product.oldPrice)}</small>` : ''}</td>
				<td><label><span class="sr-only">${escapeText(product.name)} demo stok</span><input class="stock-input" type="number" min="0" step="1" value="${product.stock}" data-stock="${escapeText(product.id)}" aria-label="${escapeText(product.name)} demo stok adedi"></label></td>
				<td><span class="stock-status ${product.stock < 1 ? 'is-out' : ''}">${product.stock > 0 ? 'Demo stokta' : 'Tükendi'}</span></td>
				<td><div class="row-actions"><button class="row-action" type="button" data-edit="${escapeText(product.id)}">Düzenle</button><button class="row-action" type="button" data-add-color="${escapeText(product.id)}">+ Renk</button><button class="row-action is-delete" type="button" data-delete="${escapeText(product.id)}">Sil</button></div>${siblingCount > 1 ? `<small>${siblingCount} renk varyantı</small>` : ''}</td>
			</tr>`;
		}).join('');
		document.querySelector('#table-empty').hidden = visible.length > 0;
		document.querySelector('#stat-products').textContent = products.length;
		document.querySelector('#stat-stock').textContent = products.reduce((total, product) => total + product.stock, 0);
		document.querySelector('#stat-out-of-stock').textContent = products.filter((product) => product.stock === 0).length;
	}

	function openForm(product, addColorVariant = false) {
		form.reset();
		const isColorVariant = Boolean(product && addColorVariant);
		selectedImages = !isColorVariant && product
			? (Array.isArray(product.images) ? product.images : [product.image]).filter(isSafeImageData).slice(0, maxProductImages)
			: [];
		renderImagePreviews();
		document.querySelector('#form-error').hidden = true;
		document.querySelector('#form-title').textContent = isColorVariant ? 'Ürüne yeni renk ekle' : product ? 'Demo ürünü düzenle' : 'Demo ürün ekle';
		document.querySelector('#product-id').value = product && !isColorVariant ? product.id : '';
		document.querySelector('#product-style-id').value = isColorVariant ? (product.styleId || product.id) : '';
		document.querySelector('#product-name').value = product ? product.name : '';
		document.querySelector('#product-category').value = product ? product.category : 'Üst giyim';
		document.querySelector('#product-price').value = product ? product.price : '';
		document.querySelector('#product-old-price').value = product && product.oldPrice ? product.oldPrice : '';
		document.querySelector('#product-stock').value = product && !isColorVariant ? product.stock : 0;
		document.querySelector('#product-color').value = product && !isColorVariant ? product.color : '';
		document.querySelector('#product-color-hex').value = product ? product.colorHex : '#c7b9a5';
		document.querySelector('#product-bg').value = product ? product.bg : '#ded6c9';
		document.querySelector('#product-sizes').value = product ? product.sizes.join(', ') : 'XS, S, M, L';
		document.querySelector('#product-badge').value = product ? product.badge : '';
		formDialog.showModal();
		document.querySelector('#product-name').focus();
	}

	function closeDialog(dialog) {
		if (dialog.open) dialog.close();
	}

	function announce(message) {
		announcer.textContent = '';
		window.setTimeout(() => { announcer.textContent = message; }, 20);
	}

	document.querySelector('#add-product').addEventListener('click', () => openForm(null));
	document.querySelector('#admin-search').addEventListener('input', render);
	document.querySelector('#product-images').addEventListener('change', async (event) => {
		const files = Array.from(event.target.files || []);
		if (!files.length) return;
		const error = document.querySelector('#form-error');
		error.hidden = true;
		try {
			if (selectedImages.length + files.length > maxProductImages) {
				throw new Error(`Bir renk için en fazla ${maxProductImages} fotoğraf ekleyebilirsin.`);
			}
			const preparedImages = [];
			for (const file of files) preparedImages.push(await prepareImage(file));
			selectedImages = [...selectedImages, ...preparedImages];
			renderImagePreviews();
			announce('Ürün fotoğrafları önizlemeye eklendi. Kaydettiğinde mağazada galeride görünür.');
		} catch (imageError) {
			event.target.value = '';
			error.textContent = imageError.message;
			error.hidden = false;
		}
	});
	document.querySelector('#image-gallery-preview').addEventListener('click', (event) => {
		const removeButton = event.target.closest('[data-remove-image]');
		if (!removeButton) return;
		selectedImages.splice(Number(removeButton.dataset.removeImage), 1);
		renderImagePreviews();
	});
	document.querySelector('#reset-catalog').addEventListener('click', () => {
		if (!window.confirm('Demo kataloğundaki değişiklikler silinsin ve örnek ürünler geri yüklensin mi?')) return;
		const previousProducts = products;
		products = defaultProducts();
		if (!saveProducts()) {
			products = previousProducts;
			return;
		}
		render();
		announce('Örnek demo kataloğu geri yüklendi.');
	});

	body.addEventListener('input', (event) => {
		const input = event.target.closest('[data-stock]');
		if (!input) return;
		const quantity = Number(input.value);
		if (input.value === '' || !Number.isInteger(quantity) || quantity < 0) {
			input.setCustomValidity('Stok sıfır veya pozitif bir tam sayı olmalıdır.');
			return;
		}
		input.setCustomValidity('');
	});

	body.addEventListener('change', (event) => {
		const input = event.target.closest('[data-stock]');
		if (!input) return;
		const product = products.find((item) => item.id === input.dataset.stock);
		if (!product) return;
		const quantity = Number(input.value);
		if (input.value === '' || !Number.isInteger(quantity) || quantity < 0) {
			input.value = product.stock;
			input.setCustomValidity('');
			return;
		}
		const previousStock = product.stock;
		product.stock = quantity;
		if (!saveProducts()) {
			product.stock = previousStock;
			input.value = previousStock;
			return;
		}
		const row = input.closest('tr');
		const status = row.querySelector('.stock-status');
		status.textContent = quantity > 0 ? 'Demo stokta' : 'Tükendi';
		status.classList.toggle('is-out', quantity === 0);
		document.querySelector('#stat-stock').textContent = products.reduce((total, item) => total + item.stock, 0);
		document.querySelector('#stat-out-of-stock').textContent = products.filter((item) => item.stock === 0).length;
		announce(`${product.name} demo stoku ${product.stock} olarak kaydedildi.`);
	});

	body.addEventListener('click', (event) => {
		const editButton = event.target.closest('[data-edit]');
		const addColorButton = event.target.closest('[data-add-color]');
		const deleteButton = event.target.closest('[data-delete]');
		if (addColorButton) {
			const product = products.find((item) => item.id === addColorButton.dataset.addColor);
			if (product) openForm(product, true);
		}
		if (editButton) {
			const product = products.find((item) => item.id === editButton.dataset.edit);
			if (product) openForm(product);
		}
		if (deleteButton) {
			const product = products.find((item) => item.id === deleteButton.dataset.delete);
			if (!product || !window.confirm(`“${product.name}” demo kataloğundan silinsin mi?`)) return;
			const previousProducts = products;
			products = products.filter((item) => item.id !== product.id);
			if (!saveProducts()) {
				products = previousProducts;
				return;
			}
			render();
			announce(`${product.name} demo kataloğundan silindi.`);
		}
	});

	form.addEventListener('submit', (event) => {
		event.preventDefault();
		let id = document.querySelector('#product-id').value;
		if (!id) {
			id = `demo-${Date.now().toString(36)}`;
			while (products.some((product) => product.id === id)) id = `${id}-1`;
		}
		const sizes = document.querySelector('#product-sizes').value.split(',').map((size) => size.trim()).filter(Boolean);
		const price = Number(document.querySelector('#product-price').value);
		const oldPriceValue = document.querySelector('#product-old-price').value;
		const oldPrice = oldPriceValue ? Number(oldPriceValue) : null;
		const stock = Number(document.querySelector('#product-stock').value);
		const error = document.querySelector('#form-error');
		const invalid = sizes.length === 0
			|| !Number.isInteger(price) || price < 1
			|| (oldPrice !== null && (!Number.isInteger(oldPrice) || oldPrice <= price))
			|| !Number.isInteger(stock) || stock < 0;
		if (invalid) {
			error.textContent = 'Bedenleri ve fiyatları kontrol et. Stok sıfır veya pozitif tam sayı olmalı; indirim öncesi fiyat varsa güncel fiyattan yüksek olmalı.';
			error.hidden = false;
			return;
		}
		const color = document.querySelector('#product-color').value.trim();
		const styleId = document.querySelector('#product-style-id').value || (products.find((product) => product.id === id)?.styleId) || id;
		const duplicateColor = products.some((item) => item.id !== id
			&& (item.styleId || item.id) === styleId
			&& item.color.toLocaleLowerCase('tr-TR') === color.toLocaleLowerCase('tr-TR'));
		if (duplicateColor) {
			error.textContent = 'Bu ürün için aynı renk adı zaten kayıtlı. Farklı bir renk adı gir.';
			error.hidden = false;
			return;
		}
		const existing = products.find((product) => product.id === id);
		const styleSource = products.find((item) => (item.styleId || item.id) === styleId);
		const product = {
			id,
			name: document.querySelector('#product-name').value.trim(),
			category: document.querySelector('#product-category').value,
			price,
			oldPrice,
			color: document.querySelector('#product-color').value.trim(),
			colorHex: document.querySelector('#product-color-hex').value,
			bg: document.querySelector('#product-bg').value,
			sizes: [...new Set(sizes)],
			stock,
			sales: existing ? existing.sales : styleSource ? styleSource.sales : 0,
			newest: existing ? existing.newest : styleSource ? styleSource.newest : Math.max(0, ...products.map((item) => item.newest || 0)) + 1,
			badge: document.querySelector('#product-badge').value.trim(),
			art: existing ? existing.art : styleSource ? styleSource.art : 'art-shirt',
			styleId,
			images: selectedImages
		};
		if (!product.name || !product.color) {
			error.textContent = 'Ürün adı ve renk adı boş bırakılamaz.';
			error.hidden = false;
			return;
		}
		const updatedProducts = existing
			? products.map((item) => {
				if (item.id === id) return product;
				if ((item.styleId || item.id) !== styleId) return item;
				return {
					...item,
					name: product.name,
					category: product.category,
					price: product.price,
					oldPrice: product.oldPrice,
					sizes: [...product.sizes],
					sales: product.sales,
					newest: product.newest,
					badge: product.badge,
					art: product.art
				};
			})
			: [product, ...products];
		const previousProducts = products;
		products = updatedProducts;
		if (!saveProducts()) {
			products = previousProducts;
			return;
		}
		render();
		closeDialog(formDialog);
		announce(`${product.name} demo kataloğuna kaydedildi.`);
	});

	document.querySelectorAll('[data-close-form]').forEach((button) => button.addEventListener('click', () => closeDialog(formDialog)));
	document.querySelectorAll('[data-close-message]').forEach((button) => button.addEventListener('click', () => closeDialog(messageDialog)));
	document.querySelectorAll('[data-unavailable]').forEach((link) => link.addEventListener('click', (event) => {
		event.preventDefault();
		showNotice(link.dataset.unavailable);
	}));
	[formDialog, messageDialog].forEach((dialog) => dialog.addEventListener('click', (event) => {
		if (event.target === dialog) closeDialog(dialog);
	}));

	window.addEventListener('storage', (event) => {
		if (event.key === siteKey) {
			try {
				const updated = normalizeSiteConfig(event.newValue ? JSON.parse(event.newValue) : {});
				siteConfig = updated;
				fillSiteForm(siteConfig);
				document.querySelector('#site-content-status').textContent = 'Site içeriği başka bir sekmeden güncellendi.';
			} catch (error) {
				showNotice('Başka bir sekmeden gelen site ayarı okunamadı.');
			}
			return;
		}
		if (event.key !== catalogKey) return;
		try {
			const updated = event.newValue ? JSON.parse(event.newValue) : defaultProducts();
			if (!Array.isArray(updated)) throw new Error('Katalog biçimi geçersiz.');
			products = normalizeProducts(updated);
			render();
			announce('Ürün kataloğu başka bir sekmeden güncellendi.');
		} catch (error) {
			showNotice('Başka bir sekmeden gelen demo katalog değişikliği okunamadı.');
		}
	});

	render();
})();
