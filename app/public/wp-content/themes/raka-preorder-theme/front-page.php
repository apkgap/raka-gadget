<?php
/**
 * The template for displaying the front page.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Fetch configured hero options and dynamic repeater card data
$default_overlay  = get_template_directory_uri() . '/assets/images/angel-hero.png';
$hero_main_title  = raka_get_option( 'hero_main_title', 'OBSIDIAN' );
$hero_caption     = raka_get_option( 'hero_caption', "RITUALS REFINED:\nTRACING LINE, SHADOW & SKIN\nACROSS A LIVING FORM." );
$hero_video_url   = raka_get_option( 'hero_video_url', 'https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260821_132241_f0feb221-ccb9-4dce-87b4-7dfa66adabb6.mp4' );
$hero_overlay_url = raka_get_option( 'hero_overlay_url', $default_overlay );
if ( strpos( $hero_overlay_url, 'figma.site' ) !== false ) {
	$hero_overlay_url = $default_overlay;
}

$hero_cards          = raka_get_hero_cards();
$hero_card_quads     = array_chunk( $hero_cards, 4 );
$total_quads         = count( $hero_card_quads );
$hero_section_height = max( 280, ( $total_quads * 90 ) + 100 ) . 'vh';

$featured_categories = raka_get_featured_categories();
$featured_brands     = raka_get_featured_brands();
$contact_options     = raka_get_contact_options();

$flash_sale_products = raka_get_flash_sale_products( 8 );
$homepage_catalog    = raka_get_homepage_catalog_products( 12 );
$catalog_products    = $homepage_catalog['products'];
$catalog_categories  = $homepage_catalog['categories'];


if ( ! function_exists( 'raka_render_hero_card_item' ) ) {
	/**
	 * Render a single full-sized card inside the hero quad layout.
	 *
	 * @param array  $card           Card data array.
	 * @param string $custom_classes Optional extra CSS classes.
	 */
	function raka_render_hero_card_item( $card, $custom_classes = '' ) {
		if ( empty( $card ) ) {
			return;
		}
		?>
		<?php if ( ! empty( $card['show_custom_text'] ) ) : ?>
			<!-- Custom Statement / Atmospheric Text Card Mode -->
			<div class="pointer-events-auto block max-w-md lg:max-w-[420px] w-full p-6 md:p-7 rounded-3xl bg-white/90 dark:bg-black/85 backdrop-blur-2xl border border-black/10 dark:border-white/15 shadow-2xl theme-transition group hover:border-black/30 dark:hover:border-white/30 transition-all duration-300 <?php echo esc_attr( $custom_classes ); ?>">
				<?php if ( ! empty( $card['tag'] ) ) : ?>
					<div class="text-[11px] tracking-[0.25em] uppercase text-black/50 dark:text-white/50 mb-3 font-hn"><?php echo esc_html( $card['tag'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $card['title'] ) ) : ?>
					<h3 class="text-xl md:text-2xl font-black text-black dark:text-white mb-2 font-hn group-hover:translate-x-1 transition-transform leading-tight"><?php echo esc_html( $card['title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( ! empty( $card['description'] ) ) : ?>
					<p class="text-xs md:text-sm leading-[1.7] text-black/80 dark:text-white/85 font-light font-hn">
						<?php echo esc_html( $card['description'] ); ?>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $card['url'] ) && '#' !== $card['url'] ) : ?>
					<a href="<?php echo esc_url( $card['url'] ); ?>" class="mt-4 inline-flex items-center gap-2 text-xs uppercase tracking-widest text-black/60 dark:text-white/60 group-hover:text-black dark:group-hover:text-white font-hn">
						<span><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Explore', 'raka-preorder-theme' ) ); ?></span> <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
					</a>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<!-- WooCommerce Product Card Mode with Direct Add to Cart Action -->
			<div class="pointer-events-auto block max-w-full sm:max-w-md lg:max-w-[420px] w-full p-4 sm:p-5 md:p-6 rounded-2xl sm:rounded-3xl bg-white/95 dark:bg-black/90 backdrop-blur-2xl border border-black/10 dark:border-white/15 shadow-xl theme-transition group hover:border-black/30 dark:hover:border-white/30 transition-all duration-300 <?php echo esc_attr( $custom_classes ); ?>">
				<!-- Header: Tag + Price -->
				<div class="flex items-center justify-between gap-3 mb-2.5 sm:mb-3">
					<?php if ( ! empty( $card['tag'] ) ) : ?>
						<span class="inline-flex items-center px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-[10px] md:text-[11px] font-bold tracking-widest uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 font-hn">
							<?php echo esc_html( $card['tag'] ); ?>
						</span>
					<?php endif; ?>
					<?php if ( ! empty( $card['price_html'] ) ) : ?>
						<div class="text-xs sm:text-sm md:text-base font-bold text-neutral-900 dark:text-white font-hn">
							<?php echo esc_html( $card['price_html'] ); ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Card Content: Image & Text -->
				<div class="flex items-start gap-3 sm:gap-4">
					<?php if ( ! empty( $card['image_url'] ) ) : ?>
						<a href="<?php echo esc_url( $card['url'] ); ?>" class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-xl sm:rounded-2xl overflow-hidden bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 shrink-0 group-hover:scale-105 transition-transform duration-300 block">
							<img src="<?php echo esc_url( $card['image_url'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" class="w-full h-full object-cover" />
						</a>
					<?php endif; ?>
					<div class="flex-1 min-w-0">
						<?php if ( ! empty( $card['title'] ) ) : ?>
							<h3 class="text-sm sm:text-base md:text-lg font-bold text-black dark:text-white font-hn group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors leading-snug line-clamp-2">
								<a href="<?php echo esc_url( $card['url'] ); ?>">
									<?php echo esc_html( $card['title'] ); ?>
								</a>
							</h3>
						<?php endif; ?>
						<?php if ( ! empty( $card['description'] ) ) : ?>
							<p class="text-[11px] sm:text-xs leading-relaxed text-black/70 dark:text-white/75 font-light font-hn mt-1 sm:mt-1.5 line-clamp-2">
								<?php echo esc_html( $card['description'] ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Card CTA Action Footer: Direct Add to Cart + Details Link -->
				<div class="mt-3 sm:mt-4 pt-2.5 sm:pt-3.5 border-t border-black/10 dark:border-white/10 flex items-center justify-between gap-2 sm:gap-3">
					<?php if ( ! empty( $card['product_id'] ) ) : ?>
						<button
							type="button"
							onclick="window.rakaCardAddToCart(<?php echo esc_attr( $card['product_id'] ); ?>, this)"
							class="raka-hero-add-to-cart-btn inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-[11px] sm:text-xs font-bold uppercase tracking-wider font-hn shadow-md hover:shadow-lg transition-all cursor-pointer"
						>
							<i class="ri-shopping-bag-3-line text-xs sm:text-sm flex-shrink-0"></i>
							<span><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Pre-Order Now', 'raka-preorder-theme' ) ); ?></span>
						</button>
					<?php else : ?>
						<a
							href="<?php echo esc_url( $card['url'] ); ?>"
							class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-[11px] sm:text-xs font-bold uppercase tracking-wider font-hn shadow-md hover:shadow-lg transition-all"
						>
							<span><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Pre-Order Now', 'raka-preorder-theme' ) ); ?></span>
							<span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $card['url'] ) && '#' !== $card['url'] ) : ?>
						<a href="<?php echo esc_url( $card['url'] ); ?>" class="text-[11px] sm:text-xs font-semibold text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white inline-flex items-center gap-1 font-hn transition-colors">
							<span><?php esc_html_e( 'View Details', 'raka-preorder-theme' ); ?></span>
							<span>&rarr;</span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php
	}
}
?>

