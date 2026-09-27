<?php
/**
 * The footer for OBSIDIAN theme.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	</div><!-- #root -->

	<!-- ── Scripts Engine: Theme Switcher & Fullscreen Menu ── -->
	<script id="obsidian-theme-menu-scripts">
		// ─── 1. Global Theme Switcher ──────────────────────────────────────────────
		window.toggleObsidianTheme = function(event) {
			const html = document.documentElement;
			const isDark = html.classList.contains('dark') || !html.classList.contains('light');
			const targetTheme = isDark ? 'light' : 'dark';

			const themeBtn = document.getElementById('obsidian-theme-btn');
			const sunIcon = document.getElementById('theme-icon-sun');
			const moonIcon = document.getElementById('theme-icon-moon');
			const rippleCircle = document.getElementById('theme-ripple-circle');

			// 1. Button spin animation
			if (themeBtn) {
				themeBtn.classList.remove('theme-spin-active');
				void themeBtn.offsetWidth;
				themeBtn.classList.add('theme-spin-active');
			}

			// 2. Core Apply Function
			function apply() {
				if (targetTheme === 'light') {
					html.classList.remove('dark');
					html.classList.add('light');
					if (sunIcon) { sunIcon.classList.remove('hidden'); sunIcon.classList.add('block'); }
					if (moonIcon) { moonIcon.classList.remove('block'); moonIcon.classList.add('hidden'); }
				} else {
					html.classList.remove('light');
					html.classList.add('dark');
					if (sunIcon) { sunIcon.classList.remove('block'); sunIcon.classList.add('hidden'); }
					if (moonIcon) { moonIcon.classList.remove('hidden'); moonIcon.classList.add('block'); }
				}
				localStorage.setItem('obsidian_theme', targetTheme);
				window.dispatchEvent(new CustomEvent('obsidian-theme-changed', { detail: { theme: targetTheme } }));
			}

			// 3. Animation Coordinates
			let x = window.innerWidth - 60;
			let y = 30;
			if (event && typeof event.clientX === 'number') {
				x = event.clientX;
				y = event.clientY;
			} else if (themeBtn) {
				const r = themeBtn.getBoundingClientRect();
				x = r.left + r.width / 2;
				y = r.top + r.height / 2;
			}

			const endRadius = Math.hypot(
				Math.max(x, window.innerWidth - x),
				Math.max(y, window.innerHeight - y)
			);

			// 4. View Transitions API execution
			if (typeof document.startViewTransition === 'function') {
				try {
					const transition = document.startViewTransition(() => {
						apply();
					});
					transition.ready.then(() => {
						document.documentElement.animate(
							{
								clipPath: [
									`circle(0px at ${x}px ${y}px)`,
									`circle(${endRadius}px at ${x}px ${y}px)`
								]
							},
							{
								duration: 700,
								easing: 'cubic-bezier(0.76, 0, 0.24, 1)',
								pseudoElement: '::view-transition-new(root)'
							}
						);
					}).catch(() => {
						apply();
					});
				} catch (err) {
					apply();
				}
			} else if (rippleCircle) {
				const size = endRadius * 2.2;
				rippleCircle.style.width = size + 'px';
				rippleCircle.style.height = size + 'px';
				rippleCircle.style.left = x + 'px';
				rippleCircle.style.top = y + 'px';
				rippleCircle.style.backgroundColor = targetTheme === 'light' ? '#DADADA' : '#080808';
				rippleCircle.classList.add('active');
				setTimeout(() => {
					apply();
					setTimeout(() => rippleCircle.classList.remove('active'), 350);
				}, 300);
			} else {
				apply();
			}
		};

		// Sync button icon on load
		(function syncIconsOnLoad() {
			const isLight = document.documentElement.classList.contains('light');
			const sunIcon = document.getElementById('theme-icon-sun');
			const moonIcon = document.getElementById('theme-icon-moon');
			if (isLight) {
				if (sunIcon) { sunIcon.classList.remove('hidden'); sunIcon.classList.add('block'); }
				if (moonIcon) { moonIcon.classList.remove('block'); moonIcon.classList.add('hidden'); }
			} else {
				if (sunIcon) { sunIcon.classList.remove('block'); sunIcon.classList.add('hidden'); }
				if (moonIcon) { moonIcon.classList.remove('hidden'); moonIcon.classList.add('block'); }
			}
		})();

		// ─── 2. Fullscreen Menu & Mega Menu Logic ─────────────────────────────────
		(function initMenu() {
			const menuBtn = document.getElementById('obsidian-menu-btn');
			const overlay = document.getElementById('obsidian-menu-overlay');
			const burgerTop = document.getElementById('burger-top');
			const burgerMid = document.getElementById('burger-mid');
			const burgerBot = document.getElementById('burger-bot');
			const navBottom = document.getElementById('nav-bottom-info');
			const rootPanel = document.getElementById('menu-panel-root');
			const megaTriggers = document.querySelectorAll('.mega-menu-trigger');
			const megaPanels = document.querySelectorAll('.mega-panel');
			const backButtons = document.querySelectorAll('.btn-mega-back');

			if (!menuBtn || !overlay) return;

			let isOpen = false;
			let currentActiveMegaPanel = null;

			function animateRootLinksIn() {
				const linkTexts = rootPanel.querySelectorAll('.nav-link-text');
				linkTexts.forEach((span, idx) => {
					const trigger = span.closest('.mega-menu-trigger');
					const delay = trigger ? trigger.getAttribute('data-delay') : (idx * 60 + 'ms');
					span.style.transitionDelay = delay;
					span.style.transform = 'translateY(0)';
				});
			}

			function animateRootLinksOut() {
				const linkTexts = rootPanel.querySelectorAll('.nav-link-text');
				linkTexts.forEach((span) => {
					span.style.transitionDelay = '0ms';
					span.style.transform = 'translateY(110%)';
				});
			}

			function openMegaPanel(panelId) {
				const targetPanel = document.getElementById(panelId);
				if (!targetPanel) return;

				currentActiveMegaPanel = targetPanel;

				// 1. Animate root menu out smoothly
				if (rootPanel) {
					rootPanel.style.opacity = '0';
					rootPanel.style.transform = 'translate3d(0, -24px, 0)';
					rootPanel.style.pointerEvents = 'none';
					rootPanel.style.visibility = 'hidden';
				}

				// 2. Hide all other mega panels
				megaPanels.forEach(panel => {
					if (panel !== targetPanel) {
						panel.style.opacity = '0';
						panel.style.transform = 'translate3d(0, 28px, 0)';
						panel.style.pointerEvents = 'none';
						panel.style.visibility = 'hidden';
					}
				});

				// 3. Slide target mega panel in seamlessly
				targetPanel.style.visibility = 'visible';
				targetPanel.style.opacity = '1';
				targetPanel.style.transform = 'translate3d(0, 0, 0)';
				targetPanel.style.pointerEvents = 'auto';
			}

			function backToRootMenu() {
				if (currentActiveMegaPanel) {
					// Slide current mega panel down and out
					currentActiveMegaPanel.style.opacity = '0';
					currentActiveMegaPanel.style.transform = 'translate3d(0, 28px, 0)';
					currentActiveMegaPanel.style.pointerEvents = 'none';
					currentActiveMegaPanel.style.visibility = 'hidden';
					currentActiveMegaPanel = null;
				}

				// Slide root menu back into place
				if (rootPanel) {
					rootPanel.style.visibility = 'visible';
					rootPanel.style.opacity = '1';
					rootPanel.style.transform = 'translate3d(0, 0, 0)';
					rootPanel.style.pointerEvents = 'auto';
					animateRootLinksIn();
				}
			}

			function resetMenuToRootInstant() {
				if (currentActiveMegaPanel) {
					currentActiveMegaPanel.style.opacity = '0';
					currentActiveMegaPanel.style.transform = 'translate3d(0, 28px, 0)';
					currentActiveMegaPanel.style.pointerEvents = 'none';
					currentActiveMegaPanel.style.visibility = 'hidden';
					currentActiveMegaPanel = null;
				}
				if (rootPanel) {
					rootPanel.style.visibility = 'visible';
					rootPanel.style.opacity = '1';
					rootPanel.style.transform = 'translate3d(0, 0, 0)';
					rootPanel.style.pointerEvents = 'auto';
				}
				animateRootLinksOut();
			}

			function toggleMenu(open) {
				isOpen = typeof open === 'boolean' ? open : !isOpen;
				menuBtn.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');

				if (isOpen) {
					document.body.style.overflow = 'hidden';
					overlay.style.pointerEvents = 'auto';
					overlay.style.clipPath = 'inset(0% 0% 0% 0%)';

					// Burger icon -> X
					burgerTop.style.transform = 'translateY(7.5px) rotate(45deg)';
					burgerTop.style.width = '100%';
					burgerMid.style.opacity = '0';
					burgerMid.style.transform = 'scaleX(0)';
					burgerBot.style.transform = 'translateY(-7.5px) rotate(-45deg)';
					burgerBot.style.width = '100%';

					// Ensure starting at root
					if (!currentActiveMegaPanel) {
						if (rootPanel) {
							rootPanel.style.visibility = 'visible';
							rootPanel.style.opacity = '1';
							rootPanel.style.transform = 'translate3d(0, 0, 0)';
							rootPanel.style.pointerEvents = 'auto';
						}
						animateRootLinksIn();
					}

					// Bottom info
					if (navBottom) {
						navBottom.style.transitionDelay = '450ms';
						navBottom.style.opacity = '1';
						navBottom.style.transform = 'translateY(0)';
					}
				} else {
					document.body.style.overflow = '';
					overlay.style.pointerEvents = 'none';
					overlay.style.clipPath = 'inset(0% 0% 100% 0%)';

					// Burger icon reset
					burgerTop.style.transform = '';
					burgerTop.style.width = '100%';
					burgerMid.style.opacity = '1';
					burgerMid.style.transform = '';
					burgerBot.style.transform = '';
					burgerBot.style.width = '80%';

					// Reset links
					animateRootLinksOut();

					// Bottom info reset
					if (navBottom) {
						navBottom.style.transitionDelay = '0ms';
						navBottom.style.opacity = '0';
						navBottom.style.transform = 'translateY(20px)';
					}

					// Reset state after animation completes
					setTimeout(() => {
						if (!isOpen) {
							resetMenuToRootInstant();
						}
					}, 700);
				}
			}

			// Hamburger button click
			menuBtn.addEventListener('click', () => toggleMenu());

			// Trigger click: Slide to Mega Menu Panel
			megaTriggers.forEach(trigger => {
				trigger.addEventListener('click', (e) => {
					e.preventDefault();
					const target = trigger.getAttribute('data-mega-target');
					if (target) {
						openMegaPanel(target);
					}
				});
			});

			// Back buttons click: Slide back to Main Menu
			backButtons.forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					backToRootMenu();
				});
			});

			// Sublinks / Leaf anchors click: close overlay
			const leafLinks = overlay.querySelectorAll('a[href^="#"], a:not([data-mega-target])');
			leafLinks.forEach(link => {
				link.addEventListener('click', () => {
					toggleMenu(false);
				});
			});

			// Keyboard ESC to close or go back
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' && isOpen) {
					if (currentActiveMegaPanel) {
						backToRootMenu();
					} else {
						toggleMenu(false);
					}
				}
			});
		})();

		// ─── 3. Global Product Card AJAX Add to Cart Handler ───────────────────────
		window.rakaCardAddToCart = function(productId, btn) {
			if (!btn || btn.disabled) return;
			btn.disabled = true;

			const txtSpan = btn.querySelector('span');
			const icoEl   = btn.querySelector('i');
			const origTxt = txtSpan ? txtSpan.textContent : 'ADD TO CART';
			const origIco = icoEl ? icoEl.className : 'ri-shopping-bag-3-line text-xs flex-shrink-0';

			if (txtSpan) txtSpan.textContent = 'ADDING...';
			if (icoEl) icoEl.className = 'ri-loader-4-line animate-spin text-xs flex-shrink-0';

			const formData = new FormData();
			formData.append('product_id', productId);
			formData.append('quantity', 1);

			fetch('<?php echo esc_url( home_url( '/?wc-ajax=add_to_cart' ) ); ?>', {
				method: 'POST',
				body: formData
			})
			.then(res => res.json())
			.then(data => {
				if (txtSpan) txtSpan.textContent = 'ADDED!';
				if (icoEl) icoEl.className = 'ri-check-line text-xs text-green-400 flex-shrink-0';

				// Remove any WooCommerce default injected .added_to_cart links
				setTimeout(() => {
					const parent = btn.parentElement;
					if (parent) {
						parent.querySelectorAll('.added_to_cart').forEach(el => el.remove());
					}
				}, 10);

				if (window.jQuery) {
					window.jQuery(document.body).trigger('added_to_cart', [data.fragments, data.cart_hash]);
				}

				setTimeout(() => {
					if (txtSpan) txtSpan.textContent = origTxt;
					if (icoEl) icoEl.className = origIco;
					btn.disabled = false;
				}, 1800);
			})
			.catch(() => {
				window.location.href = '<?php echo esc_url( wc_get_cart_url() ); ?>?add-to-cart=' + productId;
			});
		};

		// ─── 4. Dynamic Sticky/Fixed Header on Scroll Controller ──────────────────
		(function() {
			const header = document.getElementById('obsidian-site-header');
			if (!header) return;

			function handleScroll() {
				const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
				if (scrollY > 20) {
					header.classList.add('header-scrolled');
				} else {
					header.classList.remove('header-scrolled');
				}
			}

			window.addEventListener('scroll', handleScroll, { passive: true });
			handleScroll();
		})();
	</script>

	<?php wp_footer(); ?>
</body>
</html>

