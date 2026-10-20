(function () {
	'use strict';

	var carousels = document.querySelectorAll('[data-hero-carousel]');

	carousels.forEach(function (carousel) {
		var hero = carousel.closest('.ma-hero');
		var slides = Array.prototype.slice.call(carousel.querySelectorAll('[data-hero-slide]'));
		var copySlides = hero ? Array.prototype.slice.call(hero.querySelectorAll('[data-hero-copy-slide]')) : [];
		var dots = Array.prototype.slice.call(carousel.querySelectorAll('[data-hero-go]'));
		var previous = carousel.querySelector('[data-hero-previous]');
		var next = carousel.querySelector('[data-hero-next]');
		var pauseButton = carousel.querySelector('[data-hero-pause]');
		var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
		var activeIndex = 0;
		var timer = null;
		var userPaused = false;
		var pointerPaused = false;
		var focusPaused = false;

		if (slides.length < 2 || !hero) {
			if (carousel.querySelector('.ma-hero-controls')) {
				carousel.querySelector('.ma-hero-controls').hidden = true;
			}
			return;
		}

		function clearTimer() {
			if (timer) {
				window.clearTimeout(timer);
				timer = null;
			}
		}

		function canAutoAdvance() {
			return carousel.dataset.autoplay === 'true' && !userPaused && !pointerPaused && !focusPaused && !document.hidden && !reducedMotion.matches;
		}

		function scheduleAdvance() {
			clearTimer();
			if (canAutoAdvance()) {
				timer = window.setTimeout(function () {
					showSlide(activeIndex + 1);
				}, 5000);
			}
		}

		function showSlide(index) {
			activeIndex = (index + slides.length) % slides.length;
			slides.forEach(function (slide, slideIndex) {
				slide.hidden = slideIndex !== activeIndex;
			});
			copySlides.forEach(function (copySlide, slideIndex) {
				copySlide.hidden = slideIndex !== activeIndex;
			});
			dots.forEach(function (dot, dotIndex) {
				dot.setAttribute('aria-pressed', dotIndex === activeIndex ? 'true' : 'false');
			});
			scheduleAdvance();
		}

		function setInteractionPaused(type, paused) {
			if (type === 'pointer') {
				pointerPaused = paused;
			} else {
				focusPaused = paused;
			}
			scheduleAdvance();
		}

		function updateMotionPreference() {
			if (pauseButton) {
				pauseButton.hidden = reducedMotion.matches;
			}
			scheduleAdvance();
		}

		if (previous) {
			previous.addEventListener('click', function () {
				showSlide(activeIndex - 1);
			});
		}

		if (next) {
			next.addEventListener('click', function () {
				showSlide(activeIndex + 1);
			});
		}

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				showSlide(Number(dot.getAttribute('data-hero-go')) || 0);
			});
		});

		if (pauseButton) {
			pauseButton.addEventListener('click', function () {
				userPaused = !userPaused;
				pauseButton.setAttribute('aria-pressed', userPaused ? 'true' : 'false');
				pauseButton.setAttribute(
					'aria-label',
					userPaused ? pauseButton.dataset.labelPlay : pauseButton.dataset.labelPause
				);
				pauseButton.textContent = userPaused ? '▶' : 'Ⅱ';
				scheduleAdvance();
			});
		}

		hero.addEventListener('pointerenter', function () {
			setInteractionPaused('pointer', true);
		});
		hero.addEventListener('pointerleave', function () {
			setInteractionPaused('pointer', false);
		});
		hero.addEventListener('focusin', function () {
			setInteractionPaused('focus', true);
		});
		hero.addEventListener('focusout', function (event) {
			if (!hero.contains(event.relatedTarget)) {
				setInteractionPaused('focus', false);
			}
		});
		document.addEventListener('visibilitychange', scheduleAdvance);

		if (typeof reducedMotion.addEventListener === 'function') {
			reducedMotion.addEventListener('change', updateMotionPreference);
		} else if (typeof reducedMotion.addListener === 'function') {
			reducedMotion.addListener(updateMotionPreference);
		}
		updateMotionPreference();
		scheduleAdvance();
	});
})();