<!-- ── ScrollVideoHero Component ── -->
<section id="scroll-video-hero-section" class="relative" style="height: <?php echo esc_attr( $hero_section_height ); ?>;">
	<div id="hero-pinned-viewport" class="sticky top-0 w-full h-screen overflow-hidden bg-white dark:bg-obsidian theme-transition gpu-accel">

		<!-- 1. Video Layer -->
		<video
			id="hero-video"
			src="<?php echo esc_url( $hero_video_url ); ?>"
			muted
			playsinline
			preload="auto"
			class="absolute inset-0 w-full h-full object-cover object-top gpu-accel pointer-events-none"
			style="opacity: 0; will-change: transform, opacity;"
		></video>

		<!-- 2. Canvas Layer (Ultra Smooth WebCodecs Frame Blitter) -->
		<canvas
			id="hero-canvas"
			width="1280"
			height="720"
			class="absolute inset-0 w-full h-full object-cover object-top gpu-accel pointer-events-none transition-opacity duration-300"
			style="opacity: 0; will-change: transform, opacity;"
		></canvas>

		<!-- 3. Hero Overlay Image (Figma asset: Pure white wings & gold angel) -->
		<div id="hero-overlay-png-wrap" class="absolute inset-0 z-30 pointer-events-none gpu-accel transition-opacity duration-150">
			<img
				id="hero-overlay-png"
				alt=""
				class="w-full h-full object-cover object-top"
				src="<?php echo esc_url( $hero_overlay_url ); ?>"
			/>
		</div>

		<!-- 4. Hero Content (Section 1) -->
		<div id="hero-section-1" class="absolute inset-0 z-10 flex flex-col pointer-events-none gpu-accel">
			<!-- Centered Main Title -->
			<div id="hero-title-container" class="w-full flex justify-center mt-12 md:mt-14 px-2 select-none pointer-events-none overflow-hidden">
				<h1 id="hero-main-title" class="text-[17vw] sm:text-[20vw] md:text-[22vw] font-black tracking-[-0.04em] leading-[0.75] text-black dark:text-white uppercase select-none font-hn theme-transition text-center whitespace-nowrap">
					<?php echo esc_html( $hero_main_title ); ?>
				</h1>
			</div>

			<!-- Bottom Left: Caption & Contact Popup Button -->
			<div id="hero-bottom-left-panel" class="absolute bottom-20 sm:bottom-6 md:bottom-8 left-4 sm:left-6 md:left-10 right-4 sm:right-auto pointer-events-auto flex flex-col items-start gap-2.5 sm:gap-3.5 max-w-sm z-20">
				<p id="hero-caption-text" class="text-[10px] md:text-[11px] tracking-[0.14em] uppercase leading-relaxed text-black/85 dark:text-white/85 font-hn font-medium theme-transition max-w-[280px] sm:max-w-none">
					<?php echo nl2br( esc_html( $hero_caption ) ); ?>
				</p>
				<button
					id="hero-contact-trigger-btn"
					type="button"
					onclick="window.rakaOpenModal('contact')"
					class="group inline-flex items-center gap-2 sm:gap-2.5 px-4 sm:px-6 py-2.5 sm:py-3.5 rounded-full bg-white/95 dark:bg-black/90 hover:bg-white dark:hover:bg-black backdrop-blur-2xl border border-black/15 dark:border-white/25 text-xs sm:text-sm font-bold tracking-wider uppercase text-neutral-950 dark:text-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-300 font-hn cursor-pointer"
				>
					<span id="hero-contact-btn-pulse-wrap" class="relative flex h-2 w-2 sm:h-2.5 sm:w-2.5">
						<span id="hero-contact-btn-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
						<span id="hero-contact-btn-pulse" class="relative inline-flex rounded-full h-2 w-2 sm:h-2.5 sm:w-2.5 bg-emerald-500"></span>
					</span>
					<span id="hero-contact-btn-text"><?php echo esc_html( ! empty( $contact_options['contact_title'] ) ? $contact_options['contact_title'] : __( 'Contact & Store', 'raka-preorder-theme' ) ); ?></span>
					<i id="hero-contact-btn-icon" class="ri-arrow-right-up-line text-sm sm:text-base opacity-70 group-hover:opacity-100 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
				</button>
			</div>

			<!-- Bottom Right: Categories & Brands Popup Buttons -->
			<div id="hero-bottom-right-panel" class="absolute bottom-5 sm:bottom-6 md:bottom-8 left-4 right-4 sm:left-auto sm:right-6 md:right-10 pointer-events-auto flex items-center justify-between sm:justify-end gap-2 sm:gap-4 z-20">
				<button
					id="hero-categories-trigger-btn"
					type="button"
					onclick="window.rakaOpenModal('categories')"
					class="group flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 sm:gap-2.5 px-3.5 sm:px-6 py-2.5 sm:py-3.5 rounded-full bg-white/95 dark:bg-black/90 hover:bg-white dark:hover:bg-black backdrop-blur-2xl border border-black/15 dark:border-white/25 text-[11px] sm:text-sm font-bold tracking-wider uppercase text-neutral-950 dark:text-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-300 font-hn cursor-pointer"
				>
					<i id="hero-categories-btn-icon" class="ri-grid-fill text-sm sm:text-lg text-neutral-800 dark:text-neutral-200 opacity-80 group-hover:opacity-100 group-hover:rotate-12 transition-transform"></i>
					<span id="hero-categories-btn-text"><?php esc_html_e( 'Categories', 'raka-preorder-theme' ); ?></span>
					<span id="hero-categories-btn-count" class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold bg-neutral-200/80 dark:bg-neutral-800/90 text-neutral-900 dark:text-neutral-100 border border-black/5 dark:border-white/10 font-mono"><?php echo esc_html( count( $featured_categories ) ); ?></span>
				</button>

				<button
					id="hero-brands-trigger-btn"
					type="button"
					onclick="window.rakaOpenModal('brands')"
					class="group flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 sm:gap-2.5 px-3.5 sm:px-6 py-2.5 sm:py-3.5 rounded-full bg-white/95 dark:bg-black/90 hover:bg-white dark:hover:bg-black backdrop-blur-2xl border border-black/15 dark:border-white/25 text-[11px] sm:text-sm font-bold tracking-wider uppercase text-neutral-950 dark:text-white shadow-lg hover:shadow-xl hover:scale-105 active:scale-95 transition-all duration-300 font-hn cursor-pointer"
				>
					<i id="hero-brands-btn-icon" class="ri-fire-fill text-sm sm:text-lg text-amber-500 group-hover:scale-125 transition-transform"></i>
					<span id="hero-brands-btn-text"><?php esc_html_e( 'Brands', 'raka-preorder-theme' ); ?></span>
					<span id="hero-brands-btn-count" class="px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold bg-neutral-200/80 dark:bg-neutral-800/90 text-neutral-900 dark:text-neutral-100 border border-black/5 dark:border-white/10 font-mono"><?php echo esc_html( count( $featured_brands ) ); ?></span>
				</button>
			</div>
		</div>

		<!-- 5. Quad-Card Slides (Show 4 Cards Simultaneously Per Scroll Step: 2 Left, 2 Right) -->
		<?php foreach ( $hero_card_quads as $quad_idx => $quad ) : ?>
			<div id="hero-card-quad-<?php echo esc_attr( $quad_idx ); ?>" class="hero-card-slide hero-sub-section absolute inset-0 z-20 gpu-accel pointer-events-none p-4 sm:p-6 md:p-8 lg:p-12 pt-16 sm:pt-20 md:pt-24 lg:pt-28 flex flex-col md:flex-row items-center md:items-start justify-between gap-3 sm:gap-4 md:gap-8 lg:gap-12 overflow-y-auto md:overflow-visible" style="opacity: 0; visibility: hidden; transform: translate3d(0, 20px, 0);">

				<!-- Left Flank: Card 0 + Card 2 (if present) -->
				<div id="hero-quad-left-<?php echo esc_attr( $quad_idx ); ?>" class="flex flex-col gap-3 sm:gap-4 md:gap-5 w-full max-w-sm md:max-w-md lg:max-w-[420px]">
					<?php if ( isset( $quad[0] ) ) : ?>
						<?php raka_render_hero_card_item( $quad[0] ); ?>
					<?php endif; ?>
					<?php if ( isset( $quad[2] ) ) : ?>
						<?php raka_render_hero_card_item( $quad[2] ); ?>
					<?php endif; ?>
				</div>

				<!-- Right Flank: Card 1 + Card 3 (if present) -->
				<div id="hero-quad-right-<?php echo esc_attr( $quad_idx ); ?>" class="flex flex-col gap-3 sm:gap-4 md:gap-5 w-full max-w-sm md:max-w-md lg:max-w-[420px] md:mt-4 lg:mt-6">
					<?php if ( isset( $quad[1] ) ) : ?>
						<?php raka_render_hero_card_item( $quad[1] ); ?>
					<?php endif; ?>
					<?php if ( isset( $quad[3] ) ) : ?>
						<?php raka_render_hero_card_item( $quad[3] ); ?>
					<?php endif; ?>
				</div>

			</div>
		<?php endforeach; ?>
	</div>
</section>

