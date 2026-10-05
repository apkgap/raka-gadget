<?php
/**
 * Template Name: Register Standalone Hero
 * Full-viewport Hero layout tailored with RAKA header controls, branding, and fullscreen menu overlay.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$video_url       = get_template_directory_uri() . '/assets/images/a9-air.mp4';
$logo_horizontal = get_template_directory_uri() . '/assets/images/logo-raka-horizontal.png';
$logo_icon       = get_template_directory_uri() . '/assets/images/logo-raka-icon.png';
$shop_url          = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' );
$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
$myaccount_url     = $myaccount_page_id ? get_permalink( $myaccount_page_id ) : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ) );
$cart_url          = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart' );
$cart_count        = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
$current_user      = wp_get_current_user();
$is_logged_in      = is_user_logged_in();

if ( $is_logged_in ) {
	wp_safe_redirect( $myaccount_url );
	exit;
}

$account_url   = home_url( '/login/' );
$account_label = esc_html__( 'Login', 'raka-preorder-theme' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> lang="en">
<head>
  <script>
    (function() {
      try {
        var theme = localStorage.getItem('raka_theme') || localStorage.getItem('obsidian_theme');
        if (theme === 'dark' || (!theme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
          document.documentElement.classList.remove('light');
        } else {
          document.documentElement.classList.add('light');
          document.documentElement.classList.remove('dark');
        }
      } catch(e) {}
    })();
  </script>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?php wp_title( '|', true, 'right' ); ?> <?php bloginfo( 'name' ); ?> — สมัครสมาชิก</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css">
  <?php wp_head(); ?>
  <script id="raka-global-theme-helpers">
    window.toggleRakaTheme = function(event) {
      const html = document.documentElement;
      const isDark = html.classList.contains('dark') || !html.classList.contains('light');
      const targetTheme = isDark ? 'light' : 'dark';

      const themeBtn = document.getElementById('raka-theme-btn');
      const sunIcon = document.getElementById('theme-icon-sun');
      const moonIcon = document.getElementById('theme-icon-moon');

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
    };
    window.toggleObsidianTheme = window.toggleRakaTheme;

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

    // Auto sync icon state on initial load
    (function initThemeSync() {
      const isDark = document.documentElement.classList.contains('dark') || !document.documentElement.classList.contains('light');
      const sunIcon = document.getElementById('theme-icon-sun');
      const moonIcon = document.getElementById('theme-icon-moon');
      if (isDark) {
        if (sunIcon) { sunIcon.classList.remove('block'); sunIcon.classList.add('hidden'); }
        if (moonIcon) { moonIcon.classList.remove('hidden'); moonIcon.classList.add('block'); }
      } else {
        if (sunIcon) { sunIcon.classList.remove('hidden'); sunIcon.classList.add('block'); }
        if (moonIcon) { moonIcon.classList.remove('block'); moonIcon.classList.add('hidden'); }
      }
    })();
  </script>

</head>
<body <?php body_class( 'mindora-register-body bg-neutral-100 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-100 antialiased min-h-screen' ); ?>>

  <section id="hero" class="relative w-full min-h-screen bg-white dark:bg-neutral-950 grid grid-cols-1 lg:grid-cols-[1.15fr_0.85fr] overflow-x-hidden lg:overflow-hidden">

    <!-- COLUMN 1 (LEFT): The Content & Register Container -->
    <div id="raka-hero-left" class="w-full flex flex-col justify-between p-4 sm:p-6 md:p-8 lg:p-10 xl:p-12 overflow-y-auto min-h-screen lg:min-h-0">

      <!-- Topbar Header -->
      <header id="raka-hero-topbar" class="w-full flex items-center justify-between pb-4 sm:pb-6 shrink-0">
        <div id="raka-topbar-brand-wrap" class="flex items-center">
          <a id="raka-topbar-brand-link" class="flex items-center" href="<?php echo esc_url( home_url( '/' ) ); ?>">
            <img id="raka-topbar-brand-img-light" class="h-6 sm:h-7 w-auto object-contain block dark:hidden" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal.png' ); ?>" alt="RAKA GADGET">
            <img id="raka-topbar-brand-img-dark" class="h-6 sm:h-7 w-auto object-contain hidden dark:block" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo-raka-horizontal-white.png' ); ?>" alt="RAKA GADGET">
          </a>
        </div>

        <!-- THEME HEADER CONTROLS -->
        <div id="raka-topbar-controls" class="flex items-center gap-2 sm:gap-3">

          <!-- User Account Dropdown -->
          <div id="raka-account-menu-wrap" class="relative">
            <button
              id="raka-account-btn"
              class="relative w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 border border-black/10 dark:border-white/15 text-neutral-900 dark:text-white transition-all cursor-pointer focus:outline-none"
              type="button"
              onclick="window.toggleRakaAccountMenu(event)"
              aria-label="<?php esc_attr_e( 'Account Menu', 'raka-preorder-theme' ); ?>"
              title="<?php esc_attr_e( 'Account', 'raka-preorder-theme' ); ?>"
            >
              <i id="raka-account-icon" class="ri-user-line text-sm sm:text-base"></i>
            </button>

            <!-- Account Dropdown Menu -->
            <div
              id="raka-account-dropdown"
              class="absolute right-0 top-full mt-2 w-64 p-4 rounded-3xl bg-white/95 dark:bg-neutral-900/95 backdrop-blur-2xl border border-black/10 dark:border-white/15 shadow-2xl transition-all duration-300 transform opacity-0 invisible translate-y-2 pointer-events-none z-50 text-neutral-900 dark:text-white"
            >
              <div id="raka-account-guest-header" class="pb-3 mb-3 border-b border-black/10 dark:border-white/10">
                <h4 id="raka-account-guest-title" class="text-xs font-bold font-hn leading-tight"><?php esc_html_e( 'RAKA GADGET Account', 'raka-preorder-theme' ); ?></h4>
                <p id="raka-account-guest-sub" class="text-[10px] text-neutral-500 dark:text-neutral-400 font-hn mt-0.5"><?php esc_html_e( 'Sign in to access order tracking & perks', 'raka-preorder-theme' ); ?></p>
              </div>
              <div id="raka-account-guest-actions" class="flex flex-col gap-2">
                <a id="raka-guest-login-btn" class="w-full flex items-center justify-center gap-2 py-2 px-3 rounded-xl bg-neutral-950 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950 text-xs font-bold tracking-wider uppercase transition-all shadow-md" href="<?php echo esc_url( home_url( '/login/' ) ); ?>">
                  <i id="raka-guest-login-ico" class="ri-login-box-line text-sm"></i>
                  <span><?php esc_html_e( 'เข้าสู่ระบบ', 'raka-preorder-theme' ); ?></span>
                </a>
              </div>
            </div>
          </div>

          <!-- Cart Drawer Button -->
          <a
            id="raka-cart-btn"
            class="relative w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 border border-black/10 dark:border-white/15 text-neutral-900 dark:text-white transition-all cursor-pointer focus:outline-none"
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
            class="relative w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/15 border border-black/10 dark:border-white/15 text-neutral-900 dark:text-white transition-all cursor-pointer focus:outline-none"
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

      <!-- Stage Center -->
      <div id="raka-hero-stage" class="w-full max-w-md mx-auto my-auto py-4 sm:py-6 flex flex-col items-center text-center">
        <div id="raka-stage-emblem" class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-neutral-950 dark:bg-white p-3.5 mb-4 flex items-center justify-center shadow-lg">
          <img id="raka-stage-emblem-img" class="w-full h-full object-contain filter invert dark:invert-0" src="<?php echo esc_url( $logo_icon ); ?>" alt="Raka">
        </div>
        <h1 id="raka-hero-headline" class="text-2xl sm:text-3xl md:text-4xl font-light tracking-tight text-neutral-950 dark:text-white mb-2 leading-tight"><b>Join Us</b> create account<br />with <b>RAKA GADGET</b></h1>
        <svg id="raka-hero-squiggle" class="w-32 sm:w-44 h-auto text-neutral-950 dark:text-white mx-auto mb-6" viewBox="0 0 320 26" aria-hidden="true">
          <path d="M2 20C34 4 60 3 88 12s52 12 80 3 54-12 82-3 46 10 68 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/>
        </svg>

        <!-- Register Form Mount -->
        <div id="raka-stage-form-mount" class="w-full text-left">
          <?php echo do_shortcode( '[wc_custom_register]' ); ?>
        </div>
      </div>

    </div>

    <!-- COLUMN 2 (RIGHT): Dedicated Video Column (No Overlap) -->
    <div id="raka-hero-right" class="relative hidden lg:block h-full w-full overflow-hidden bg-neutral-900">
      <video id="raka-hero-video" class="absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline preload="auto" poster="https://images.higgs.ai/?default=1&output=webp&url=https%3A%2F%2Fd8j0ntlcm91z4.cloudfront.net%2Fuser_38xzZboKViGWJOttwIXH07lWA1P%2Fhf_20260831_232824_93a0252b-e9f2-432f-9961-555a2e458100.png&w=1920&q=85">
        <source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
      </video>

      <!-- Floating Controls on Top Right of Video -->
      <div id="raka-side-controls" class="absolute top-6 right-6 z-20 flex items-center gap-3">
        <a id="raka-explore-shop-btn" class="w-11 h-11 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md border border-white/20 text-white flex items-center justify-center transition-all hover:scale-105" href="<?php echo esc_url( $shop_url ); ?>" aria-label="Explore Shop">
          <svg id="raka-explore-shop-svg" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>
          </svg>
        </a>
        <a id="raka-side-account-btn" class="flex items-center gap-2.5 py-1.5 pl-3.5 pr-1.5 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md border border-white/20 text-white transition-all text-xs font-semibold hover:scale-105" href="<?php echo esc_url( home_url( '/login/' ) ); ?>" aria-label="Login">
          <svg id="raka-side-account-svg" class="w-3.5 h-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          <span id="raka-account-pill-label" class="font-medium tracking-wide"><?php esc_html_e( 'เข้าสู่ระบบ', 'raka-preorder-theme' ); ?></span>
          <div id="raka-account-pill-avatar" class="w-8 h-8 rounded-full bg-white text-black flex items-center justify-center overflow-hidden shrink-0 font-bold text-xs uppercase">
            <img id="raka-account-avatar-img" class="w-full h-full object-cover" src="<?php echo esc_url( $logo_icon ); ?>" alt="Account">
          </div>
        </a>
      </div>

      <a id="raka-side-cart-fab" class="absolute bottom-6 right-6 z-20 w-12 h-12 rounded-full bg-white dark:bg-neutral-900 text-neutral-950 dark:text-white shadow-2xl flex items-center justify-center border border-black/10 dark:border-white/20 transition-all hover:scale-110" href="<?php echo esc_url( $shop_url ); ?>" aria-label="Shop Products">
        <i id="raka-side-cart-fab-icon" class="ri-shopping-bag-3-line text-xl"></i>
      </a>
    </div>

  </section>

  <!-- ══════════ FULL THEME NAVIGATION OVERLAY (CLONED FROM THEME HEADER) ══════════ -->
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

  <script>
    /* ═════════ IIFE #1: Entry Animation Cleanup ═════════ */
    (function initAnimationCleanup() {
      const animSelectors = [
        '.topbar .brand', '.topbar .theme-topbar-controls',
        '.stage .stage__emblem', '.stage .headline', '.stage .squiggle',
        '.cards .card', '.side .pill-btn',
        '.side .account', '.side .arrow-fab'
      ];
      const elements = document.querySelectorAll(animSelectors.join(', '));
      elements.forEach(el => {
        el.addEventListener('animationend', function handler(e) {
          if (e.target === el) {
            el.classList.add('anim-done');
            el.removeEventListener('animationend', handler);
          }
        });
      });
    })();

    /* ═════════ IIFE #2: Fullscreen Theme Navigation & Header Controls ═════════ */
    (function initMenuAndControls() {
      const menuBtn = document.getElementById('raka-menu-btn');
      const overlay = document.getElementById('raka-menu-overlay');
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
        if (!rootPanel) return;
        const linkTexts = rootPanel.querySelectorAll('.nav-link-text');
        linkTexts.forEach((span, idx) => {
          const trigger = span.closest('.mega-menu-trigger');
          const delay = trigger ? trigger.getAttribute('data-delay') : (idx * 60 + 'ms');
          span.style.transitionDelay = delay;
          span.style.transform = 'translateY(0)';
        });
      }

      function animateRootLinksOut() {
        if (!rootPanel) return;
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
        menuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        if (isOpen) {
          document.body.style.overflow = 'hidden';
          overlay.style.pointerEvents = 'auto';
          overlay.style.clipPath = 'inset(0% 0% 0% 0%)';

          // Burger icon -> X
          if (burgerTop) {
            burgerTop.style.transform = 'translateY(7.5px) rotate(45deg)';
            burgerTop.style.width = '100%';
          }
          if (burgerMid) {
            burgerMid.style.opacity = '0';
            burgerMid.style.transform = 'scaleX(0)';
          }
          if (burgerBot) {
            burgerBot.style.transform = 'translateY(-7.5px) rotate(-45deg)';
            burgerBot.style.width = '100%';
          }

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
          if (burgerTop) {
            burgerTop.style.transform = '';
            burgerTop.style.width = '100%';
          }
          if (burgerMid) {
            burgerMid.style.opacity = '1';
            burgerMid.style.transform = '';
          }
          if (burgerBot) {
            burgerBot.style.transform = '';
            burgerBot.style.width = '85%';
          }

          animateRootLinksOut();

          if (navBottom) {
            navBottom.style.transitionDelay = '0ms';
            navBottom.style.opacity = '0';
            navBottom.style.transform = 'translateY(20px)';
          }

          setTimeout(() => {
            if (!isOpen) {
              resetMenuToRootInstant();
            }
          }, 700);
        }
      }

      // Hamburger button click
      menuBtn.addEventListener('click', (e) => {
        e.preventDefault();
        toggleMenu();
      });

      // Dedicated Cancel / Close buttons inside overlay
      const closeButtons = overlay.querySelectorAll('.btn-menu-close, #raka-menu-close-btn, #btn-close-overlay, #overlay-close-btn');
      closeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          toggleMenu(false);
        });
      });

      // ESC key to close
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isOpen) {
          toggleMenu(false);
        }
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
    })();
  </script>

  <?php wp_footer(); ?>
</body>
</html>
