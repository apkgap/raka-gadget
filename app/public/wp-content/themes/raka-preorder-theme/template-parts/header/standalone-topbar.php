<?php
/**
 * Standalone Topbar Header Component.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart' );
$cart_count   = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
?>
<!-- Topbar Header -->
<header id="raka-hero-topbar" class="topbar">
	<!-- Brand -->
	<div id="raka-topbar-brand-wrap" class="nav-left flex items-center">
		<a id="raka-topbar-brand-link" class="brand flex items-center" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img id="raka-topbar-brand-img-light" class="h-6 sm:h-7 w-auto object-contain block dark:hidden" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<img id="raka-topbar-brand-img-dark" class="h-6 sm:h-7 w-auto object-contain hidden dark:block" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal-white.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		</a>
	</div>

	<!-- Controls -->
	<div id="raka-topbar-controls" class="theme-topbar-controls">
		
		<!-- User Account Dropdown -->
		<div id="raka-account-menu-wrap" class="relative">
			<button
				id="raka-account-btn"
				class="theme-ctrl-btn"
				type="button"
				onclick="window.toggleRakaAccountMenu(event)"
				aria-label="<?php esc_attr_e( 'Account Menu', 'raka-preorder-theme' ); ?>"
				title="<?php esc_attr_e( 'Account', 'raka-preorder-theme' ); ?>"
			>
				<?php if ( $is_logged_in ) : ?>
					<span id="raka-account-status-dot" class="w-2 h-2 rounded-full bg-emerald-500 absolute top-1 right-1"></span>
				<?php endif; ?>
				<i id="raka-account-icon" class="ri-user-line text-sm sm:text-base"></i>
			</button>

			<!-- Account Dropdown Menu -->
			<div
				id="raka-account-dropdown"
				class="absolute right-0 top-full mt-2 w-64 p-4 rounded-3xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-2xl border border-black/10 dark:border-white/15 shadow-2xl transition-all duration-300 transform opacity-0 invisible translate-y-2 pointer-events-none z-50 text-neutral-900 dark:text-white"
			>
				<?php if ( $is_logged_in ) : 
					$my_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/account' );
					$logout_url     = function_exists( 'wc_logout_url' ) ? wc_logout_url() : wp_logout_url( home_url() );
					$orders_url     = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/account' );
				?>
					<div id="raka-account-user-header" class="flex items-center gap-3 pb-3 mb-3 border-b border-black/10 dark:border-white/10">
						<div id="raka-account-avatar-char" class="w-9 h-9 rounded-2xl bg-red-500/10 text-red-600 dark:text-red-400 flex items-center justify-center font-bold text-xs shrink-0 border border-red-500/20">
							<?php echo esc_html( strtoupper( substr( $current_user->display_name, 0, 1 ) ) ); ?>
						</div>
						<div id="raka-account-user-meta" class="min-w-0 flex-1">
							<p id="raka-account-user-name" class="text-xs font-bold truncate font-hn leading-tight"><?php echo esc_html( $current_user->display_name ); ?></p>
							<p id="raka-account-user-email" class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate font-mono mt-0.5"><?php echo esc_html( $current_user->user_email ); ?></p>
						</div>
					</div>
					<div id="raka-account-links" class="flex flex-col gap-1 text-xs">
						<a id="raka-link-myaccount" class="flex items-center gap-2.5 px-3 py-2 rounded-xl font-medium hover:bg-black/5 dark:hover:bg-white/10 transition-colors" href="<?php echo esc_url( $my_account_url ); ?>">
							<i id="raka-icon-dashboard" class="ri-dashboard-line text-sm text-neutral-400"></i>
							<span><?php esc_html_e( 'My Account', 'raka-preorder-theme' ); ?></span>
						</a>
						<a id="raka-link-myorders" class="flex items-center gap-2.5 px-3 py-2 rounded-xl font-medium hover:bg-black/5 dark:hover:bg-white/10 transition-colors" href="<?php echo esc_url( $orders_url ); ?>">
							<i id="raka-icon-orders" class="ri-file-list-3-line text-sm text-neutral-400"></i>
							<span><?php esc_html_e( 'My Orders', 'raka-preorder-theme' ); ?></span>
						</a>
						<a id="raka-link-logout" class="flex items-center gap-2.5 px-3 py-2 rounded-xl font-medium text-red-600 dark:text-red-400 hover:bg-red-500/10 transition-colors mt-1 border-t border-black/5 dark:border-white/5 pt-2" href="<?php echo esc_url( $logout_url ); ?>">
							<i id="raka-icon-logout" class="ri-logout-box-r-line text-sm"></i>
							<span><?php esc_html_e( 'Log Out', 'raka-preorder-theme' ); ?></span>
						</a>
					</div>
				<?php else : 
					$login_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/login/' );
				?>
					<div id="raka-account-guest-header" class="pb-3 mb-3 border-b border-black/10 dark:border-white/10">
						<h4 id="raka-account-guest-title" class="text-xs font-bold font-hn leading-tight"><?php esc_html_e( 'RAKA GADGET Account', 'raka-preorder-theme' ); ?></h4>
						<p id="raka-account-guest-sub" class="text-[10px] text-neutral-500 dark:text-neutral-400 font-hn mt-0.5"><?php esc_html_e( 'Sign in to access order tracking & perks', 'raka-preorder-theme' ); ?></p>
					</div>
					<div id="raka-account-guest-actions" class="flex flex-col gap-2">
						<a id="raka-guest-login-btn" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-neutral-950 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950 text-xs font-bold tracking-wider uppercase transition-all shadow-md" href="<?php echo esc_url( $login_url ); ?>">
							<i id="raka-guest-login-ico" class="ri-login-box-line text-sm"></i>
							<span><?php esc_html_e( 'Sign In / Login', 'raka-preorder-theme' ); ?></span>
						</a>
						<a id="raka-guest-register-btn" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 text-neutral-900 dark:text-white text-xs font-semibold border border-black/10 dark:border-white/20 transition-all" href="<?php echo esc_url( home_url( '/register/' ) ); ?>">
							<i id="raka-guest-register-ico" class="ri-user-add-line text-sm"></i>
							<span><?php esc_html_e( 'Register', 'raka-preorder-theme' ); ?></span>
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Cart Drawer Button -->
		<a
			id="raka-cart-btn"
			class="theme-ctrl-btn"
			href="<?php echo esc_url( $cart_url ); ?>"
			onclick="if(window.openRakaCart){window.openRakaCart(event);return false;}"
			aria-label="Shopping Cart"
			title="Shopping Cart"
		>
			<i id="raka-cart-icon" class="ri-shopping-bag-3-line text-sm sm:text-base"></i>
			<span
				id="raka-cart-count"
				class="raka-cart-count-badge absolute -top-1 -right-1 min-w-[15px] h-[15px] px-1 rounded-full bg-neutral-950 text-white dark:bg-white dark:text-neutral-950 text-[9px] font-bold font-mono flex items-center justify-center border border-white dark:border-black transition-transform duration-300 <?php echo ( $cart_count > 0 ) ? 'scale-100' : 'scale-0'; ?>"
			>
				<?php echo esc_html( (string) $cart_count ); ?>
			</span>
		</a>

		<!-- Theme Mode Switcher -->
		<button
			id="raka-theme-btn"
			class="theme-ctrl-btn"
			type="button"
			onclick="window.toggleRakaTheme(event)"
			aria-label="Toggle dark / light mode"
			title="Toggle theme"
		>
			<i id="theme-icon-sun" class="ri-sun-line text-sm sm:text-base text-amber-500"></i>
			<i id="theme-icon-moon" class="ri-moon-line text-sm sm:text-base hidden text-sky-400"></i>
		</button>

		<!-- Hamburger Menu Button -->
		<button
			id="raka-menu-btn"
			class="relative w-7 sm:w-8 md:w-9 h-5 sm:h-6 flex flex-col justify-between items-end focus:outline-none cursor-pointer py-0.5 ml-1"
			type="button"
			aria-label="Open menu"
		>
			<span id="burger-top" class="block h-[2px] bg-neutral-950 dark:bg-white transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] origin-center w-full"></span>
			<span id="burger-mid" class="block h-[2px] bg-neutral-950 dark:bg-white transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] w-[65%]"></span>
			<span id="burger-bot" class="block h-[2px] bg-neutral-950 dark:bg-white transition-all duration-500 ease-[cubic-bezier(0.76,0,0.24,1)] origin-center w-[85%]"></span>
		</button>

	</div>
</header>