<!-- ── SECTION: FLASH SALE (LIMITED DROPS) ── -->
<?php if ( ! empty( $flash_sale_products ) ) : ?>
<section id="raka-flashsale-section" class="relative z-20 py-16 sm:py-20 md:py-24 bg-neutral-950 border-t border-neutral-800/80 text-white overflow-hidden">
	<div id="raka-flashsale-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Section Header -->
		<div id="raka-flashsale-header" class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 sm:pb-10 border-b border-neutral-800">
			<!-- Title & Badge -->
			<div id="raka-flashsale-title-wrap" class="space-y-2.5">
				<div id="raka-flashsale-badge-row" class="flex items-center gap-2.5">
					<span id="raka-flashsale-pulse-dot" class="relative flex h-2.5 w-2.5">
						<span id="raka-flashsale-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
						<span id="raka-flashsale-static-dot" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
					</span>
					<span id="raka-flashsale-tag" class="text-xs font-mono font-bold tracking-[0.2em] text-red-400 uppercase">
						<?php esc_html_e( 'LIMITED TIME DROPS', 'raka-preorder-theme' ); ?>
					</span>
				</div>
				<h2 id="raka-flashsale-title" class="text-2xl sm:text-3xl md:text-4xl font-black font-hn tracking-tight text-white flex items-center gap-3">
					<span id="raka-flashsale-title-text"><?php esc_html_e( 'FLASH SALE', 'raka-preorder-theme' ); ?></span>
					<i id="raka-flashsale-fire-icon" class="ri-flashlight-fill text-red-500 text-2xl sm:text-3xl"></i>
				</h2>
				<p id="raka-flashsale-desc" class="text-xs sm:text-sm text-neutral-400 font-light font-hn max-w-md">
					<?php esc_html_e( 'Exclusive pre-order allocations at special introductory prices. Once stock is claimed, regular rates apply.', 'raka-preorder-theme' ); ?>
				</p>
			</div>

			<!-- Live Countdown Timer -->
			<div id="raka-flashsale-timer-wrap" class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 bg-neutral-900/90 border border-neutral-800 rounded-2xl p-3 sm:p-4 shrink-0">
				<div id="raka-flashsale-timer-label-wrap" class="flex items-center gap-2">
					<i id="raka-flashsale-timer-icon" class="ri-time-line text-red-400 text-lg"></i>
					<span id="raka-flashsale-timer-label" class="text-[11px] font-mono tracking-widest uppercase text-neutral-400">
						<?php esc_html_e( 'ENDS IN', 'raka-preorder-theme' ); ?>
					</span>
				</div>
				<div id="raka-flashsale-countdown-clock" class="flex items-center gap-1.5 font-mono text-white">
					<div id="raka-timer-box-hours" class="flex flex-col items-center justify-center w-12 h-11 rounded-xl bg-neutral-800 border border-neutral-700/60">
						<span id="raka-timer-hours" class="text-base sm:text-lg font-bold text-white tracking-wider">08</span>
						<span id="raka-timer-hours-sub" class="text-[9px] text-neutral-400 font-sans uppercase">HRS</span>
					</div>
					<span id="raka-timer-colon-1" class="text-red-500 font-bold text-lg">:</span>
					<div id="raka-timer-box-mins" class="flex flex-col items-center justify-center w-12 h-11 rounded-xl bg-neutral-800 border border-neutral-700/60">
						<span id="raka-timer-mins" class="text-base sm:text-lg font-bold text-white tracking-wider">42</span>
						<span id="raka-timer-mins-sub" class="text-[9px] text-neutral-400 font-sans uppercase">MIN</span>
					</div>
					<span id="raka-timer-colon-2" class="text-red-500 font-bold text-lg">:</span>
					<div id="raka-timer-box-secs" class="flex flex-col items-center justify-center w-12 h-11 rounded-xl bg-neutral-800 border border-neutral-700/60">
						<span id="raka-timer-secs" class="text-base sm:text-lg font-bold text-red-400 tracking-wider">19</span>
						<span id="raka-timer-secs-sub" class="text-[9px] text-neutral-400 font-sans uppercase">SEC</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Products Grid -->
		<div id="raka-flashsale-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 pt-8 sm:pt-10">
			<?php foreach ( $flash_sale_products as $item ) : ?>
				<div id="raka-flash-item-<?php echo esc_attr( (string) $item['id'] ); ?>" class="group relative flex flex-col justify-between rounded-2xl sm:rounded-3xl bg-neutral-900/70 border border-neutral-800/90 hover:border-red-500/50 transition-all duration-300 p-4 sm:p-5">
					<!-- Top Bar: Category & Discount Badge -->
					<div id="raka-flash-top-<?php echo esc_attr( (string) $item['id'] ); ?>" class="flex items-center justify-between gap-2 mb-3">
						<span id="raka-flash-cat-<?php echo esc_attr( (string) $item['id'] ); ?>" class="text-[10px] font-mono uppercase tracking-widest text-neutral-400 truncate">
							<?php echo esc_html( $item['category'] ); ?>
						</span>
						<span id="raka-flash-badge-<?php echo esc_attr( (string) $item['id'] ); ?>" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold tracking-wider uppercase bg-red-500/15 text-red-400 border border-red-500/30">
							-<?php echo esc_html( (string) $item['discount_pct'] ); ?>% OFF
						</span>
					</div>

					<!-- Image Box -->
					<a id="raka-flash-img-link-<?php echo esc_attr( (string) $item['id'] ); ?>" href="<?php echo esc_url( $item['url'] ); ?>" class="relative block aspect-square rounded-2xl overflow-hidden bg-black/40 border border-neutral-800 mb-4 group-hover:border-neutral-700 transition-colors">
						<?php if ( ! empty( $item['image_url'] ) ) : ?>
							<img id="raka-flash-img-<?php echo esc_attr( (string) $item['id'] ); ?>" src="<?php echo esc_url( $item['image_url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" loading="lazy" />
						<?php else : ?>
							<div id="raka-flash-placeholder-<?php echo esc_attr( (string) $item['id'] ); ?>" class="w-full h-full flex items-center justify-center text-neutral-700">
								<i id="raka-flash-placeholder-ico-<?php echo esc_attr( (string) $item['id'] ); ?>" class="ri-image-line text-4xl"></i>
							</div>
						<?php endif; ?>
					</a>

					<!-- Title -->
					<div id="raka-flash-info-<?php echo esc_attr( (string) $item['id'] ); ?>" class="flex-1 min-w-0 mb-4">
						<h3 id="raka-flash-title-<?php echo esc_attr( (string) $item['id'] ); ?>" class="text-sm sm:text-base font-bold font-hn text-white group-hover:text-red-400 transition-colors line-clamp-2 leading-snug">
							<a id="raka-flash-title-link-<?php echo esc_attr( (string) $item['id'] ); ?>" href="<?php echo esc_url( $item['url'] ); ?>">
								<?php echo esc_html( $item['name'] ); ?>
							</a>
						</h3>

						<!-- Pricing -->
						<div id="raka-flash-price-wrap-<?php echo esc_attr( (string) $item['id'] ); ?>" class="flex items-baseline gap-2 mt-2">
							<span id="raka-flash-sale-price-<?php echo esc_attr( (string) $item['id'] ); ?>" class="text-base sm:text-lg font-black font-hn text-white">
								<?php echo wp_kses_post( $item['sale_price'] ); ?>
							</span>
							<?php if ( ! empty( $item['regular_price'] ) ) : ?>
								<span id="raka-flash-reg-price-<?php echo esc_attr( (string) $item['id'] ); ?>" class="text-xs text-neutral-500 line-through font-mono">
									<?php echo wp_kses_post( $item['regular_price'] ); ?>
								</span>
							<?php endif; ?>
						</div>

						<!-- Stock Urgency Progress Bar -->
						<div id="raka-flash-progress-wrap-<?php echo esc_attr( (string) $item['id'] ); ?>" class="mt-3.5 space-y-1.5">
							<div id="raka-flash-progress-meta-<?php echo esc_attr( (string) $item['id'] ); ?>" class="flex items-center justify-between text-[10px] font-mono text-neutral-400">
								<span id="raka-flash-claimed-lbl-<?php echo esc_attr( (string) $item['id'] ); ?>">Claimed: <?php echo esc_html( (string) $item['claimed_pct'] ); ?>%</span>
								<span id="raka-flash-left-lbl-<?php echo esc_attr( (string) $item['id'] ); ?>" class="text-red-400 font-bold"><?php echo esc_html( (string) $item['left_count'] ); ?> left</span>
							</div>
							<div id="raka-flash-progress-track-<?php echo esc_attr( (string) $item['id'] ); ?>" class="w-full h-1.5 rounded-full bg-neutral-800 overflow-hidden">
								<div id="raka-flash-progress-bar-<?php echo esc_attr( (string) $item['id'] ); ?>" class="h-full bg-gradient-to-r from-red-600 to-red-400 rounded-full transition-all duration-500" style="width: <?php echo esc_attr( (string) $item['claimed_pct'] ); ?>%"></div>
							</div>
						</div>
					</div>

					<!-- Bottom Action CTA -->
					<div id="raka-flash-action-<?php echo esc_attr( (string) $item['id'] ); ?>" class="pt-2">
						<a id="raka-flash-cta-btn-<?php echo esc_attr( (string) $item['id'] ); ?>" href="<?php echo esc_url( $item['url'] ); ?>" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-neutral-800 hover:bg-red-600 text-neutral-200 hover:text-white text-xs font-bold uppercase tracking-wider transition-all duration-300 font-mono group/btn">
							<span id="raka-flash-cta-txt-<?php echo esc_attr( (string) $item['id'] ); ?>"><?php esc_html_e( 'Pre-Order Deal', 'raka-preorder-theme' ); ?></span>
							<i id="raka-flash-cta-arr-<?php echo esc_attr( (string) $item['id'] ); ?>" class="ri-arrow-right-line text-sm group-hover/btn:translate-x-1 transition-transform"></i>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ── SECTION: OUR PRODUCTS (CATALOG & FILTER TABS) ── -->
