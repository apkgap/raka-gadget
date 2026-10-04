<?php
/**
 * The header for OBSIDIAN theme.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html id="obsidian-theme-html" class="light" <?php language_attributes(); ?>>
<head id="obsidian-theme-head">
	<meta id="obsidian-meta-charset" charset="<?php bloginfo( 'charset' ); ?>">
	<meta id="obsidian-meta-viewport" name="viewport" content="width=device-width, initial-scale=1.0">
	<title id="obsidian-theme-title"><?php wp_title( '|', true, 'right' ); ?></title>
	
	<!-- Fonts & Icons -->
	<link rel="stylesheet" href="https://db.onlinewebfonts.com/c/95cecf452d3208890088a5b4c19c7ecf?family=Helvetica+Neue+ME">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css">
	
	<script id="obsidian-theme-init">
		// Instant theme restore in head (Default: Light Mode)
		(function() {
			var saved = localStorage.getItem('obsidian_theme');
			if (saved === 'dark') {
				document.documentElement.classList.remove('light');
				document.documentElement.classList.add('dark');
			} else {
				document.documentElement.classList.remove('dark');
				document.documentElement.classList.add('light');
			}
		})();
	</script>

	<?php wp_head(); ?>
</head>
<body id="obsidian-body" <?php body_class( 'bg-white dark:bg-obsidian text-black dark:text-white theme-transition' ); ?>>
	<?php wp_body_open(); ?>
	<div id="root" class="bg-inherit theme-transition">

		<!-- Fallback Ripple Circle for browsers without native View Transitions -->
		<div id="theme-ripple-circle"></div>

		<?php
		$is_home    = is_front_page();
		$cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
		$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart' );
		?>
		<!-- ── Prominent Semantic Header Component ── -->
		<header 
			id="raka-site-header" 
			class="<?php echo $is_home ? 'absolute top-0 left-0 right-0 z-50 pointer-events-none transition-all duration-300' : 'sticky top-0 left-0 right-0 z-50 bg-white/95 dark:bg-obsidian/95 backdrop-blur-md border-b border-black/10 dark:border-white/10 transition-all duration-300 pointer-events-auto'; ?>"
		>
			<nav id="raka-nav" class="flex items-center justify-between px-3.5 sm:px-6 md:px-10 lg:px-16 <?php echo $is_home ? 'pt-4 sm:pt-5 md:pt-7 pb-4 sm:pb-5 pointer-events-none' : 'py-3.5 md:py-5 pointer-events-auto'; ?>">
				<!-- Left Slot: Pre-Order Menu -->
				<div id="raka-nav-left-slot" class="flex-1 flex items-center justify-start pointer-events-auto min-w-0">
					<a
						id="raka-nav-preorder-btn"
						href="<?php echo esc_url( home_url( '/pre-order/' ) ); ?>"
						class="group relative inline-flex items-center gap-1.5 sm:gap-2.5 pl-1.5 pr-2.5 sm:pl-2 sm:pr-4 py-1 sm:py-1.5 rounded-full text-[11px] sm:text-xs font-bold tracking-wider uppercase text-neutral-900 dark:text-white bg-white/80 dark:bg-neutral-900/80 hover:bg-white dark:hover:bg-neutral-800 backdrop-blur-xl border border-black/10 hover:border-red-500/50 dark:border-white/15 dark:hover:border-red-500/50 shadow-sm hover:shadow-lg transition-all duration-300 hover:scale-105 font-hn cursor-pointer shrink-0"
						aria-label="<?php esc_attr_e( 'Check Pre-Order Status', 'raka-preorder-theme' ); ?>"
					>
						<!-- Glowing Icon Badge -->
						<span id="raka-nav-preorder-icon-wrap" class="w-5 h-5 sm:w-7 sm:h-7 rounded-full bg-red-600 text-white flex items-center justify-center text-[10px] sm:text-sm shadow-md shadow-red-600/30 group-hover:scale-110 group-hover:rotate-12 transition-all duration-300">
							<i id="raka-nav-preorder-ico" class="ri-radar-line"></i>
						</span>
						<!-- Text & Accent -->
						<span id="raka-nav-preorder-text" class="group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors"><?php esc_html_e( 'Check Pre-Order', 'raka-preorder-theme' ); ?></span>
						<!-- Arrow Indicator -->
						<i id="raka-nav-preorder-arrow" class="ri-arrow-right-up-line text-xs text-neutral-400 dark:text-neutral-500 group-hover:text-red-600 dark:group-hover:text-red-400 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all hidden sm:inline-block"></i>
					</a>
				</div>

				<!-- Centered Brand Logo -->
				<div id="raka-nav-brand" class="pointer-events-auto shrink-0 px-2 sm:px-4 flex items-center justify-center">
					<a id="raka-brand-link" class="hover:opacity-80 transition-opacity flex items-center justify-center" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img id="raka-brand-img-light" class="h-6 sm:h-7 md:h-8 w-auto object-contain block dark:hidden" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
						<img id="raka-brand-img-dark" class="h-6 sm:h-7 md:h-8 w-auto object-contain hidden dark:block" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal-white.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
					</a>
				</div>

				<!-- Right Controls (Compare + Account Dropdown + Cart + Theme Toggle + Hamburger) -->
				<div id="raka-nav-controls" class="pointer-events-auto flex-1 flex items-center justify-end gap-1.5 sm:gap-2.5 md:gap-3.5 min-w-0">
					<!-- Compare Button -->
					<a
						id="raka-compare-btn"
						href="<?php echo esc_url( home_url( '/compare' ) ); ?>"
						class="relative hidden md:flex w-8 h-8 md:w-10 md:h-10 items-center justify-center rounded-full text-neutral-950 dark:text-white hover:text-black dark:hover:text-white bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 backdrop-blur-md border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-110 focus:outline-none cursor-pointer"
						aria-label="<?php esc_attr_e( 'Compare Products', 'raka-preorder-theme' ); ?>"
						title="<?php esc_attr_e( 'Compare Products', 'raka-preorder-theme' ); ?>"
					>
						<i id="raka-compare-icon" class="ri-scales-3-line text-base md:text-lg block"></i>
					</a>

					<!-- User Account Dropdown -->
					<div id="raka-account-wrapper" class="relative">
						<button
							id="raka-account-btn"
							type="button"
							onclick="window.toggleRakaAccountMenu(event)"
							class="relative w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 flex items-center justify-center rounded-full text-neutral-950 dark:text-white hover:text-black dark:hover:text-white bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 backdrop-blur-md border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-110 focus:outline-none cursor-pointer"
							aria-label="<?php esc_attr_e( 'User Account', 'raka-preorder-theme' ); ?>"
							title="<?php esc_attr_e( 'User Account', 'raka-preorder-theme' ); ?>"
							aria-expanded="false"
						>
							<i id="raka-account-icon" class="ri-user-3-line text-base md:text-lg block"></i>
						</button>

						<!-- Account Submenu Dropdown Popup -->
						<div
							id="raka-account-dropdown"
							class="absolute right-0 mt-3 w-72 max-w-[90vw] rounded-3xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-2xl border border-black/10 dark:border-white/15 shadow-2xl p-5 transition-all duration-300 opacity-0 invisible translate-y-2 pointer-events-none z-[100] text-neutral-900 dark:text-white"
						>
							<?php if ( is_user_logged_in() ) : 
								$current_user   = wp_get_current_user();
								$my_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account' );
								$logout_url     = function_exists( 'wc_logout_url' ) ? wc_logout_url() : wp_logout_url( home_url() );
								$orders_url     = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/orders' );
							?>
								<!-- Logged In User State -->
								<div id="raka-account-user-header" class="flex items-center gap-3 pb-3.5 mb-3.5 border-b border-black/10 dark:border-white/10">
									<div id="raka-account-avatar" class="w-10 h-10 rounded-2xl bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center font-bold font-hn text-sm shrink-0 border border-red-500/20">
										<?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?>
									</div>
									<div id="raka-account-user-info" class="min-w-0 flex-1">
										<p id="raka-account-user-name" class="text-sm font-bold truncate font-hn leading-tight"><?php echo esc_html( $current_user->display_name ); ?></p>
										<p id="raka-account-user-email" class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate font-mono mt-0.5"><?php echo esc_html( $current_user->user_email ); ?></p>
									</div>
								</div>
								<div id="raka-account-user-links" class="flex flex-col gap-1.5">
									<a id="raka-account-link-dashboard" href="<?php echo esc_url( $my_account_url ); ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 transition-colors font-hn">
										<i id="raka-account-ico-dash" class="ri-dashboard-line text-base text-neutral-500 dark:text-neutral-400"></i>
										<span><?php esc_html_e( 'My Account', 'raka-preorder-theme' ); ?></span>
									</a>
									<a id="raka-account-link-orders" href="<?php echo esc_url( $orders_url ); ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold hover:bg-black/5 dark:hover:bg-white/10 transition-colors font-hn">
										<i id="raka-account-ico-ord" class="ri-file-list-3-line text-base text-neutral-500 dark:text-neutral-400"></i>
										<span><?php esc_html_e( 'My Orders', 'raka-preorder-theme' ); ?></span>
									</a>
									<a id="raka-account-link-logout" href="<?php echo esc_url( $logout_url ); ?>" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-500/10 transition-colors font-hn mt-1 border-t border-black/5 dark:border-white/5 pt-2.5">
										<i id="raka-account-ico-out" class="ri-logout-box-r-line text-base"></i>
										<span><?php esc_html_e( 'Log Out', 'raka-preorder-theme' ); ?></span>
									</a>
								</div>
							<?php else : 
								$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account' );
							?>
								<!-- Guest State -->
								<div id="raka-account-guest-header" class="pb-3.5 mb-3.5 border-b border-black/10 dark:border-white/10">
									<div id="raka-account-guest-wrap" class="flex items-center gap-3">
										<div id="raka-account-guest-badge" class="w-10 h-10 rounded-2xl bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center border border-red-500/20 shrink-0">
											<i id="raka-account-guest-ico" class="ri-user-smile-line text-lg"></i>
										</div>
										<div id="raka-account-guest-text" class="flex-1 min-w-0">
											<h4 id="raka-account-guest-title" class="text-sm font-bold font-hn leading-tight"><?php esc_html_e( 'Welcome to OBSIDIAN', 'raka-preorder-theme' ); ?></h4>
											<p id="raka-account-guest-sub" class="text-[11px] text-neutral-500 dark:text-neutral-400 font-hn mt-0.5"><?php esc_html_e( 'Sign in to access pre-orders', 'raka-preorder-theme' ); ?></p>
										</div>
									</div>
								</div>
								<div id="raka-account-guest-actions" class="flex flex-col gap-2">
									<a
										id="raka-account-btn-login"
										href="<?php echo esc_url( $account_url ); ?>"
										class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-2xl bg-neutral-950 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950 text-xs font-bold font-hn tracking-wider uppercase shadow-md transition-all cursor-pointer"
									>
										<i id="raka-account-ico-log" class="ri-login-box-line text-base"></i>
										<span><?php esc_html_e( 'Sign In / Login', 'raka-preorder-theme' ); ?></span>
									</a>
									<a
										id="raka-account-btn-register"
										href="<?php echo esc_url( $account_url ); ?>"
										class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-2xl bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 text-neutral-900 dark:text-white text-xs font-semibold font-hn border border-black/10 dark:border-white/20 transition-all cursor-pointer"
									>
										<i id="raka-account-ico-reg" class="ri-user-add-line text-base"></i>
										<span><?php esc_html_e( 'Create Account', 'raka-preorder-theme' ); ?></span>
									</a>
								</div>
							<?php endif; ?>
						</div>
					</div>

					<!-- Cart Button -->
					<a
						id="raka-cart-btn"
						href="<?php echo esc_url( $cart_url ); ?>"
						onclick="if(window.openRakaCart){window.openRakaCart(event);return false;}"
						class="relative w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 flex items-center justify-center rounded-full text-neutral-950 dark:text-white hover:text-black dark:hover:text-white bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 backdrop-blur-md border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-110 focus:outline-none cursor-pointer"
						aria-label="Shopping Cart"
						title="Shopping Cart"
					>
						<i id="raka-cart-icon" class="ri-shopping-bag-3-line text-sm sm:text-base md:text-lg block"></i>
						<span
							id="raka-cart-count"
							class="raka-cart-count-badge absolute -top-1 -right-1 min-w-[15px] h-[15px] sm:min-w-[18px] sm:h-[18px] px-1 rounded-full bg-neutral-950 text-white dark:bg-white dark:text-neutral-950 text-[9px] sm:text-[10px] font-bold font-mono flex items-center justify-center border border-white dark:border-black transition-transform duration-300 <?php echo ( $cart_count > 0 ) ? 'scale-100' : 'scale-0'; ?>"
						>
							<?php echo esc_html( (string) $cart_count ); ?>
						</span>
					</a>

					<!-- Theme Toggle Button -->
					<button
						id="raka-theme-btn"
						type="button"
						onclick="window.toggleRakaTheme(event)"
						class="relative w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 flex items-center justify-center rounded-full text-neutral-950 dark:text-white hover:text-black dark:hover:text-white bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 backdrop-blur-md border border-black/10 dark:border-white/20 transition-all duration-300 hover:scale-110 focus:outline-none cursor-pointer"
						aria-label="Toggle dark / light mode"
						title="Toggle theme"
					>
						<i id="theme-icon-sun" class="ri-sun-line text-sm sm:text-base md:text-lg block text-amber-500"></i>
						<i id="theme-icon-moon" class="ri-moon-line text-sm sm:text-base md:text-lg hidden text-sky-400"></i>
					</button>

					<!-- Hamburger Button -->
					<button
						id="raka-menu-btn"
						type="button"
						class="relative w-7 sm:w-8 md:w-9 h-5 sm:h-6 flex flex-col justify-between items-end focus:outline-none cursor-pointer py-0.5 ml-0.5"
						aria-label="Open menu"
					>
						<span id="burger-top" class="block h-[2px] bg-neutral-950 dark:bg-white transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] origin-center w-full"></span>
						<span id="burger-mid" class="block h-[2px] bg-neutral-950 dark:bg-white transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] w-[65%]"></span>
						<span id="burger-bot" class="block h-[2px] bg-neutral-950 dark:bg-white transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] origin-center w-[85%]"></span>
					</button>
				</div>
			</nav>
		</header>

		<!-- ── Fullscreen Menu Overlay with Sliding Mega Menus ── -->
		<div id="raka-menu-overlay" class="fixed inset-0 z-[80] transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] pointer-events-none overflow-hidden" style="clip-path: inset(0% 0% 100% 0%);">
			<div id="overlay-backdrop" class="absolute inset-0 bg-white/[0.99] dark:bg-black/[0.99] backdrop-blur-2xl theme-transition"></div>
			
			<div id="raka-menu-container" class="relative z-10 h-full w-full max-w-none flex flex-col justify-between px-6 sm:px-10 md:px-14 lg:px-20 xl:px-28 2xl:px-36 pt-6 sm:pt-8 md:pt-10 pb-8 overflow-y-auto">
				
				<!-- ── Overlay Top Bar (Dedicated Minimalist Close Header) ── -->
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

					<!-- Right Controls: Cart + Theme Toggle & Minimalist X Button -->
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
				$custom_nav_tree = raka_get_nav_menu_tree( 'primary-menu' );
				if ( ! empty( $custom_nav_tree ) ) :
				?>
					<div id="menu-panels-viewport" class="menu-panels-grid flex-1 w-full relative items-stretch min-h-[calc(100vh-220px)] py-2">
						<!-- ─── LEVEL 1: ROOT CUSTOM WORDPRESS MENU ──────────────── -->
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
												<span id="menu-text-<?php echo esc_attr( $nav_item->ID ); ?>" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
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
												<span id="menu-text-<?php echo esc_attr( $nav_item->ID ); ?>" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
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

						<!-- ─── LEVEL 2: DYNAMIC SLIDING MEGA PANELS ──────────────── -->
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
							<!-- Header / Back Button -->
							<div id="bar-mega-<?php echo esc_attr( $nav_item->ID ); ?>" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
								<button id="btn-back-<?php echo esc_attr( $nav_item->ID ); ?>" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
									<i id="icon-back-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
									<span>BACK</span>
								</button>
								<span id="tag-header-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $pad_idx . ' · ' . $nav_item->title ); ?></span>
							</div>

							<!-- Sub-links Grid (Fullscreen Ultra-Minimalist Editorial Layout) -->
							<div id="grid-mega-<?php echo esc_attr( $nav_item->ID ); ?>" class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
								<!-- Main Columns: Categories & Brands -->
								<div id="sublinks-col-<?php echo esc_attr( $nav_item->ID ); ?>" class="lg:col-span-8 flex flex-col justify-start space-y-8 lg:space-y-12 py-2">
									<?php
									$c_idx = 1;
									foreach ( $nav_item->children as $child ) :
										$c_pad        = str_pad( (string) $c_idx, 2, '0', STR_PAD_LEFT );
										$has_subitems = ! empty( $child->children );
									?>
										<?php if ( $has_subitems ) : ?>
											<!-- Minimalist Group / Category -->
											<div id="group-block-<?php echo esc_attr( $child->ID ); ?>" class="flex flex-col space-y-4">
												<!-- Minimal Group Header -->
												<div id="group-head-<?php echo esc_attr( $child->ID ); ?>" class="flex items-center gap-2.5">
													<span id="group-pad-<?php echo esc_attr( $child->ID ); ?>" class="text-[11px] font-mono tracking-widest text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $c_pad ); ?>.</span>
													<span id="group-title-<?php echo esc_attr( $child->ID ); ?>" class="text-xs font-semibold uppercase tracking-[0.25em] text-neutral-400 dark:text-neutral-500"><?php echo esc_html( $child->title ); ?></span>
												</div>

												<!-- Minimal Brand Links Typography List -->
												<div id="group-items-grid-<?php echo esc_attr( $child->ID ); ?>" class="flex flex-wrap items-center gap-x-8 lg:gap-x-12 gap-y-4 pt-1">
													<?php
													$sub_idx = 1;
													foreach ( $child->children as $grandchild ) :
													?>
														<a id="grandchild-link-<?php echo esc_attr( $grandchild->ID ); ?>" href="<?php echo esc_url( $grandchild->url ); ?>" class="group/item inline-flex items-center gap-2.5 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
															<span id="grandchild-txt-<?php echo esc_attr( $grandchild->ID ); ?>" class="font-hn"><?php echo esc_html( $grandchild->title ); ?></span>
															<i id="grandchild-icon-<?php echo esc_attr( $grandchild->ID ); ?>" class="ri-arrow-right-up-line text-base md:text-lg opacity-30 group-hover/item:opacity-100 group-hover/item:translate-x-0.5 group-hover/item:-translate-y-0.5 transition-all"></i>
														</a>
													<?php
														$sub_idx++;
													endforeach;
													?>
												</div>
											</div>
										<?php else : ?>
											<!-- Minimal Direct Sublink -->
											<a id="child-link-<?php echo esc_attr( $child->ID ); ?>" href="<?php echo esc_url( $child->url ); ?>" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
												<span id="child-pad-<?php echo esc_attr( $child->ID ); ?>" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal"><?php echo esc_html( $c_pad ); ?>.</span>
												<span id="child-txt-<?php echo esc_attr( $child->ID ); ?>" class="font-hn"><?php echo esc_html( $child->title ); ?></span>
												<i id="child-icon-<?php echo esc_attr( $child->ID ); ?>" class="ri-arrow-right-up-line text-lg md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
											</a>
										<?php endif; ?>
									<?php
										$c_idx++;
									endforeach;
									?>
								</div>

								<!-- Parent Spotlight / Overview Column (Ultra-Minimal, Open Air) -->
								<div id="spotlight-col-<?php echo esc_attr( $nav_item->ID ); ?>" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-5">
									<div id="spotlight-tag-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">OVERVIEW</div>
									<h3 id="spotlight-title-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-extrabold uppercase tracking-tight text-neutral-900 dark:text-white font-hn"><?php echo esc_html( $nav_item->title ); ?></h3>
									<?php if ( ! empty( $nav_item->description ) ) : ?>
										<p id="spotlight-desc-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed max-w-md font-hn"><?php echo esc_html( $nav_item->description ); ?></p>
									<?php else : ?>
										<p id="spotlight-desc-<?php echo esc_attr( $nav_item->ID ); ?>" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed max-w-md font-hn">Discover complete collections, verified brand lineups, and newly released hardware.</p>
									<?php endif; ?>
									<div id="spotlight-btn-wrap-<?php echo esc_attr( $nav_item->ID ); ?>" class="pt-4">
										<a id="spotlight-explore-<?php echo esc_attr( $nav_item->ID ); ?>" href="<?php echo esc_url( $nav_item->url ?: '#' ); ?>" class="group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-semibold text-neutral-900 dark:text-white hover:opacity-50 transition-opacity">
											<span>EXPLORE ALL <?php echo esc_html( $nav_item->title ); ?></span>
											<i id="spotlight-icon-<?php echo esc_attr( $nav_item->ID ); ?>" class="ri-arrow-right-line text-sm md:text-base transition-transform group-hover:translate-x-1.5"></i>
										</a>
									</div>
								</div>
							</div>
						</div>
					<?php
						$p_idx++;
					endforeach;
					?>
					</div>
				<?php else : ?>
					<div id="menu-panels-viewport-default" class="menu-panels-grid flex-1 w-full relative items-stretch min-h-[calc(100vh-200px)] py-2">
						<!-- ─── LEVEL 1: ROOT DEFAULT CURATED MENU ────────────────── -->
						<div id="menu-panel-root" class="w-full h-full min-h-full flex flex-col justify-center opacity-100 transform translate-y-0">
							<div id="root-menu-list" class="flex flex-col gap-1 sm:gap-2">
							
							<!-- Item 1: Atelier (Has Mega) -->
							<div id="menu-item-atelier-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-atelier" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-atelier" data-delay="60ms">
									<span id="menu-text-atelier" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-atelier" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">01</span>
										Atelier
									</span>
									<span id="menu-meta-atelier" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-atelier" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">4 SECTIONS</span>
										<i id="menu-icon-atelier" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 2: Artists (Has Mega) -->
							<div id="menu-item-artists-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-artists" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-artists" data-delay="120ms">
									<span id="menu-text-artists" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-artists" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">02</span>
										Artists
									</span>
									<span id="menu-meta-artists" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-artists" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">ROSTER</span>
										<i id="menu-icon-artists" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 3: Editions & Shop (Has Mega) -->
							<div id="menu-item-shop-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-shop" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-shop" data-delay="180ms">
									<span id="menu-text-shop" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-shop" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">03</span>
										Editions & Shop
									</span>
									<span id="menu-meta-shop" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-shop" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">CATALOGUE</span>
										<i id="menu-icon-shop" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 4: Gallery & Exhibitions (Has Mega) -->
							<div id="menu-item-gallery-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-gallery" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-gallery" data-delay="240ms">
									<span id="menu-text-gallery" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-gallery" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">04</span>
										Gallery
									</span>
									<span id="menu-meta-gallery" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-gallery" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">SHOWCASE</span>
										<i id="menu-icon-gallery" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

							<!-- Item 5: Concierge & Inquire (Has Mega) -->
							<div id="menu-item-inquire-wrap" class="group block overflow-hidden py-1 sm:py-2">
								<button id="menu-trigger-inquire" type="button" class="mega-menu-trigger w-full text-left flex items-center justify-between group cursor-pointer focus:outline-none" data-mega-target="mega-panel-inquire" data-delay="300ms">
									<span id="menu-text-inquire" class="nav-link-text flex items-baseline gap-3 sm:gap-4 text-[7.5vw] sm:text-[5vw] md:text-[3.2vw] lg:text-[2.4vw] xl:text-[38px] font-extrabold uppercase text-neutral-900/90 dark:text-white/90 leading-[1.1] tracking-tight transition-all duration-700 ease-[cubic-bezier(0.76,0,0.24,1)] group-hover:text-black dark:group-hover:text-white group-hover:translate-x-3 font-hn" style="transform: translateY(110%);">
										<span id="menu-idx-inquire" class="text-xs sm:text-sm font-mono tracking-widest text-neutral-400 dark:text-neutral-500 font-normal">05</span>
										Concierge & Inquire
									</span>
									<span id="menu-meta-inquire" class="flex items-center gap-2 text-xs sm:text-sm uppercase tracking-[0.2em] text-neutral-400 dark:text-neutral-500 group-hover:text-black dark:group-hover:text-white transition-colors duration-300">
										<span id="menu-badge-inquire" class="hidden sm:inline-block px-2.5 py-1 rounded-full text-[10px] border border-neutral-300 dark:border-neutral-800">CONTACT</span>
										<i id="menu-icon-inquire" class="ri-arrow-right-line text-lg md:text-xl transition-transform duration-300 group-hover:translate-x-1.5"></i>
									</span>
								</button>
							</div>

						</div>
					</div>

					<!-- ─── LEVEL 2: DEFAULT SLIDING MEGA PANELS ──────────────── -->
					
					<!-- PANEL 1: ATELIER -->
					<div id="mega-panel-atelier" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="atelier-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-atelier" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-atelier" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-atelier" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">01 · ATELIER</span>
						</div>

						<!-- Content Grid -->
						<div id="atelier-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
							<!-- Sub-Links Column -->
							<div id="atelier-sublinks-col" class="lg:col-span-8 flex flex-col justify-start space-y-6 lg:space-y-8 py-2">
								<a id="sublink-atelier-1" href="#sec_2" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-1" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">01.</span>
									<span id="sublink-txt-atelier-1" class="font-hn">Workshop & Philosophy</span>
									<i id="sublink-icon-atelier-1" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
								<a id="sublink-atelier-2" href="#sec_3" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-2" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">02.</span>
									<span id="sublink-txt-atelier-2" class="font-hn">Bespoke Commissions</span>
									<i id="sublink-icon-atelier-2" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
								<a id="sublink-atelier-3" href="#sec_4" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-3" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">03.</span>
									<span id="sublink-txt-atelier-3" class="font-hn">Materials & Foundry</span>
									<i id="sublink-icon-atelier-3" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
								<a id="sublink-atelier-4" href="#sec_5" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="sublink-num-atelier-4" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">04.</span>
									<span id="sublink-txt-atelier-4" class="font-hn">Archive MMXX - MMXXV</span>
									<i id="sublink-icon-atelier-4" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
								</a>
							</div>

							<!-- Details & Meta Column -->
							<div id="atelier-meta-col" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-6">
								<div id="atelier-meta-block-1">
									<div id="atelier-lbl-loc" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono mb-1">FOUNDRY LOCATION</div>
									<div id="atelier-val-loc" class="text-sm md:text-base text-neutral-800 dark:text-neutral-300 font-light font-hn">Brooklyn Navy Yard, New York</div>
								</div>
								<div id="atelier-meta-block-2">
									<div id="atelier-lbl-stat" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono mb-1">STATUS</div>
									<div id="atelier-val-stat" class="text-sm md:text-base text-neutral-800 dark:text-neutral-300 font-mono">BATCH 04 IN PRODUCTION</div>
								</div>
								<div id="atelier-meta-block-3">
									<div id="atelier-lbl-inq" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono mb-1">CONCIERGE</div>
									<div id="atelier-val-inq" class="text-sm md:text-base text-neutral-800 dark:text-neutral-300 font-light font-hn">atelier@obsidian.studio</div>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 2: ARTISTS -->
					<div id="mega-panel-artists" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="artists-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-artists" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-artists" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-artists" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">02 · ARTISTS</span>
						</div>

						<!-- Content Grid -->
						<div id="artists-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
							<div id="artists-list-col" class="lg:col-span-8 flex flex-col justify-start space-y-6 lg:space-y-8 py-2">
								<a id="artist-item-1" href="#sec_2" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="artist-num-1" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">01.</span>
									<span id="artist-name-1" class="font-hn">Evelyn Vance</span>
									<span id="artist-role-1" class="text-[10px] sm:text-xs tracking-widest text-neutral-400 uppercase font-mono">Lead Sculptor</span>
								</a>
								<a id="artist-item-2" href="#sec_3" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="artist-num-2" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">02.</span>
									<span id="artist-name-2" class="font-hn">Kaito Kurogane</span>
									<span id="artist-role-2" class="text-[10px] sm:text-xs tracking-widest text-neutral-400 uppercase font-mono">Master Metallurgist</span>
								</a>
								<a id="artist-item-3" href="#sec_4" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
									<span id="artist-num-3" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">03.</span>
									<span id="artist-name-3" class="font-hn">Seraphina Novak</span>
									<span id="artist-role-3" class="text-[10px] sm:text-xs tracking-widest text-neutral-400 uppercase font-mono">Concept Director</span>
								</a>
							</div>

							<div id="artists-feature-col" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-5">
								<div id="artists-prog-tag" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">RESIDENCY</div>
								<h3 id="artists-prog-title" class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold uppercase tracking-tight text-neutral-900 dark:text-white font-hn">MMXXVI FELLOWSHIP</h3>
								<p id="artists-prog-desc" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Applications open for sculptors, miniaturists, and character artists seeking studio residency.</p>
								<div id="artists-prog-btn-wrap" class="pt-4">
									<a id="artists-prog-link" href="#sec_5" class="group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-semibold text-neutral-900 dark:text-white hover:opacity-50 transition-opacity">
										<span>SUBMIT PORTFOLIO</span>
										<i id="artists-prog-icon" class="ri-arrow-right-line text-sm md:text-base transition-transform group-hover:translate-x-1.5"></i>
									</a>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 3: EDITIONS & SHOP -->
					<div id="mega-panel-shop" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="shop-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-shop" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-shop" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-shop" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">03 · EDITIONS & SHOP</span>
						</div>

						<!-- Content Grid -->
						<div id="shop-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 xl:gap-24 flex-1 w-full items-start">
							<div id="shop-cats-col" class="lg:col-span-8 flex flex-col justify-start space-y-6 lg:space-y-8 py-2">
								<?php
								$wc_cats = raka_get_wc_categories();
								if ( ! empty( $wc_cats ) ) :
									$cat_idx = 1;
									foreach ( array_slice( $wc_cats, 0, 5, true ) as $cid => $cname ) :
										$cterm = get_term( $cid, 'product_cat' );
										$clink = $cterm && ! is_wp_error( $cterm ) ? get_term_link( $cterm ) : '#';
										$c_pad = str_pad( (string) $cat_idx, 2, '0', STR_PAD_LEFT );
								?>
									<a id="wc-cat-link-<?php echo esc_attr( $cid ); ?>" href="<?php echo esc_url( $clink ); ?>" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="wc-cat-pad-<?php echo esc_attr( $cid ); ?>" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal"><?php echo esc_html( $c_pad ); ?>.</span>
										<span id="wc-cat-txt-<?php echo esc_attr( $cid ); ?>" class="font-hn"><?php echo esc_html( $cname ); ?></span>
										<i id="wc-cat-icon-<?php echo esc_attr( $cid ); ?>" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
								<?php
									$cat_idx++;
									endforeach;
								else :
								?>
									<a id="static-cat-1" href="#sec_2" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="static-cat-pad-1" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">01.</span>
										<span id="static-cat-txt-1" class="font-hn">Pre-Order Editions</span>
										<i id="static-cat-icon-1" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
									<a id="static-cat-2" href="#sec_3" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="static-cat-pad-2" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">02.</span>
										<span id="static-cat-txt-2" class="font-hn">Shadow Box Sculptures</span>
										<i id="static-cat-icon-2" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
									<a id="static-cat-3" href="#sec_4" class="group inline-flex items-baseline gap-4 text-2xl sm:text-3xl md:text-3xl lg:text-4xl xl:text-5xl font-light uppercase tracking-tight text-neutral-900 dark:text-white hover:opacity-40 transition-opacity">
										<span id="static-cat-pad-3" class="text-xs sm:text-sm font-mono text-neutral-400 font-normal">03.</span>
										<span id="static-cat-txt-3" class="font-hn">1/6 Scale Collectibles</span>
										<i id="static-cat-icon-3" class="ri-arrow-right-up-line text-base md:text-xl opacity-30 group-hover:opacity-100 transition-opacity"></i>
									</a>
								<?php endif; ?>
							</div>

							<div id="shop-feature-col" class="lg:col-span-4 flex flex-col justify-start border-t lg:border-t-0 lg:border-l border-black/10 dark:border-white/10 lg:pl-12 xl:pl-16 pt-8 lg:pt-2 space-y-5">
								<div id="shop-feat-tag" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">CATALOGUE</div>
								<h3 id="shop-feat-title" class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold uppercase tracking-tight text-neutral-900 dark:text-white font-hn">FULL STORE</h3>
								<p id="shop-feat-desc" class="text-xs md:text-sm lg:text-base text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Browse all in-stock collectibles, active pre-orders, and archival editions.</p>
								<div id="shop-feat-btn-wrap" class="pt-4">
									<a id="shop-feat-link" href="<?php echo esc_url( function_exists('wc_get_page_permalink') ? wc_get_page_permalink( 'shop' ) : home_url('/shop') ); ?>" class="group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-semibold text-neutral-900 dark:text-white hover:opacity-50 transition-opacity">
										<span>VIEW COMPLETE STORE</span>
										<i id="shop-feat-icon" class="ri-arrow-right-line text-sm md:text-base transition-transform group-hover:translate-x-1.5"></i>
									</a>
								</div>
							</div>
						</div>
					</div>

					<!-- PANEL 4: GALLERY -->
					<div id="mega-panel-gallery" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="gallery-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-gallery" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-gallery" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-gallery" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">04 · GALLERY</span>
						</div>

						<!-- Content Grid -->
						<div id="gallery-grid" class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 xl:gap-16 flex-1 w-full items-start py-2">
							<div id="gallery-exhibit-1" class="flex flex-col justify-between space-y-3">
								<div id="gallery-tag-1" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">CURRENT</div>
								<h4 id="gallery-title-1" class="text-xl md:text-2xl lg:text-3xl font-light uppercase text-neutral-900 dark:text-white font-hn">Forms of the Unseen</h4>
								<p id="gallery-desc-1" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Oct 12 – Dec 30, MMXXV<br>Obsidian Main Hall, Brooklyn</p>
							</div>

							<div id="gallery-exhibit-2" class="flex flex-col justify-between space-y-3">
								<div id="gallery-tag-2" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">UPCOMING</div>
								<h4 id="gallery-title-2" class="text-xl md:text-2xl lg:text-3xl font-light uppercase text-neutral-900 dark:text-white font-hn">Tokyo Figuration</h4>
								<p id="gallery-desc-2" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Feb 15 – Feb 22, MMXXVI<br>Mori Arts Center Gallery, Roppongi</p>
							</div>

							<div id="gallery-exhibit-3" class="flex flex-col justify-between space-y-3">
								<div id="gallery-tag-3" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">SPATIAL</div>
								<h4 id="gallery-title-3" class="text-xl md:text-2xl lg:text-3xl font-light uppercase text-neutral-900 dark:text-white font-hn">3D Viewing Chamber</h4>
								<p id="gallery-desc-3" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light leading-relaxed font-hn">Interactive WebGL spatial gallery viewing for collectors worldwide.</p>
							</div>
						</div>
					</div>

					<!-- PANEL 5: INQUIRE & CONCIERGE -->
					<div id="mega-panel-inquire" class="mega-panel w-full min-h-full flex flex-col justify-start py-2 opacity-0 pointer-events-none invisible transform translate-y-8">
						<!-- Header / Back Button -->
						<div id="inquire-mega-nav-bar" class="flex items-center justify-between pb-4 mb-6 md:mb-10 border-b border-black/10 dark:border-white/10 w-full shrink-0">
							<button id="btn-back-inquire" type="button" class="btn-mega-back group inline-flex items-center gap-3 text-xs sm:text-sm uppercase tracking-[0.25em] font-medium text-neutral-900 dark:text-white hover:opacity-50 focus:outline-none cursor-pointer">
								<i id="icon-back-inquire" class="ri-arrow-left-line text-base transition-transform group-hover:-translate-x-1.5"></i>
								<span>BACK</span>
							</button>
							<span id="tag-header-inquire" class="text-[11px] sm:text-xs uppercase tracking-[0.25em] font-mono text-neutral-400 dark:text-neutral-500">05 · CONCIERGE</span>
						</div>

						<!-- Content Grid -->
						<div id="inquire-grid" class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 xl:gap-16 flex-1 w-full items-start py-2">
							<div id="inquire-box-1" class="flex flex-col space-y-2">
								<div id="inquire-tag-1" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">ACQUISITIONS</div>
								<h4 id="inquire-title-1" class="text-lg md:text-xl font-light text-neutral-900 dark:text-white uppercase font-hn">Collector Concierge</h4>
								<p id="inquire-desc-1" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light font-hn">Dedicated advisory for private collectors & insured delivery.</p>
								<div id="inquire-val-1" class="text-xs sm:text-sm font-mono text-neutral-800 dark:text-neutral-300 pt-1">collector@obsidian.studio</div>
							</div>

							<div id="inquire-box-2" class="flex flex-col space-y-2">
								<div id="inquire-tag-2" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">VISITS</div>
								<h4 id="inquire-title-2" class="text-lg md:text-xl font-light text-neutral-900 dark:text-white uppercase font-hn">Private Appointment</h4>
								<p id="inquire-desc-2" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light font-hn">Foundry & salon visits by reservation only.</p>
								<div id="inquire-val-2" class="text-xs sm:text-sm font-mono text-neutral-800 dark:text-neutral-300 pt-1">+1 (718) 555-0199</div>
							</div>

							<div id="inquire-box-3" class="flex flex-col space-y-2">
								<div id="inquire-tag-3" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">EDITORIAL</div>
								<h4 id="inquire-title-3" class="text-lg md:text-xl font-light text-neutral-900 dark:text-white uppercase font-hn">Media Inquiries</h4>
								<p id="inquire-desc-3" class="text-xs md:text-sm text-neutral-500 dark:text-neutral-400 font-light font-hn">High-res asset packs & exhibition releases.</p>
								<div id="inquire-val-3" class="text-xs sm:text-sm font-mono text-neutral-800 dark:text-neutral-300 pt-1">press@obsidian.studio</div>
							</div>
						</div>
					</div>
					</div>
				<?php endif; ?>

				<!-- ─── BOTTOM INFO BAR (Fixed at footer of overlay) ────────── -->
				<div id="nav-bottom-info" class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6 pt-6 border-t border-neutral-200/80 dark:border-neutral-800/80 transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] opacity-0 translate-y-[20px]">
					<div id="footer-info-loc">
						<div id="lbl-info-loc" class="text-[10px] tracking-[0.25em] uppercase text-black/40 dark:text-white/30 mb-1 font-hn">Location</div>
						<div id="val-info-loc" class="text-xs text-black/60 dark:text-white/50 font-light leading-relaxed font-hn">Brooklyn, New York</div>
					</div>
					<div id="footer-info-inq">
						<div id="lbl-info-inq" class="text-[10px] tracking-[0.25em] uppercase text-black/40 dark:text-white/30 mb-1 font-hn">Inquiries</div>
						<div id="val-info-inq" class="text-xs text-black/60 dark:text-white/50 font-light font-hn">hello@obsidian.studio</div>
					</div>
					<div id="footer-info-copy" class="hidden sm:block text-[10px] tracking-[0.2em] uppercase text-black/30 dark:text-white/20 font-hn">
						© OBSIDIAN MMXXV
					</div>
				</div>

			</div>
		</div>



