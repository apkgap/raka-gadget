<?php
/**
 * Standalone Fullscreen Menu Overlay Component.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart' );
?>
<!-- ══════════ FULL THEME NAVIGATION OVERLAY ══════════ -->
<div id="raka-menu-overlay" class="fixed inset-0 z-[80] transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] pointer-events-none overflow-hidden" style="clip-path: inset(0% 0% 100% 0%);">
	<div id="overlay-backdrop" class="absolute inset-0 bg-white/[0.99] dark:bg-black/[0.99] backdrop-blur-2xl theme-transition"></div>
	
	<div id="raka-menu-container" class="relative z-10 h-full w-full max-w-none flex flex-col justify-between px-6 sm:px-10 md:px-14 lg:px-20 xl:px-28 2xl:px-36 pt-6 sm:pt-8 md:pt-10 pb-8 overflow-y-auto">
		
		<!-- ── Overlay Top Bar ── -->
		<div id="overlay-top-bar" class="w-full flex items-center justify-between pb-5 mb-4 md:mb-6 border-b border-black/10 dark:border-white/10 shrink-0">
			<!-- Brand -->
			<div id="overlay-brand" class="shrink-0 flex items-center">
				<a id="overlay-brand-link" class="hover:opacity-80 transition-opacity flex items-center gap-2" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img id="overlay-brand-img-light" class="h-6 sm:h-7 w-auto object-contain block dark:hidden" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					<img id="overlay-brand-img-dark" class="h-6 sm:h-7 w-auto object-contain hidden dark:block" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal-white.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
				</a>
			</div>

			<!-- Center Meta Info -->
			<div id="overlay-center-tag" class="hidden sm:flex items-center gap-3 text-[11px] md:text-xs font-mono tracking-[0.25em] uppercase text-neutral-400 dark:text-neutral-500">
				<span id="overlay-tag-txt">DIRECTORY</span>
				<span id="overlay-tag-dot" class="opacity-40">·</span>
				<span id="overlay-tag-vol">MMXXV</span>
			</div>

			<!-- Right Controls: Cart + Theme Toggle & Close Button -->
			<div id="overlay-controls" class="flex items-center gap-3 sm:gap-4">
				<!-- Cart Button in Overlay -->
				<a
					id="overlay-cart-btn"
					href="<?php echo esc_url( $cart_url ); ?>"
					onclick="if(window.openRakaCart){window.openRakaCart(event);return false;}"
					class="relative w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full text-neutral-800 dark:text-white/90 hover:text-black dark:hover:text-white bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-105 focus:outline-none cursor-pointer"
					aria-label="Shopping Cart"
					title="Shopping Cart"
				>
					<i id="overlay-cart-icon" class="ri-shopping-bag-3-line text-lg block"></i>
					<span
						id="overlay-cart-count"
						class="raka-cart-count-badge absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-black text-white dark:bg-white dark:text-black text-[10px] font-bold font-mono flex items-center justify-center border border-white dark:border-black transition-transform duration-300 <?php echo ( $cart_count > 0 ) ? 'scale-100' : 'scale-0'; ?>"
					>
						<?php echo esc_html( (string) $cart_count ); ?>
					</span>
				</a>

				<!-- Theme Toggle in Overlay -->
				<button
					id="overlay-theme-btn"
					type="button"
					onclick="window.toggleRakaTheme(event)"
					class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full text-neutral-800 dark:text-white/90 hover:text-black dark:hover:text-white bg-black/5 dark:bg-white/10 hover:bg-black/10 dark:hover:bg-white/20 border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-105 focus:outline-none cursor-pointer"
					aria-label="Toggle dark / light mode"
					title="Toggle theme"
				>
					<i id="overlay-theme-sun" class="ri-sun-line text-lg block text-amber-500"></i>
					<i id="overlay-theme-moon" class="ri-moon-line text-lg hidden text-sky-400"></i>
				</button>

				<!-- Minimalist X Close Button -->
				<button
					id="raka-menu-close-btn"
					type="button"
					class="btn-menu-close group w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full border border-black/15 dark:border-white/20 bg-black/5 dark:bg-white/10 hover:bg-black/15 dark:hover:bg-white/25 text-neutral-900 dark:text-white transition-all duration-300 hover:scale-105 cursor-pointer focus:outline-none shadow-xs"
					aria-label="Close menu"
					title="Close menu"
				>
					<i id="icon-close-menu" class="ri-close-line text-xl sm:text-2xl transition-transform duration-300 group-hover:rotate-90"></i>
				</button>
			</div>
		</div>

		<?php
		$custom_nav_tree = function_exists( 'raka_get_nav_menu_tree' ) ? raka_get_nav_menu_tree( 'primary-menu' ) : [];
		if ( ! empty( $custom_nav_tree ) ) :
		?>
			<div id="menu-panels-viewport" class="menu-panels-grid flex-1 w-full relative items-stretch min-h-[calc(100vh-220px)] py-2">
				<!-- LEVEL 1: ROOT MENU -->
				<div id="menu-panel-root" class="w-full h-full min-h-full flex flex-col justify-center opacity-100 transform translate-y-0">
					<div id="root-menu-list" class="flex flex-col gap-1 sm:gap-2">
						<?php
						$item_idx = 1;
						foreach ( $custom_nav_tree as $nav_item ) :
							$has_children = ! empty( $nav_item->children );
							$pad_idx      = str_pad( (string) $item_idx, 2, '0', STR_PAD_LEFT );
							$delay_ms     = ( $item_idx * 60 ) . 'ms';
						?>
							<div id="menu-item-wrap-<?php echo esc_attr( $nav_item->ID ); ?>" class="group block overflow-hidden py-1 sm:py-2">
								<?php if ( $has_children ) : ?>
									<button id="menu-trigger-<?php echo esc_attr( $nav_item->ID ); ?>" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-<?php echo esc_attr( $nav_item->ID ); ?>" data-delay="<?php echo esc_attr( $delay_ms ); ?>">
										<span id="menu-text-<?php echo esc_attr( $nav_item->ID ); ?>" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn">
											<span id="menu-idx-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal"><?php echo esc_html( $pad_idx ); ?></span>
											<?php echo esc_html( $nav_item->title ); ?>
										</span>
										<span id="menu-meta-<?php echo esc_attr( $nav_item->ID ); ?>" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
											<span id="menu-badge-<?php echo esc_attr( $nav_item->ID ); ?>" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800"><?php echo count( $nav_item->children ); ?> ITEMS</span>
											<i id="menu-icon-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
										</span>
									</button>
								<?php else : ?>
									<a id="menu-link-<?php echo esc_attr( $nav_item->ID ); ?>" href="<?php echo esc_url( $nav_item->url ); ?>" class="nav-menu-link w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-delay="<?php echo esc_attr( $delay_ms ); ?>">
										<span id="menu-text-<?php echo esc_attr( $nav_item->ID ); ?>" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn">
											<span id="menu-idx-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal"><?php echo esc_html( $pad_idx ); ?></span>
											<?php echo esc_html( $nav_item->title ); ?>
										</span>
										<span id="menu-meta-<?php echo esc_attr( $nav_item->ID ); ?>" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
											<i id="menu-icon-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-right-up-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
										</span>
									</a>
								<?php endif; ?>
							</div>
						<?php
							$item_idx++;
						endforeach;
						?>
					</div>
				</div>

				<!-- LEVEL 2: DYNAMIC SLIDING MEGA PANELS -->
				<?php
				$p_idx = 1;
				foreach ( $custom_nav_tree as $nav_item ) :
					if ( empty( $nav_item->children ) ) {
						$p_idx++;
						continue;
					}
					$pad_idx = str_pad( (string) $p_idx, 2, '0', STR_PAD_LEFT );
				?>
					<div id="mega-panel-<?php echo esc_attr( $nav_item->ID ); ?>" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<div id="bar-mega-<?php echo esc_attr( $nav_item->ID ); ?>" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-<?php echo esc_attr( $nav_item->ID ); ?>" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $pad_idx . ' · ' . $nav_item->title ); ?></span>
						</div>

						<div id="grid-mega-children-<?php echo esc_attr( $nav_item->ID ); ?>" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8 flex-1">
							<?php foreach ( $nav_item->children as $child ) : ?>
								<a id="child-link-<?php echo esc_attr( $child->ID ); ?>" href="<?php echo esc_url( $child->url ); ?>" class="group flex flex-col justify-between p-6 rounded-2xl bg-black/5 dark:bg-white/5 border border-black/5 dark:border-white/10 hover:border-black/20 dark:hover:border-white/25 transition-all duration-300 hover:scale-[1.02]">
									<div id="child-top-<?php echo esc_attr( $child->ID ); ?>" class="flex items-center justify-between mb-4">
										<span id="child-title-<?php echo esc_attr( $child->ID ); ?>" class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors"><?php echo esc_html( $child->title ); ?></span>
										<i id="child-ico-<?php echo esc_attr( $child->ID ); ?>" class="ri-arrow-right-up-line text-neutral-400 group-hover:text-neutral-900 dark:group-hover:text-white transition-colors"></i>
									</div>
									<?php if ( ! empty( $child->description ) ) : ?>
										<p id="child-desc-<?php echo esc_attr( $child->ID ); ?>" class="text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2"><?php echo esc_html( $child->description ); ?></p>
									<?php endif; ?>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php
					$p_idx++;
				endforeach;
				?>
			</div>
		<?php endif; ?>

		<!-- Overlay Bottom Footer Bar -->
		<div id="nav-bottom-info" class="w-full flex items-center justify-between pt-6 mt-6 border-t border-black/10 dark:border-white/10 shrink-0 text-xs text-neutral-500 dark:text-neutral-400 font-hn">
			<div id="footer-info-loc" class="flex items-center gap-2">
				<i id="loc-icon" class="ri-map-pin-2-line text-sm"></i>
				<span>Bangkok, Thailand</span>
			</div>
			<div id="footer-info-copy" class="text-[11px] font-mono">
				© <?php echo esc_html( date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
			</div>
		</div>

	</div>
</div>