<?php if ( ! empty( $catalog_products ) ) : ?>
<section id="raka-our-products-section" class="relative z-20 py-16 sm:py-20 md:py-24 bg-neutral-900/60 border-t border-neutral-800/80 text-white">
	<div id="raka-our-products-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Section Header -->
		<div id="raka-our-products-header" class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-neutral-800/80">
			<div id="raka-our-products-title-wrap" class="space-y-2">
				<span id="raka-our-products-tag" class="text-xs font-mono font-bold tracking-[0.2em] text-neutral-400 uppercase">
					<?php esc_html_e( 'CURATED CATALOG', 'raka-preorder-theme' ); ?>
				</span>
				<h2 id="raka-our-products-title" class="text-2xl sm:text-3xl md:text-4xl font-black font-hn tracking-tight text-white">
					<?php esc_html_e( 'OUR PRODUCTS', 'raka-preorder-theme' ); ?>
				</h2>
				<p id="raka-our-products-desc" class="text-xs sm:text-sm text-neutral-400 font-light font-hn max-w-xl">
					<?php esc_html_e( 'Explore custom keyboards, precision ergonomic gaming mice, and studio-grade audio gear built for uncompromising performance.', 'raka-preorder-theme' ); ?>
				</p>
			</div>

			<!-- All Products Link -->
			<div id="raka-our-products-header-link" class="shrink-0">
				<a id="raka-view-all-catalog-btn" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-neutral-400 hover:text-white transition-colors group">
					<span id="raka-view-all-catalog-txt"><?php esc_html_e( 'Explore Full Catalog', 'raka-preorder-theme' ); ?></span>
					<i id="raka-view-all-catalog-arr" class="ri-arrow-right-line text-sm group-hover:translate-x-1 transition-transform"></i>
				</a>
			</div>
		</div>

		<!-- Category Filter Tabs -->
		<div id="raka-catalog-tabs-wrap" class="flex items-center gap-2 sm:gap-3 py-6 sm:py-8 overflow-x-auto no-scrollbar">
			<button
				id="raka-catalog-tab-all"
				type="button"
				onclick="window.rakaFilterCatalog('all', this)"
				class="raka-catalog-tab-btn active px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider transition-all duration-200 shrink-0 bg-white text-neutral-950 shadow-sm"
			>
				<?php esc_html_e( 'All Gear', 'raka-preorder-theme' ); ?>
			</button>
			<?php if ( ! empty( $catalog_categories ) ) : ?>
				<?php foreach ( $catalog_categories as $cat_slug => $cat_name ) : ?>
					<button
						id="raka-catalog-tab-<?php echo esc_attr( $cat_slug ); ?>"
						type="button"
						onclick="window.rakaFilterCatalog('<?php echo esc_js( $cat_slug ); ?>', this)"
						class="raka-catalog-tab-btn px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider transition-all duration-200 shrink-0 bg-neutral-800/80 hover:bg-neutral-800 text-neutral-300 hover:text-white border border-neutral-700/50"
					>
						<?php echo esc_html( $cat_name ); ?>
					</button>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<!-- Product Grid -->
		<div id="raka-catalog-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
			<?php foreach ( $catalog_products as $p ) : ?>
				<div
					id="raka-catalog-card-<?php echo esc_attr( (string) $p['id'] ); ?>"
					data-category="<?php echo esc_attr( implode( ' ', (array) $p['cat_slugs'] ) ); ?>"
					class="raka-catalog-product-card group relative flex flex-col justify-between rounded-2xl sm:rounded-3xl bg-neutral-900/80 border border-neutral-800 hover:border-neutral-700 transition-all duration-300 p-4 sm:p-5"
				>
					<!-- Top Meta -->
					<div id="raka-catalog-card-top-<?php echo esc_attr( (string) $p['id'] ); ?>" class="flex items-center justify-between gap-2 mb-3">
						<span id="raka-catalog-cat-<?php echo esc_attr( (string) $p['id'] ); ?>" class="text-[10px] font-mono uppercase tracking-widest text-neutral-400 truncate">
							<?php echo esc_html( $p['category'] ); ?>
						</span>
						<?php if ( ! empty( $p['brand'] ) ) : ?>
							<span id="raka-catalog-brand-<?php echo esc_attr( (string) $p['id'] ); ?>" class="text-[10px] font-mono uppercase tracking-wider text-neutral-400 bg-neutral-800/80 px-2 py-0.5 rounded-md border border-neutral-700/40">
								<?php echo esc_html( $p['brand'] ); ?>
							</span>
						<?php endif; ?>
					</div>

					<!-- Image -->
					<a id="raka-catalog-img-link-<?php echo esc_attr( (string) $p['id'] ); ?>" href="<?php echo esc_url( $p['url'] ); ?>" class="relative block aspect-square rounded-2xl overflow-hidden bg-black/40 border border-neutral-800 mb-4 group-hover:border-neutral-700 transition-colors">
						<?php if ( ! empty( $p['image_url'] ) ) : ?>
							<img id="raka-catalog-img-<?php echo esc_attr( (string) $p['id'] ); ?>" src="<?php echo esc_url( $p['image_url'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>" class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500" loading="lazy" />
						<?php else : ?>
							<div id="raka-catalog-ph-<?php echo esc_attr( (string) $p['id'] ); ?>" class="w-full h-full flex items-center justify-center text-neutral-700">
								<i id="raka-catalog-ph-ico-<?php echo esc_attr( (string) $p['id'] ); ?>" class="ri-image-line text-4xl"></i>
							</div>
						<?php endif; ?>
					</a>

					<!-- Info & Price -->
					<div id="raka-catalog-info-<?php echo esc_attr( (string) $p['id'] ); ?>" class="flex-1 min-w-0 mb-4">
						<h3 id="raka-catalog-title-<?php echo esc_attr( (string) $p['id'] ); ?>" class="text-sm sm:text-base font-bold font-hn text-white group-hover:text-red-400 transition-colors line-clamp-2 leading-snug">
							<a id="raka-catalog-title-link-<?php echo esc_attr( (string) $p['id'] ); ?>" href="<?php echo esc_url( $p['url'] ); ?>">
								<?php echo esc_html( $p['name'] ); ?>
							</a>
						</h3>

						<div id="raka-catalog-price-row-<?php echo esc_attr( (string) $p['id'] ); ?>" class="flex items-center justify-between gap-2 mt-2.5">
							<div id="raka-catalog-price-<?php echo esc_attr( (string) $p['id'] ); ?>" class="text-sm sm:text-base font-bold font-hn text-white">
								<?php echo wp_kses_post( $p['price_html'] ); ?>
							</div>
							<div id="raka-catalog-rating-<?php echo esc_attr( (string) $p['id'] ); ?>" class="flex items-center gap-1 text-[11px] font-mono text-amber-400">
								<i id="raka-catalog-star-<?php echo esc_attr( (string) $p['id'] ); ?>" class="ri-star-fill text-xs"></i>
								<span id="raka-catalog-rate-val-<?php echo esc_attr( (string) $p['id'] ); ?>">5.0</span>
							</div>
						</div>
					</div>

					<!-- Bottom Action -->
					<div id="raka-catalog-action-<?php echo esc_attr( (string) $p['id'] ); ?>" class="pt-2">
						<a id="raka-catalog-cta-<?php echo esc_attr( (string) $p['id'] ); ?>" href="<?php echo esc_url( $p['url'] ); ?>" class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-neutral-800/80 hover:bg-white text-neutral-300 hover:text-neutral-950 text-xs font-bold uppercase tracking-wider transition-all duration-300 font-mono group/btn">
							<span id="raka-catalog-cta-txt-<?php echo esc_attr( (string) $p['id'] ); ?>"><?php esc_html_e( 'View Product', 'raka-preorder-theme' ); ?></span>
							<i id="raka-catalog-cta-arr-<?php echo esc_attr( (string) $p['id'] ); ?>" class="ri-arrow-right-line text-sm group-hover/btn:translate-x-1 transition-transform"></i>
						</a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Bottom CTA Strip -->
		<div id="raka-catalog-bottom-cta-wrap" class="mt-12 sm:mt-16 text-center">
			<a id="raka-catalog-explore-btn" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="inline-flex items-center justify-center gap-3 py-3.5 px-8 rounded-2xl bg-neutral-800 hover:bg-neutral-700 text-white font-mono text-xs sm:text-sm font-bold uppercase tracking-widest border border-neutral-700 hover:border-neutral-600 transition-all duration-300">
				<span id="raka-catalog-explore-txt"><?php esc_html_e( 'BROWSE ALL PRE-ORDERS & ACCESSORIES', 'raka-preorder-theme' ); ?></span>
				<i id="raka-catalog-explore-ico" class="ri-arrow-right-up-line text-base text-red-400"></i>
			</a>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ── Client-side Script for Flash Sale Countdown & Catalog Filter ── -->
<script id="raka-homepage-sections-script">
(function initHomepageSections() {
	// 1. Live Countdown Timer for Flash Sale
	var hoursEl = document.getElementById('raka-timer-hours');
	var minsEl  = document.getElementById('raka-timer-mins');
	var secsEl  = document.getElementById('raka-timer-secs');

	if (hoursEl && minsEl && secsEl) {
		var totalSeconds = (8 * 3600) + (42 * 60) + 19;
		var timerInterval = setInterval(function() {
			if (totalSeconds <= 0) {
				totalSeconds = 24 * 3600;
			}
			totalSeconds--;

			var h = Math.floor(totalSeconds / 3600);
			var m = Math.floor((totalSeconds % 3600) / 60);
			var s = totalSeconds % 60;

			hoursEl.textContent = (h < 10 ? '0' : '') + h;
			minsEl.textContent  = (m < 10 ? '0' : '') + m;
			secsEl.textContent  = (s < 10 ? '0' : '') + s;
		}, 1000);
	}

	// 2. Instant Client-Side Category Filtering
	window.rakaFilterCatalog = function(categorySlug, btn) {
		var buttons = document.querySelectorAll('.raka-catalog-tab-btn');
		buttons.forEach(function(b) {
			b.classList.remove('active', 'bg-white', 'text-neutral-950', 'shadow-sm');
			b.classList.add('bg-neutral-800/80', 'text-neutral-300', 'border', 'border-neutral-700/50');
		});

		if (btn) {
			btn.classList.add('active', 'bg-white', 'text-neutral-950', 'shadow-sm');
			btn.classList.remove('bg-neutral-800/80', 'text-neutral-300', 'border', 'border-neutral-700/50');
		}

		var cards = document.querySelectorAll('.raka-catalog-product-card');
		cards.forEach(function(card) {
			if (categorySlug === 'all') {
				card.style.display = 'flex';
			} else {
				var catList = card.getAttribute('data-category') || '';
				if (catList.indexOf(categorySlug) !== -1) {
					card.style.display = 'flex';
				} else {
					card.style.display = 'none';
				}
			}
		});
	};
})();
</script>

<!-- ── POPUP MODALS: Categories, Brands, Contact ── -->


<?php
$cat_icon_map = [
	'keyboard'    => 'ri-keyboard-box-line',
	'keyboards'   => 'ri-keyboard-box-line',
	'mice'        => 'ri-mouse-line',
	'mouse'       => 'ri-mouse-line',
	'accessories' => 'ri-plug-line',
	'accessory'   => 'ri-plug-line',
	'mouse-pad'   => 'ri-rectangle-line',
	'mouse-pads'  => 'ri-rectangle-line',
	'headphone'   => 'ri-headphone-line',
	'headphones'  => 'ri-headphone-line',
	'audio'       => 'ri-volume-up-line',
	'deals'       => 'ri-percent-line',
	'sale'        => 'ri-fire-line',
	'promo'       => 'ri-gift-line',
];
?>

