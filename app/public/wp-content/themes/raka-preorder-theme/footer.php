<?php
/**
 * The footer for OBSIDIAN theme.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Render Global Luxury Footer on Inner Pages ──
if ( ! is_front_page() && function_exists( 'raka_render_site_footer' ) ) {
	raka_render_site_footer();
}
?>
	</div><!-- #root -->

	<?php
	// ── Render Global Slide-Over Cart Drawer ──
	if ( function_exists( 'raka_render_cart_drawer' ) ) {
		raka_render_cart_drawer();
	}
	?>

	<!-- ── Scripts Engine: Theme Switcher, Fullscreen Menu & Cart Drawer ── -->
	<script id="raka-theme-menu-scripts">
		// ─── 1. Global Theme Switcher ──────────────────────────────────────────────
		window.toggleRakaTheme = function(event) {
			const html = document.documentElement;
			const isDark = html.classList.contains('dark') || !html.classList.contains('light');
			const targetTheme = isDark ? 'light' : 'dark';

			const themeBtn = document.getElementById('raka-theme-btn') || document.getElementById('obsidian-theme-btn');
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
				localStorage.setItem('raka_theme', targetTheme);
				localStorage.setItem('obsidian_theme', targetTheme);
				window.dispatchEvent(new CustomEvent('raka-theme-changed', { detail: { theme: targetTheme } }));
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
		// Backward compatibility alias
		window.toggleObsidianTheme = window.toggleRakaTheme;

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

		// ─── 1.1 Global User Account Dropdown ────────────────────────────────────
		window.toggleRakaAccountMenu = function(event) {
			if (event) {
				event.stopPropagation();
				event.preventDefault();
			}
			const dropdown = document.getElementById('raka-account-dropdown');
			const btn = document.getElementById('raka-account-btn');
			if (!dropdown) return;

			const isClosed = dropdown.classList.contains('opacity-0') || dropdown.classList.contains('invisible');
			if (isClosed) {
				dropdown.classList.remove('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
				dropdown.classList.add('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
				if (btn) btn.setAttribute('aria-expanded', 'true');
			} else {
				dropdown.classList.add('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
				dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
				if (btn) btn.setAttribute('aria-expanded', 'false');
			}
		};

		document.addEventListener('click', function(e) {
			const dropdown = document.getElementById('raka-account-dropdown');
			const btn = document.getElementById('raka-account-btn');
			if (dropdown && !dropdown.contains(e.target) && (!btn || !btn.contains(e.target))) {
				dropdown.classList.add('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
				dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
				if (btn) btn.setAttribute('aria-expanded', 'false');
			}
		});

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				const dropdown = document.getElementById('raka-account-dropdown');
				const btn = document.getElementById('raka-account-btn');
				if (dropdown) {
					dropdown.classList.add('opacity-0', 'invisible', 'translate-y-2', 'pointer-events-none');
					dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0', 'pointer-events-auto');
					if (btn) btn.setAttribute('aria-expanded', 'false');
				}
			}
		});

		// ─── 2. Fullscreen Menu & Mega Menu Logic ─────────────────────────────────
		(function initMenu() {
			const menuBtn = document.getElementById('raka-menu-btn') || document.getElementById('obsidian-menu-btn');
			const overlay = document.getElementById('raka-menu-overlay') || document.getElementById('obsidian-menu-overlay');
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

			// Dedicated Cancel / Close buttons inside overlay
			const closeButtons = overlay.querySelectorAll('.btn-menu-close, #raka-menu-close-btn, #obsidian-menu-close-btn');
			closeButtons.forEach(btn => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					toggleMenu(false);
				});
			});

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

			fetch('<?php echo esc_url( home_url( '/?wc-ajax=raka_add_to_cart' ) ); ?>', {
				method: 'POST',
				body: formData
			})
			.then(res => res.json())
			.then(data => {
				if (txtSpan) txtSpan.textContent = 'ADDED!';
				if (icoEl) icoEl.className = 'ri-check-line text-xs text-green-400 flex-shrink-0';

				if (data && data.fragments && typeof window.updateCartDrawerFragments === 'function') {
					window.updateCartDrawerFragments(data.fragments);
				}

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

				// Automatically open Slide-Over Cart Drawer upon adding
				if (typeof window.openRakaCart === 'function') {
					window.openRakaCart();
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
			const header = document.getElementById('raka-site-header') || document.getElementById('obsidian-site-header');
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

		// ─── 5. Luxury Slide-Over Cart Drawer Controller ──────────────────────────
		(function() {
			const drawer   = document.getElementById('raka-cart-drawer');
			const backdrop = document.getElementById('raka-cart-backdrop');
			const panel    = document.getElementById('raka-cart-panel');
			let isCartOpen = false;

			window.openRakaCart = function(e) {
				if (e && typeof e.preventDefault === 'function') e.preventDefault();
				if (!drawer || !backdrop || !panel) return false;
				isCartOpen = true;
				drawer.classList.remove('pointer-events-none');
				drawer.classList.add('pointer-events-auto');
				drawer.setAttribute('aria-hidden', 'false');

				// Backdrop fade in
				backdrop.classList.remove('opacity-0');
				backdrop.classList.add('opacity-100');

				// Panel slide in
				panel.classList.remove('translate-x-full');
				panel.classList.add('translate-x-0');

				document.body.style.overflow = 'hidden';
			};

			window.closeRakaCart = function() {
				if (!drawer || !backdrop || !panel) return;
				isCartOpen = false;

				// Panel slide out
				panel.classList.remove('translate-x-0');
				panel.classList.add('translate-x-full');

				// Backdrop fade out
				backdrop.classList.remove('opacity-100');
				backdrop.classList.add('opacity-0');

				drawer.classList.remove('pointer-events-auto');
				drawer.classList.add('pointer-events-none');
				drawer.setAttribute('aria-hidden', 'true');

				// If fullscreen menu overlay is not open, unlock body scroll
				const menuOverlay = document.getElementById('raka-menu-overlay') || document.getElementById('fullscreen-menu-overlay');
				const isMenuOpen = menuOverlay && menuOverlay.style.pointerEvents === 'auto';
				if (!isMenuOpen) {
					document.body.style.overflow = '';
				}
			};

			// Update Cart Item Quantity with Live AJAX (+ / -)
			window.rakaUpdateCartQty = function(cartItemKey, delta, btn) {
				if (!cartItemKey) return;
				const qtyEl = document.getElementById('cart-qty-val-' + cartItemKey);
				const itemRow = document.getElementById('cart-item-' + cartItemKey);
				let currentQty = qtyEl ? parseInt(qtyEl.textContent.trim(), 10) : 1;
				if (isNaN(currentQty)) currentQty = 1;

				const newQty = currentQty + delta;
				if (newQty < 1) {
					// If reduced below 1, remove item
					window.rakaRemoveCartItem(cartItemKey, btn);
					return;
				}

				if (qtyEl) qtyEl.textContent = newQty;
				if (itemRow) itemRow.style.opacity = '0.5';

				const formData = new FormData();
				formData.append('cart_item_key', cartItemKey);
				formData.append('quantity', newQty);

				fetch('<?php echo esc_url( home_url( '/?wc-ajax=raka_update_cart_item_qty' ) ); ?>', {
					method: 'POST',
					body: formData
				})
				.then(res => res.json())
				.then(data => {
					if (data && data.fragments) {
						updateCartDrawerFragments(data.fragments);
					}
					if (data && typeof data.cart_count === 'number') {
						updateBadges(data.cart_count);
					}
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
					// If on cart page, trigger on-page cart refresh if present
					if (window.location.pathname.includes('/cart')) {
						const updateCartBtn = document.querySelector('button[name="update_cart"]');
						if (updateCartBtn) {
							updateCartBtn.disabled = false;
							updateCartBtn.click();
						}
					}
				})
				.catch(() => {
					if (itemRow) itemRow.style.opacity = '1';
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
				});
			};

			// Remove Cart Item with Live AJAX
			window.rakaRemoveCartItem = function(cartItemKey, btn) {
				if (!cartItemKey) return;
				const itemRow = document.getElementById('cart-item-' + cartItemKey);
				if (itemRow) {
					itemRow.style.opacity = '0.4';
					itemRow.style.pointerEvents = 'none';
				}
				if (btn) {
					btn.innerHTML = '<i class="ri-loader-4-line animate-spin text-sm"></i>';
				}

				const formData = new FormData();
				formData.append('cart_item_key', cartItemKey);

				fetch('<?php echo esc_url( home_url( '/?wc-ajax=raka_remove_cart_item' ) ); ?>', {
					method: 'POST',
					body: formData
				})
				.then(res => res.json())
				.then(data => {
					if (data && data.fragments) {
						updateCartDrawerFragments(data.fragments);
					}
					if (data && typeof data.cart_count === 'number') {
						updateBadges(data.cart_count);
					}
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
					// If on cart page, trigger on-page cart refresh if present
					if (window.location.pathname.includes('/cart')) {
						const updateCartBtn = document.querySelector('button[name="update_cart"]');
						if (updateCartBtn) {
							updateCartBtn.disabled = false;
							updateCartBtn.click();
						}
					}
				})
				.catch(() => {
					if (window.jQuery) {
						window.jQuery(document.body).trigger('wc_fragment_refresh');
					}
				});
			};

			window.updateBadges = function(count) {
				const badges = document.querySelectorAll('.raka-cart-count-badge');
				badges.forEach(b => {
					if (typeof count === 'number') {
						b.textContent = count;
						if (count > 0) {
							b.classList.remove('scale-0');
							b.classList.add('scale-100');
						} else {
							b.classList.remove('scale-100');
							b.classList.add('scale-0');
						}
					}
					// Pulse animation
					b.classList.remove('scale-125');
					void b.offsetWidth;
					b.classList.add('scale-125');
					setTimeout(() => b.classList.remove('scale-125'), 300);
				});
			};

			window.updateCartDrawerFragments = function(fragments) {
				if (!fragments) return;

				// Update Cart Drawer Content
				if (fragments['#raka-cart-drawer-content']) {
					const drawerBody = document.getElementById('raka-cart-drawer-body');
					if (drawerBody) {
						drawerBody.innerHTML = fragments['#raka-cart-drawer-content'];
					}
				}

				// Update Drawer Count
				if (fragments['#raka-cart-drawer-count']) {
					const temp = document.createElement('div');
					temp.innerHTML = fragments['#raka-cart-drawer-count'];
					const countEl = document.getElementById('raka-cart-drawer-count');
					if (countEl && temp.firstChild) {
						countEl.textContent = temp.firstChild.textContent.trim();
					}
				}

				// Update Badges
				if (fragments['#raka-cart-count']) {
					const temp = document.createElement('div');
					temp.innerHTML = fragments['#raka-cart-count'];
					const newSpan = temp.querySelector('span') || temp.firstChild;
					if (newSpan) {
						const cnt = parseInt(newSpan.textContent.trim(), 10);
						if (!isNaN(cnt)) window.updateBadges(cnt);
					}
				}
			};

			const updateCartDrawerFragments = window.updateCartDrawerFragments;
			const updateBadges = window.updateBadges;

			// Intercept Header & Overlay Cart buttons
			const cartBtns = document.querySelectorAll('#raka-cart-btn, #overlay-cart-btn, a[href*="cart"]');
			cartBtns.forEach(btn => {
				// Only intercept if not checkout link
				if (btn.id === 'raka-cart-btn' || btn.id === 'overlay-cart-btn') {
					btn.addEventListener('click', function(e) {
						e.preventDefault();
						window.openRakaCart();
					});
				}
			});

			// Escape key to close cart
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' && isCartOpen) {
					window.closeRakaCart();
				}
			});

			// Auto-sync Cart page quantity inputs on change
			let qtyDebounceTimer = null;
			document.addEventListener('change', function(e) {
				if (e.target && e.target.matches('.woocommerce-cart-form input.qty, input[name*="cart["]')) {
					clearTimeout(qtyDebounceTimer);
					qtyDebounceTimer = setTimeout(() => {
						const updateBtn = document.querySelector('button[name="update_cart"]');
						if (updateBtn) {
							updateBtn.disabled = false;
							updateBtn.click();
						}
					}, 400);
				}
			});

			// WooCommerce jQuery events
			if (window.jQuery) {
				window.jQuery(document.body).on('added_to_cart', function(event, fragments) {
					if (fragments) updateCartDrawerFragments(fragments);
				});

				window.jQuery(document.body).on('removed_from_cart', function(event, fragments) {
					if (fragments) updateCartDrawerFragments(fragments);
				});

				window.jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
					// Auto sync if needed
				});

				window.jQuery(document.body).on('updated_cart_totals', function() {
					// When cart totals are updated on /cart/ page, sync drawer fragments
					fetch('<?php echo esc_url( home_url( '/?wc-ajax=get_refreshed_fragments' ) ); ?>')
						.then(r => r.json())
						.then(d => {
							if (d && d.fragments) updateCartDrawerFragments(d.fragments);
						})
						.catch(() => {});
				});
			}

			// WooCommerce Block Cart store subscription (if React Gutenberg Cart is active)
			if (window.wp && wp.data && wp.data.subscribe) {
				let prevCartCount = null;
				wp.data.subscribe(function() {
					try {
						const store = wp.data.select('wc/store/cart');
						if (store && typeof store.getCartData === 'function') {
							const cartData = store.getCartData();
							if (cartData && typeof cartData.itemsCount === 'number') {
								if (prevCartCount !== null && prevCartCount !== cartData.itemsCount) {
									updateBadges(cartData.itemsCount);
									fetch('<?php echo esc_url( home_url( '/?wc-ajax=get_refreshed_fragments' ) ); ?>')
										.then(r => r.json())
										.then(d => {
											if (d && d.fragments) updateCartDrawerFragments(d.fragments);
										})
										.catch(() => {});
								}
								prevCartCount = cartData.itemsCount;
							}
						}
					} catch(e) {}
				});
			}
		})();
	</script>

	<?php wp_footer(); ?>
</body>
</html>

