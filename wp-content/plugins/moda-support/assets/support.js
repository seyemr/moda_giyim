(function () {
	'use strict';

	var search = document.querySelector('[data-moda-faq-search]');
	if (search) {
		var items = Array.prototype.slice.call(document.querySelectorAll('.moda-faq-item'));
		var groups = Array.prototype.slice.call(document.querySelectorAll('.moda-faq-group'));
		var empty = document.querySelector('[data-moda-faq-empty]');

		search.addEventListener('input', function () {
			var term = search.value.trim().toLocaleLowerCase('tr');
			items.forEach(function (item) {
				var match = !term || item.textContent.toLocaleLowerCase('tr').indexOf(term) !== -1;
				item.hidden = !match;
				if (term && match) {
					item.open = true;
				}
			});
			groups.forEach(function (group) {
				group.hidden = !group.querySelector('.moda-faq-item:not([hidden])');
			});
			if (empty) {
				empty.hidden = groups.some(function (group) {
					return !group.hidden;
				});
			}
		});
	}

	var config = window.modaSupport;
	var form = document.querySelector('.moda-support-form');
	if (!config || !form) {
		return;
	}

	var input = form.querySelector('[data-moda-files]');
	var list = form.querySelector('[data-moda-previews]');
	var error = form.querySelector('[data-moda-file-error]');
	var types = ['image/jpeg', 'image/png', 'image/webp'];
	var selected = [];

	function showError(message) {
		error.textContent = message;
		error.hidden = !message;
	}

	function sync() {
		var transfer = new DataTransfer();
		selected.forEach(function (file) {
			transfer.items.add(file);
		});
		input.files = transfer.files;
	}

	function render() {
		list.textContent = '';
		selected.forEach(function (file, index) {
			var item = document.createElement('li');
			var image = document.createElement('img');
			var url = URL.createObjectURL(file);
			image.src = url;
			image.alt = file.name;
			image.addEventListener('load', function () {
				URL.revokeObjectURL(url);
			});
			var remove = document.createElement('button');
			remove.type = 'button';
			remove.textContent = '×';
			remove.setAttribute('aria-label', config.i18n.remove + ': ' + file.name);
			remove.addEventListener('click', function () {
				selected.splice(index, 1);
				sync();
				render();
			});
			item.appendChild(image);
			item.appendChild(remove);
			list.appendChild(item);
		});
	}

	if (input && list && error && window.DataTransfer) {
		input.addEventListener('change', function () {
			var message = '';
			Array.prototype.slice.call(input.files).forEach(function (file) {
				if (types.indexOf(file.type) === -1) {
					message = config.i18n.badType;
				} else if (file.size > config.maxBytes) {
					message = config.i18n.tooLarge;
				} else if (selected.length >= config.maxFiles) {
					message = config.i18n.tooMany;
				} else {
					selected.push(file);
				}
			});
			showError(message);
			sync();
			render();
		});
	}

	form.addEventListener('submit', function () {
		var button = form.querySelector('.moda-support-submit');
		if (button && form.checkValidity()) {
			button.disabled = true;
			button.textContent = config.i18n.sending;
		}
	});
})();