<!-- 1. Categories Modal -->
<div id="raka-categories-modal" class="raka-popup-modal fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 md:p-10 bg-black/60 dark:bg-black/85 backdrop-blur-2xl transition-all duration-300 opacity-0 pointer-events-none">
	<div id="raka-categories-modal-backdrop" class="absolute inset-0" onclick="window.rakaCloseModal('categories')"></div>
	<div id="raka-categories-modal-dialog" class="raka-modal-dialog relative z-10 w-full max-w-5xl max-h-[85vh] flex flex-col rounded-3xl bg-white/95 dark:bg-neutral-900/95 border border-black/10 dark:border-white/15 shadow-2xl text-neutral-900 dark:text-white overflow-hidden transform scale-95 transition-all duration-300">
		<!-- Header -->
		<div id="raka-categories-modal-header" class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/10 dark:border-white/10 shrink-0">
			<div id="raka-categories-header-left" class="flex items-center gap-3">
				<div id="raka-categories-icon-badge" class="w-10 h-10 rounded-2xl bg-red-500/10 dark:bg-red-500/15 text-red-600 dark:text-red-400 border border-red-500/20 dark:border-red-500/25 flex items-center justify-center">
					<i id="raka-cat-modal-ico" class="ri-grid-fill text-lg"></i>
				</div>
				<div>
					<h3 id="raka-categories-modal-title" class="text-lg sm:text-xl font-bold font-hn leading-tight text-neutral-950 dark:text-white"><?php esc_html_e( 'Product Categories', 'raka-preorder-theme' ); ?></h3>
					<p id="raka-categories-modal-subtitle" class="text-xs text-neutral-500 dark:text-white/50 font-hn mt-0.5"><?php esc_html_e( 'Discover all available gadget categories and collections', 'raka-preorder-theme' ); ?></p>
				</div>
			</div>
			<button id="raka-categories-modal-close" type="button" onclick="window.rakaCloseModal('categories')" class="w-9 h-9 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/20 text-neutral-800 dark:text-white flex items-center justify-center transition-colors cursor-pointer" aria-label="<?php esc_attr_e( 'Close', 'raka-preorder-theme' ); ?>">
				<i id="raka-categories-close-icon" class="ri-close-line text-xl"></i>
			</button>
		</div>
		<!-- Grid Content: Uniform Category Icon Cards -->
		<div id="raka-categories-modal-body" class="overflow-y-auto p-6 sm:p-8">
			<?php if ( ! empty( $featured_categories ) ) : ?>
				<div id="raka-categories-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 sm:gap-5">
					<?php
					foreach ( $featured_categories as $cat ) :
						$cat_slug  = sanitize_title( $cat['slug'] );
						$icon_cls  = ! empty( $cat['icon_class'] ) ? $cat['icon_class'] : ( isset( $cat_icon_map[ $cat_slug ] ) ? $cat_icon_map[ $cat_slug ] : 'ri-apps-2-line' );
						$media_src = ! empty( $cat['media_icon'] ) ? $cat['media_icon'] : '';
						?>
						<a
							id="raka-cat-item-<?php echo esc_attr( $cat['id'] ); ?>"
							href="<?php echo esc_url( $cat['url'] ); ?>"
							class="group relative flex flex-col justify-between aspect-square sm:aspect-[4/3.5] rounded-3xl overflow-hidden bg-neutral-50 dark:bg-white/5 border border-black/10 dark:border-white/10 hover:border-red-500/60 dark:hover:border-red-400/60 transition-all duration-300 p-5 sm:p-6 cursor-pointer"
						>
							<!-- Top Icon -->
							<div id="raka-cat-top-bar-<?php echo esc_attr( $cat['id'] ); ?>" class="relative z-10 flex items-center justify-start w-full">
								<div id="raka-cat-ico-wrap-<?php echo esc_attr( $cat['id'] ); ?>" class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-red-500/10 dark:bg-red-500/15 text-red-600 dark:text-red-400 border border-red-500/20 dark:border-red-500/20 flex items-center justify-center group-hover:scale-110 group-hover:bg-red-600 group-hover:text-white dark:group-hover:bg-red-500 dark:group-hover:text-white transition-all duration-300">
									<?php if ( ! empty( $media_src ) ) : ?>
										<img id="raka-cat-media-<?php echo esc_attr( $cat['id'] ); ?>" src="<?php echo esc_url( $media_src ); ?>" alt="<?php echo esc_attr( $cat['name'] ); ?>" class="w-6 h-6 sm:w-7 sm:h-7 object-contain" />
									<?php else : ?>
										<i id="raka-cat-ico-<?php echo esc_attr( $cat['id'] ); ?>" class="<?php echo esc_attr( $icon_cls ); ?> text-2xl sm:text-3xl"></i>
									<?php endif; ?>
								</div>
							</div>

							<!-- Bottom Title & Arrow -->
							<div id="raka-cat-meta-<?php echo esc_attr( $cat['id'] ); ?>" class="relative z-10 flex items-end justify-between gap-2 mt-4">
								<h4 id="raka-cat-title-<?php echo esc_attr( $cat['id'] ); ?>" class="text-base sm:text-lg font-bold text-neutral-900 dark:text-white font-hn group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors leading-snug">
									<?php echo esc_html( $cat['name'] ); ?>
								</h4>
								<i id="raka-cat-arr-<?php echo esc_attr( $cat['id'] ); ?>" class="ri-arrow-right-line text-lg text-neutral-400 dark:text-white/50 group-hover:text-red-600 dark:group-hover:text-red-400 group-hover:translate-x-1 transition-all shrink-0"></i>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div id="raka-categories-empty" class="text-center py-12 text-neutral-500 dark:text-white/50 text-sm font-hn">
					<?php esc_html_e( 'No product categories found.', 'raka-preorder-theme' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- 2. Brands Modal -->
<div id="raka-brands-modal" class="raka-popup-modal fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 md:p-10 bg-black/60 dark:bg-black/85 backdrop-blur-2xl transition-all duration-300 opacity-0 pointer-events-none">
	<div id="raka-brands-modal-backdrop" class="absolute inset-0" onclick="window.rakaCloseModal('brands')"></div>
	<div id="raka-brands-modal-dialog" class="raka-modal-dialog relative z-10 w-full max-w-5xl max-h-[85vh] flex flex-col rounded-3xl bg-white/95 dark:bg-neutral-900/95 border border-black/10 dark:border-white/15 shadow-2xl text-neutral-900 dark:text-white overflow-hidden transform scale-95 transition-all duration-300">
		<!-- Header -->
		<div id="raka-brands-modal-header" class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/10 dark:border-white/10 shrink-0">
			<div id="raka-brands-header-left" class="flex items-center gap-3">
				<div id="raka-brands-icon-badge" class="w-10 h-10 rounded-2xl bg-amber-500/15 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/25 dark:border-amber-500/30 flex items-center justify-center">
					<i id="raka-brand-modal-ico" class="ri-fire-fill text-lg"></i>
				</div>
				<div>
					<h3 id="raka-brands-modal-title" class="text-lg sm:text-xl font-bold font-hn leading-tight text-neutral-950 dark:text-white"><?php esc_html_e( 'Official Brands', 'raka-preorder-theme' ); ?></h3>
					<p id="raka-brands-modal-subtitle" class="text-xs text-neutral-500 dark:text-white/50 font-hn mt-0.5"><?php esc_html_e( 'Authorized and premium gadget partners', 'raka-preorder-theme' ); ?></p>
				</div>
			</div>
			<button id="raka-brands-modal-close" type="button" onclick="window.rakaCloseModal('brands')" class="w-9 h-9 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/20 text-neutral-800 dark:text-white flex items-center justify-center transition-colors cursor-pointer" aria-label="<?php esc_attr_e( 'Close', 'raka-preorder-theme' ); ?>">
				<i id="raka-brands-close-icon" class="ri-close-line text-xl"></i>
			</button>
		</div>
		<!-- Grid Content -->
		<div id="raka-brands-modal-body" class="overflow-y-auto p-6 sm:p-8">
			<?php if ( ! empty( $featured_brands ) ) : ?>
				<div id="raka-brands-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 sm:gap-5">
					<?php foreach ( $featured_brands as $brand ) : ?>
						<a
							id="raka-brand-item-<?php echo esc_attr( $brand['id'] ); ?>"
							href="<?php echo esc_url( $brand['url'] ); ?>"
							class="group relative flex flex-col items-center justify-center text-center rounded-3xl bg-neutral-50 dark:bg-white/5 border border-black/10 hover:border-amber-500/50 dark:border-white/10 dark:hover:border-amber-500/50 transition-all duration-300 p-6 sm:p-7"
						>
							<?php if ( ! empty( $brand['image_url'] ) ) : ?>
								<div id="raka-brand-img-wrap-<?php echo esc_attr( $brand['id'] ); ?>" class="w-16 h-16 sm:w-20 sm:h-20 mb-3 flex items-center justify-center overflow-hidden rounded-2xl bg-white dark:bg-white/10 border border-black/5 dark:border-white/10 p-2.5 group-hover:scale-105 transition-transform duration-300">
									<img
										id="raka-brand-img-<?php echo esc_attr( $brand['id'] ); ?>"
										src="<?php echo esc_url( $brand['image_url'] ); ?>"
										alt="<?php echo esc_attr( $brand['name'] ); ?>"
										class="max-w-full max-h-full object-contain"
									/>
								</div>
							<?php else : ?>
								<div id="raka-brand-icon-wrap-<?php echo esc_attr( $brand['id'] ); ?>" class="w-16 h-16 sm:w-20 sm:h-20 mb-3 flex items-center justify-center rounded-2xl bg-amber-500/15 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/25 dark:border-amber-500/30 group-hover:scale-105 transition-transform duration-300">
									<i id="raka-brand-crown-<?php echo esc_attr( $brand['id'] ); ?>" class="ri-vip-crown-2-line text-2xl sm:text-3xl"></i>
								</div>
							<?php endif; ?>
							<h4 id="raka-brand-name-<?php echo esc_attr( $brand['id'] ); ?>" class="text-sm sm:text-base font-bold text-neutral-900 dark:text-white font-hn group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
								<?php echo esc_html( $brand['name'] ); ?>
							</h4>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div id="raka-brands-empty" class="text-center py-12 text-neutral-500 dark:text-white/50 text-sm font-hn">
					<?php esc_html_e( 'No brands registered yet.', 'raka-preorder-theme' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- 3. Contact & Store Modal -->
<div id="raka-contact-modal" class="raka-popup-modal fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 md:p-10 bg-black/60 dark:bg-black/85 backdrop-blur-2xl transition-all duration-300 opacity-0 pointer-events-none">
	<div id="raka-contact-modal-backdrop" class="absolute inset-0" onclick="window.rakaCloseModal('contact')"></div>
	<div id="raka-contact-modal-dialog" class="raka-modal-dialog relative z-10 w-full max-w-3xl max-h-[88vh] flex flex-col rounded-3xl bg-white/95 dark:bg-neutral-900/95 border border-black/10 dark:border-white/15 shadow-2xl text-neutral-900 dark:text-white overflow-hidden transform scale-95 transition-all duration-300">
		<!-- Header -->
		<div id="raka-contact-modal-header" class="flex items-center justify-between px-6 sm:px-8 py-5 border-b border-black/10 dark:border-white/10 shrink-0">
			<div id="raka-contact-header-left" class="flex items-center gap-3">
				<div id="raka-contact-icon-badge" class="w-10 h-10 rounded-2xl bg-emerald-500/15 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 dark:border-emerald-500/30 flex items-center justify-center">
					<i id="raka-contact-modal-ico" class="ri-customer-service-2-fill text-lg"></i>
				</div>
				<div>
					<h3 id="raka-contact-modal-title" class="text-lg sm:text-xl font-bold font-hn leading-tight text-neutral-950 dark:text-white"><?php echo esc_html( ! empty( $contact_options['contact_title'] ) ? $contact_options['contact_title'] : __( 'Contact & Store Info', 'raka-preorder-theme' ) ); ?></h3>
					<p id="raka-contact-modal-subtitle" class="text-xs text-neutral-500 dark:text-white/50 font-hn mt-0.5"><?php echo esc_html( ! empty( $contact_options['contact_subtitle'] ) ? $contact_options['contact_subtitle'] : __( 'Get in touch with our team for orders and consultations', 'raka-preorder-theme' ) ); ?></p>
				</div>
			</div>
			<button id="raka-contact-modal-close" type="button" onclick="window.rakaCloseModal('contact')" class="w-9 h-9 rounded-full bg-black/5 hover:bg-black/10 dark:bg-white/10 dark:hover:bg-white/20 text-neutral-800 dark:text-white flex items-center justify-center transition-colors cursor-pointer" aria-label="<?php esc_attr_e( 'Close', 'raka-preorder-theme' ); ?>">
				<i id="raka-contact-close-icon" class="ri-close-line text-xl"></i>
			</button>
		</div>
		<!-- Contact Content Body -->
		<div id="raka-contact-modal-body" class="overflow-y-auto p-6 sm:p-8 space-y-6">

			<!-- Direct Channels Grid -->
			<div id="raka-contact-channels-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-4">

				<!-- LINE Official Card -->
				<?php if ( ! empty( $contact_options['contact_line_id'] ) || ! empty( $contact_options['contact_line_url'] ) ) : ?>
					<a
						id="raka-contact-line-card"
						href="<?php echo esc_url( ! empty( $contact_options['contact_line_url'] ) ? $contact_options['contact_line_url'] : 'https://line.me/ti/p/' . rawurlencode( $contact_options['contact_line_id'] ) ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						class="group flex items-center gap-4 p-4 sm:p-5 rounded-2xl bg-[#06C755]/10 hover:bg-[#06C755]/20 border border-[#06C755]/30 hover:border-[#06C755] transition-all duration-300"
					>
						<div id="raka-contact-line-icon-wrap" class="w-12 h-12 rounded-xl bg-[#06C755] text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
							<i id="raka-contact-line-ico" class="ri-line-fill text-2xl"></i>
						</div>
						<div id="raka-contact-line-details" class="flex-1 min-w-0">
							<div id="raka-contact-line-tag" class="text-[10px] font-bold tracking-wider uppercase text-[#06C755] font-mono"><?php esc_html_e( 'LINE Official', 'raka-preorder-theme' ); ?></div>
							<div id="raka-contact-line-id" class="text-sm font-bold text-neutral-900 dark:text-white truncate font-hn"><?php echo esc_html( $contact_options['contact_line_id'] ); ?></div>
							<div id="raka-contact-line-sub" class="text-[11px] text-neutral-500 dark:text-white/50 font-hn"><?php esc_html_e( 'Chat with us 24/7', 'raka-preorder-theme' ); ?></div>
						</div>
						<i id="raka-contact-line-arrow" class="ri-arrow-right-up-line text-neutral-400 dark:text-white/50 group-hover:text-[#06C755] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
					</a>
				<?php endif; ?>

				<!-- Facebook Card -->
				<?php if ( ! empty( $contact_options['contact_facebook_url'] ) ) : ?>
					<a
						id="raka-contact-facebook-card"
						href="<?php echo esc_url( $contact_options['contact_facebook_url'] ); ?>"
						target="_blank"
						rel="noopener noreferrer"
						class="group flex items-center gap-4 p-4 sm:p-5 rounded-2xl bg-[#1877F2]/10 hover:bg-[#1877F2]/20 border border-[#1877F2]/30 hover:border-[#1877F2] transition-all duration-300"
					>
						<div id="raka-contact-fb-icon-wrap" class="w-12 h-12 rounded-xl bg-[#1877F2] text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
							<i id="raka-contact-fb-ico" class="ri-facebook-circle-fill text-2xl"></i>
						</div>
						<div id="raka-contact-fb-details" class="flex-1 min-w-0">
							<div id="raka-contact-fb-tag" class="text-[10px] font-bold tracking-wider uppercase text-[#1877F2] font-mono"><?php esc_html_e( 'Facebook Page', 'raka-preorder-theme' ); ?></div>
							<div id="raka-contact-fb-title" class="text-sm font-bold text-neutral-900 dark:text-white truncate font-hn"><?php esc_html_e( 'OBSIDIAN Atelier', 'raka-preorder-theme' ); ?></div>
							<div id="raka-contact-fb-sub" class="text-[11px] text-neutral-500 dark:text-white/50 font-hn"><?php esc_html_e( 'Follow news & updates', 'raka-preorder-theme' ); ?></div>
						</div>
						<i id="raka-contact-fb-arrow" class="ri-arrow-right-up-line text-neutral-400 dark:text-white/50 group-hover:text-[#1877F2] group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all"></i>
					</a>
				<?php endif; ?>

				<!-- Phone Card -->
				<?php if ( ! empty( $contact_options['contact_phone'] ) ) : ?>
					<a
						id="raka-contact-phone-card"
						href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $contact_options['contact_phone'] ) ); ?>"
						class="group flex items-center gap-4 p-4 sm:p-5 rounded-2xl bg-neutral-100 hover:bg-neutral-200 dark:bg-white/5 dark:hover:bg-white/10 border border-black/10 dark:border-white/10 hover:border-black/30 dark:hover:border-white/30 transition-all duration-300"
					>
						<div id="raka-contact-phone-icon-wrap" class="w-12 h-12 rounded-xl bg-black/10 dark:bg-white/15 text-neutral-900 dark:text-white flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
							<i id="raka-contact-phone-ico" class="ri-phone-fill text-2xl"></i>
						</div>
						<div id="raka-contact-phone-details" class="flex-1 min-w-0">
							<div id="raka-contact-phone-tag" class="text-[10px] font-bold tracking-wider uppercase text-neutral-500 dark:text-white/50 font-mono"><?php esc_html_e( 'Direct Call', 'raka-preorder-theme' ); ?></div>
							<div id="raka-contact-phone-num" class="text-sm font-bold text-neutral-900 dark:text-white truncate font-hn"><?php echo esc_html( $contact_options['contact_phone'] ); ?></div>
							<div id="raka-contact-phone-sub" class="text-[11px] text-neutral-500 dark:text-white/50 font-hn"><?php esc_html_e( 'Customer hotline', 'raka-preorder-theme' ); ?></div>
						</div>
						<i id="raka-contact-phone-arrow" class="ri-phone-line text-neutral-400 dark:text-white/50 group-hover:text-neutral-900 dark:group-hover:text-white transition-all"></i>
					</a>
				<?php endif; ?>

				<!-- Email Card -->
				<?php if ( ! empty( $contact_options['contact_email'] ) ) : ?>
					<a
						id="raka-contact-email-card"
						href="mailto:<?php echo esc_attr( $contact_options['contact_email'] ); ?>"
						class="group flex items-center gap-4 p-4 sm:p-5 rounded-2xl bg-neutral-100 hover:bg-neutral-200 dark:bg-white/5 dark:hover:bg-white/10 border border-black/10 dark:border-white/10 hover:border-black/30 dark:hover:border-white/30 transition-all duration-300"
					>
						<div id="raka-contact-email-icon-wrap" class="w-12 h-12 rounded-xl bg-black/10 dark:bg-white/15 text-neutral-900 dark:text-white flex items-center justify-center shrink-0 shadow-sm group-hover:scale-105 transition-transform">
							<i id="raka-contact-email-ico" class="ri-mail-fill text-2xl"></i>
						</div>
						<div id="raka-contact-email-details" class="flex-1 min-w-0">
							<div id="raka-contact-email-tag" class="text-[10px] font-bold tracking-wider uppercase text-neutral-500 dark:text-white/50 font-mono"><?php esc_html_e( 'Email Support', 'raka-preorder-theme' ); ?></div>
							<div id="raka-contact-email-val" class="text-sm font-bold text-neutral-900 dark:text-white truncate font-hn"><?php echo esc_html( $contact_options['contact_email'] ); ?></div>
							<div id="raka-contact-email-sub" class="text-[11px] text-neutral-500 dark:text-white/50 font-hn"><?php esc_html_e( 'Inquiries & orders', 'raka-preorder-theme' ); ?></div>
						</div>
						<i id="raka-contact-email-arrow" class="ri-mail-line text-neutral-400 dark:text-white/50 group-hover:text-neutral-900 dark:group-hover:text-white transition-all"></i>
					</a>
				<?php endif; ?>
			</div>

			<!-- Studio Hours & Address Box -->
			<div id="raka-contact-details-box" class="p-5 sm:p-6 rounded-2xl bg-neutral-100/90 dark:bg-white/5 border border-black/10 dark:border-white/10 space-y-4">
				<?php if ( ! empty( $contact_options['contact_hours'] ) ) : ?>
					<div id="raka-contact-hours-row" class="flex items-start gap-3.5">
						<div id="raka-contact-hours-icon" class="w-8 h-8 rounded-lg bg-black/5 dark:bg-white/10 text-neutral-700 dark:text-white/80 flex items-center justify-center shrink-0 mt-0.5">
							<i id="raka-hours-ico" class="ri-time-line text-base"></i>
						</div>
						<div>
							<div id="raka-contact-hours-label" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-white/50 font-mono"><?php esc_html_e( 'Service Hours', 'raka-preorder-theme' ); ?></div>
							<div id="raka-contact-hours-val" class="text-sm text-neutral-900 dark:text-white/90 font-hn mt-0.5"><?php echo esc_html( $contact_options['contact_hours'] ); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $contact_options['contact_address'] ) ) : ?>
					<div id="raka-contact-address-row" class="flex items-start gap-3.5 pt-3 border-t border-black/10 dark:border-white/10">
						<div id="raka-contact-address-icon" class="w-8 h-8 rounded-lg bg-black/5 dark:bg-white/10 text-neutral-700 dark:text-white/80 flex items-center justify-center shrink-0 mt-0.5">
							<i id="raka-address-ico" class="ri-map-pin-2-line text-base"></i>
						</div>
						<div>
							<div id="raka-contact-address-label" class="text-[10px] font-bold uppercase tracking-widest text-neutral-500 dark:text-white/50 font-mono"><?php esc_html_e( 'Store Location', 'raka-preorder-theme' ); ?></div>
							<div id="raka-contact-address-val" class="text-sm text-neutral-900 dark:text-white/90 font-hn mt-0.5"><?php echo esc_html( $contact_options['contact_address'] ); ?></div>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $contact_options['contact_note'] ) ) : ?>
					<div id="raka-contact-note-row" class="pt-3 border-t border-black/10 dark:border-white/10">
						<div id="raka-contact-note-badge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-widest bg-amber-500/15 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-500/25 dark:border-amber-500/30 mb-1.5 font-mono">
							<i id="raka-note-ico" class="ri-information-line"></i>
							<span><?php esc_html_e( 'Notice', 'raka-preorder-theme' ); ?></span>
						</div>
						<p id="raka-contact-note-val" class="text-xs text-neutral-700 dark:text-white/70 font-hn leading-relaxed">
							<?php echo nl2br( esc_html( $contact_options['contact_note'] ) ); ?>
						</p>
					</div>
				<?php endif; ?>
			</div>

		</div>
	</div>
</div>

<script id="raka-modal-controller-script">
	(function initRakaModalController() {
		window.rakaOpenModal = function(type) {
			var modal = document.getElementById('raka-' + type + '-modal');
			if (!modal) return;
			modal.classList.remove('opacity-0', 'pointer-events-none');
			modal.classList.add('opacity-100', 'pointer-events-auto');
			var dialog = modal.querySelector('.raka-modal-dialog');
			if (dialog) {
				dialog.classList.remove('scale-95');
				dialog.classList.add('scale-100');
			}
			document.body.style.overflow = 'hidden';
		};

		window.rakaCloseModal = function(type) {
			var modals = type ? [document.getElementById('raka-' + type + '-modal')] : document.querySelectorAll('.raka-popup-modal');
			modals.forEach(function(m) {
				if (!m) return;
				m.classList.remove('opacity-100', 'pointer-events-auto');
				m.classList.add('opacity-0', 'pointer-events-none');
				var dialog = m.querySelector('.raka-modal-dialog');
				if (dialog) {
					dialog.classList.remove('scale-100');
					dialog.classList.add('scale-95');
				}
			});
			document.body.style.overflow = '';
		};

		document.addEventListener('keydown', function(e) {
			if (e.key === 'Escape') {
				window.rakaCloseModal();
			}
		});
	})();
</script>

<!-- ── ClosingSection Component ── -->
<section class="relative min-h-screen bg-black dark:bg-black light:bg-neutral-950 flex flex-col justify-between overflow-hidden theme-transition">
	<!-- Top rule -->
	<div class="w-full px-6 md:px-12 lg:px-16 pt-16 md:pt-20">
		<div class="h-px bg-white/10 w-full"></div>
	</div>

	<!-- Main -->
	<div class="flex-1 flex flex-col justify-center px-6 md:px-12 lg:px-16 py-16">
		<div class="text-[10px] tracking-[0.3em] uppercase text-white/30 mb-8 font-hn">Convictions</div>
		<h2 class="text-[8vw] sm:text-[7vw] md:text-[5vw] lg:text-[4vw] font-light leading-[1.15] tracking-[-0.02em] text-white/90 max-w-5xl font-hn">
			We don't merely mark skin. <br class="hidden sm:block"/>
			<span class="text-white/40"> We distill stories into permanence&mdash;</span> <br class="hidden sm:block"/>
			<span> one stroke at a time.</span>
		</h2>
		<p class="mt-10 text-xs md:text-sm leading-[1.8] text-white/40 font-light max-w-md font-hn">
			Every work begins as a dialogue and arrives as a partnership. Your history becomes our medium. No shortcuts, no repetition—only purpose translated into ink.
		</p>
	</div>

	<!-- Footer -->
	<div class="px-6 md:px-12 lg:px-16 pb-10">
		<div class="h-px bg-white/10 w-full mb-8"></div>
		<div class="flex flex-col md:flex-row items-start md:items-end justify-between gap-6">
			<div>
				<div class="text-[10px] tracking-[0.3em] uppercase text-white/30 mb-2 font-hn">Atelier</div>
				<div class="text-sm text-white/60 font-light leading-relaxed font-hn">
					Brooklyn, New York<br/>
					By private session only
				</div>
			</div>
			<div class="flex flex-col items-start md:items-end gap-3">
				<div class="text-[10px] tracking-[0.3em] uppercase text-white/30 font-hn">Start your journey</div>
				<a href="#" class="group inline-flex items-center gap-3 text-white/80 hover:text-white transition-colors duration-300">
					<span class="text-sm font-light tracking-wide font-hn">Schedule a consultation</span>
					<span class="w-8 h-px bg-white/40 group-hover:w-12 group-hover:bg-white transition-all duration-300"></span>
				</a>
			</div>
			<div class="text-right mt-4 md:mt-0 text-[10px] tracking-[0.2em] uppercase text-white/20 font-hn">
				© OBSIDIAN MMXXV
			</div>
		</div>
	</div>
</section>

<!-- ── Ultra-Smooth Scroll Scrubbing & Frame-Bank Engine ── -->
<script id="obsidian-hero-video-engine">
	(function initScrollVideoHero() {
		const section = document.getElementById('scroll-video-hero-section');
		const video = document.getElementById('hero-video');
		const canvas = document.getElementById('hero-canvas');
		if (!section || !video || !canvas) return;

		const ctx = canvas.getContext('2d', { alpha: false, desynchronized: true });
		const overlayPng = document.getElementById('hero-overlay-png-wrap');
		const heroSec1   = document.getElementById('hero-section-1');
		const subSecs    = Array.from(document.querySelectorAll('.hero-card-slide'));
		const totalCards = subSecs.length;

		const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

		// Fast & Responsive Smoothing Constants
		const LERP_TAU = 28;
		const SNAP = 0.001;
		const LRU_MAX = 36;
		const LEAD = 24;
		const WATCHDOG_MS = 45000;
		const VIDEO_URL = video.src;

		let start = 0;
		let end = 1;
		let progress = 0;
		let targetTime = 0;
		let currentTime = 0;
		let lastTime = performance.now();
		let isSeekingVideo = false;
		let pendingVideoSeek = null;

		function measure() {
			const rect = section.getBoundingClientRect();
			start = window.scrollY + rect.top;
			end = start + section.offsetHeight - window.innerHeight;
		}
		measure();
		window.addEventListener('resize', measure, { passive: true });

		const revealVideo = () => {
			video.style.opacity = '1';
		};
		video.addEventListener('loadeddata', revealVideo);
		video.addEventListener('seeked', () => {
			isSeekingVideo = false;
			revealVideo();
			if (pendingVideoSeek !== null) {
				const t = pendingVideoSeek;
				pendingVideoSeek = null;
				performVideoSeek(t);
			}
		});
		if (video.readyState >= 2) revealVideo();

		function performVideoSeek(time) {
			if (isSeekingVideo) {
				pendingVideoSeek = time;
				return;
			}
			if (Math.abs(video.currentTime - time) > 0.008) {
				isSeekingVideo = true;
				try {
					if (typeof video.fastSeek === 'function') {
						video.fastSeek(time);
					} else {
						video.currentTime = time;
					}
				} catch (e) {
					isSeekingVideo = false;
				}
			}
		}

		function applyPositions(prog) {
			// 1. Hero Main Title & Overlay PNG (Intro Section)
			const introDuration = totalCards > 0 ? 0.20 : 0.40;

			if (heroSec1) {
				// Title stays solid for the first 8% scroll, then fades out smoothly
				let heroOpacity = 1;
				if (prog > 0.08) {
					const t = Math.min(1, (prog - 0.08) / (introDuration - 0.08));
					heroOpacity = Math.max(0, 1 - t);
				}
				heroSec1.style.opacity = heroOpacity.toFixed(3);
				heroSec1.style.transform = `translate3d(0, ${(prog * -35).toFixed(1)}px, 0)`;
				heroSec1.style.visibility = heroOpacity > 0.01 ? 'visible' : 'hidden';
			}

			if (overlayPng) {
				let pngOpacity = 1;
				if (prog > 0.10) {
					const t = Math.min(1, (prog - 0.10) / (introDuration - 0.10));
					pngOpacity = Math.max(0, 1 - t);
				}
				overlayPng.style.opacity = pngOpacity.toFixed(3);
				overlayPng.style.visibility = pngOpacity > 0.01 ? 'visible' : 'hidden';
			}

			// 2. Quad-Card Scrubbing (Harmoniously synced with video playback)
			if (totalCards > 0) {
				const cardProgressStart = 0.16; // Seamless handoff right after title fades
				const cardProgressRange = Math.max(0.1, 0.94 - cardProgressStart);
				const cardSlotSize = cardProgressRange / totalCards;

				subSecs.forEach((sec, idx) => {
					if (!sec) return;

					const mySlotStart = cardProgressStart + (idx * cardSlotSize);
					const mySlotEnd   = mySlotStart + cardSlotSize;

					// If scroll progress is outside this card's exclusive slot, hide it immediately
					if (prog < mySlotStart || prog >= mySlotEnd) {
						sec.style.opacity = '0';
						sec.style.visibility = 'hidden';
						sec.style.pointerEvents = 'none';
						sec.style.transform = 'translate3d(0, 16px, 0)';
						return;
					}

					// Local progress within this quad's dedicated slot [0.0 -> 1.0]
					const local = (prog - mySlotStart) / cardSlotSize;

					let opacity = 0;
					let translateY = 0;

					if (local < 0.14) {
						// Smooth enter phase (0.0 -> 0.14)
						const enterT = local / 0.14;
						// Quadratic ease-out for ultra smooth entry
						const ease = enterT * (2 - enterT);
						opacity = ease;
						translateY = (1 - ease) * 16;
					} else if (local > 0.72) {
						// Smooth exit phase (0.72 -> 0.88) + inter-quad rest buffer (0.88 -> 1.0)
						if (local < 0.88) {
							const exitT = (local - 0.72) / 0.16;
							const ease = exitT * exitT; // Quadratic ease-in for gentle lift off
							opacity = Math.max(0, 1 - ease);
							translateY = ease * -16;
						} else {
							// Clean buffer rest before next slide enters
							opacity = 0;
							translateY = -16;
						}
					} else {
						// Generous solid hold phase (0.14 -> 0.72)
						opacity = 1;
						translateY = 0;
					}

					sec.style.visibility = opacity > 0.005 ? 'visible' : 'hidden';
					sec.style.opacity = opacity.toFixed(3);
					sec.style.transform = `translate3d(0, ${translateY.toFixed(1)}px, 0)`;
					sec.style.pointerEvents = opacity > 0.4 ? 'auto' : 'none';
				});
			}
		}

		window.addEventListener('scroll', () => {
			const scrollY = window.scrollY;
			progress = Math.max(0, Math.min(1, (scrollY - start) / (end - start)));
			if (video.duration) {
				targetTime = progress * video.duration;
			}
		}, { passive: true });

		const bank = {
			frames: [],
			lru: new Map(),
			drawnIndex: -1,
			ready: false,
			reverted: false,
			painted: false
		};

		function binarySearchNearest(tsMicros) {
			const f = bank.frames;
			let lo = 0, hi = f.length - 1;
			if (tsMicros <= f[0].ts) return 0;
			if (tsMicros >= f[hi].ts) return hi;
			while (lo <= hi) {
				const mid = (lo + hi) >> 1;
				if (f[mid].ts === tsMicros) return mid;
				if (f[mid].ts < tsMicros) lo = mid + 1;
				else hi = mid - 1;
			}
			return (lo < f.length && Math.abs(f[lo].ts - tsMicros) < Math.abs(f[hi].ts - tsMicros)) ? lo : hi;
		}

		function warmLRU(targetIdx) {
			const minI = Math.max(0, targetIdx - 2);
			const maxI = Math.min(bank.frames.length - 1, targetIdx + 4);
			for (let i = minI; i <= maxI; i++) {
				if (!bank.lru.has(i)) {
					bank.lru.set(i, null);
					const blob = bank.frames[i].blob;
					createImageBitmap(blob).then(bm => {
						if (bank.lru.has(i)) bank.lru.set(i, bm);
						else bm.close();
					}).catch(() => {});
				}
			}
			while (bank.lru.size > LRU_MAX) {
				const oldestKey = bank.lru.keys().next().value;
				const item = bank.lru.get(oldestKey);
				if (item && typeof item.close === 'function') item.close();
				bank.lru.delete(oldestKey);
			}
		}

		function drawCanvasFrame(source) {
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.drawImage(source, 0, 0, canvas.width, canvas.height);
		}

		function rAF(now) {
			const dt = Math.min(0.1, (now - lastTime) / 1000);
			lastTime = now;

			if (prefersReducedMotion) {
				currentTime = 0;
			} else {
				const smoothing = 1 - Math.exp(-dt * LERP_TAU);
				currentTime += (targetTime - currentTime) * smoothing;
				if (Math.abs(targetTime - currentTime) < SNAP) {
					currentTime = targetTime;
				}
			}

			// Synchronize card transforms and opacity with the exact smoothed video clock
			const animProgress = (video.duration && video.duration > 0)
				? (currentTime / video.duration)
				: progress;
			applyPositions(animProgress);

			if (bank.ready && bank.frames.length > 0) {
				const targetMicros = currentTime * 1e6;
				const idx = binarySearchNearest(targetMicros);
				const bm = bank.lru.get(idx);
				if (bm && idx !== bank.drawnIndex) {
					drawCanvasFrame(bm);
					bank.drawnIndex = idx;
					if (!bank.painted) {
						bank.painted = true;
						canvas.style.opacity = '1';
					}
				}
				warmLRU(idx);
			} else {
				performVideoSeek(currentTime);
			}

			requestAnimationFrame(rAF);
		}

		video.addEventListener('loadedmetadata', () => {
			requestAnimationFrame(rAF);
			startFrameBankPipeline();
		});
		if (video.readyState >= 1) {
			requestAnimationFrame(rAF);
			startFrameBankPipeline();
		}

		function loadScript(src) {
			return new Promise((resolve, reject) => {
				if (window.MP4Box) return resolve(window.MP4Box);
				const script = document.createElement('script');
				script.src = src;
				script.onload = () => resolve(window.MP4Box);
				script.onerror = reject;
				document.head.appendChild(script);
			});
		}

		let pipelineStarted = false;
		function startFrameBankPipeline() {
			if (pipelineStarted) return;
			if (prefersReducedMotion || typeof window.VideoDecoder === 'undefined') return;
			pipelineStarted = true;

			const onReadyOrLoaded = () => {
				buildFrameBank('prefer-hardware').catch(() => {
					buildFrameBank('prefer-software').catch(() => {
						bank.reverted = true;
						bank.ready = false;
						canvas.style.opacity = '0';
					});
				});
			};

			if (document.readyState === 'complete') onReadyOrLoaded();
			else window.addEventListener('load', onReadyOrLoaded);
		}

		async function buildFrameBank(accel) {
			const MP4Box = await loadScript('https://cdn.jsdelivr.net/npm/mp4box@0.5.2/dist/mp4box.all.min.js');
			const mp4boxfile = MP4Box.createFile();

			let resolveTrack, rejectTrack;
			const trackPromise = new Promise((res, rej) => { resolveTrack = res; rejectTrack = rej; });

			let samples = [];
			let completedDecodeCount = 0;
			let decodeChain = Promise.resolve();
			const offCanvas = document.createElement('canvas');
			const offCtx = offCanvas.getContext('2d', { alpha: false });

			const watchdog = setTimeout(() => {
				rejectTrack(new Error('Watchdog timeout'));
			}, WATCHDOG_MS);

			let decoder = null;

			mp4boxfile.onReady = (info) => {
				const track = info.videoTracks[0];
				if (!track) return rejectTrack(new Error('No video tracks'));

				offCanvas.width = track.video.width || 1280;
				offCanvas.height = track.video.height || 720;

				let description = null;
				const trak = mp4boxfile.getTrackById(track.id);
				if (trak && trak.mdia && trak.mdia.minf && trak.mdia.minf.stbl && trak.mdia.minf.stbl.stsd) {
					const entry = trak.mdia.minf.stbl.stsd.entries[0];
					const box = entry.avcC || entry.hvcC || entry.vpcC || entry.av1C;
					if (box) {
						const stream = new MP4Box.DataStream(undefined, 0, MP4Box.DataStream.BIG_ENDIAN);
						box.write(stream);
						description = new Uint8Array(stream.buffer, 8);
					}
				}

				decoder = new VideoDecoder({
					output: (frame) => {
						decodeChain = decodeChain.then(async () => {
							offCtx.drawImage(frame, 0, 0, offCanvas.width, offCanvas.height);
							const ts = frame.timestamp;
							frame.close();
							const blob = await new Promise(r => offCanvas.toBlob(r, 'image/webp', 0.82));
							bank.frames.push({ ts, blob });
							completedDecodeCount++;
						});
					},
					error: (err) => {
						rejectTrack(err);
					}
				});

				decoder.configure({
					codec: track.codec,
					codedWidth: track.video.width || 1280,
					codedHeight: track.video.height || 720,
					description: description,
					hardwareAcceleration: accel
				});

				mp4boxfile.setExtractionOptions(track.id, null, { nbSamples: Infinity });
				mp4boxfile.start();
			};

			mp4boxfile.onSamples = (track_id, ref, s) => {
				samples.push(...s);
				resolveTrack();
			};

			mp4boxfile.onError = (e) => rejectTrack(e);

			const response = await fetch(VIDEO_URL);
			const arrayBuffer = await response.arrayBuffer();
			arrayBuffer.fileStart = 0;
			mp4boxfile.appendBuffer(arrayBuffer);
			mp4boxfile.flush();

			await trackPromise;
			clearTimeout(watchdog);

			if (!samples.length) throw new Error('No samples extracted');

			for (let i = 0; i < samples.length; i++) {
				const s = samples[i];
				while (i - completedDecodeCount > LEAD) {
					await new Promise(r => setTimeout(r, 6));
				}
				decoder.decode(new EncodedVideoChunk({
					type: s.is_sync ? 'key' : 'delta',
					timestamp: (s.cts * 1e6) / s.timescale,
					duration: (s.duration * 1e6) / s.timescale,
					data: s.data
				}));
			}

			await decoder.flush();
			await decodeChain;

			bank.frames.sort((a, b) => a.ts - b.ts);

			const targetMicros = currentTime * 1e6;
			const seedIdx = binarySearchNearest(targetMicros);
			const seedBm = await createImageBitmap(bank.frames[seedIdx].blob);
			bank.lru.set(seedIdx, seedBm);
			bank.ready = true;
			drawCanvasFrame(seedBm);
			bank.drawnIndex = seedIdx;
			bank.painted = true;
			canvas.style.opacity = '1';
		}
	})();
</script>

<?php
get_footer();
