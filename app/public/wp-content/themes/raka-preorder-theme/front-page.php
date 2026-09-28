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
$left_hero_cards     = [];
$right_hero_cards    = [];

foreach ( $hero_cards as $idx => $c ) {
	if ( isset( $c['position'] ) && 'right' === $c['position'] ) {
		$right_hero_cards[] = $c;
	} elseif ( isset( $c['position'] ) && 'left' === $c['position'] ) {
		$left_hero_cards[] = $c;
	} else {
		if ( 0 === $idx % 2 ) {
			$left_hero_cards[] = $c;
		} else {
			$right_hero_cards[] = $c;
		}
	}
}

$max_stream_count    = max( count( $left_hero_cards ), count( $right_hero_cards ), 1 );
$hero_section_height = max( 260, ( $max_stream_count * 80 ) + 100 ) . 'vh';

$featured_categories = raka_get_featured_categories();
$featured_brands     = raka_get_featured_brands();
$contact_options     = raka_get_contact_options();
$theme_options       = raka_get_all_options();

$flash_sale_campaign = raka_get_flash_sale_campaign_data();
$flash_sale_limit    = ! empty( $theme_options['flash_sale_limit'] ) ? (int) $theme_options['flash_sale_limit'] : 4;
$our_products_limit  = ! empty( $theme_options['our_products_limit'] ) ? (int) $theme_options['our_products_limit'] : 8;

$flash_sale_products = array_slice( raka_get_flash_sale_products( $flash_sale_limit ), 0, $flash_sale_limit );
$homepage_catalog    = raka_get_homepage_catalog_products( $our_products_limit );
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
		$card_uid = ! empty( $card['product_id'] ) ? (string) $card['product_id'] : sanitize_title( $card['title'] ?? uniqid( 'card_' ) );
		?>
		<?php if ( ! empty( $card['show_custom_text'] ) ) : ?>
			<!-- Custom Statement / Atmospheric Text Card Mode -->
			<div id="raka-hero-card-txt-<?php echo esc_attr( $card_uid ); ?>" class="pointer-events-auto block max-w-md lg:max-w-[420px] w-full p-6 md:p-7 rounded-3xl bg-white/95 dark:bg-neutral-900/90 backdrop-blur-2xl border border-black/10 dark:border-white/15 theme-transition group hover:border-black/25 dark:hover:border-white/25 transition-all duration-300 <?php echo esc_attr( $custom_classes ); ?>">
				<?php if ( ! empty( $card['tag'] ) ) : ?>
					<div id="raka-hero-card-tag-<?php echo esc_attr( $card_uid ); ?>" class="text-[11px] tracking-[0.25em] uppercase text-red-600 dark:text-red-400 mb-3 font-mono font-bold"><?php echo esc_html( $card['tag'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $card['title'] ) ) : ?>
					<h3 id="raka-hero-card-title-<?php echo esc_attr( $card_uid ); ?>" class="text-xl md:text-2xl font-black text-black dark:text-white mb-2 font-hn group-hover:translate-x-1 transition-transform leading-tight"><?php echo esc_html( $card['title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( ! empty( $card['description'] ) ) : ?>
					<p id="raka-hero-card-desc-<?php echo esc_attr( $card_uid ); ?>" class="text-xs md:text-sm leading-[1.7] text-neutral-700 dark:text-neutral-300 font-light font-hn">
						<?php echo esc_html( $card['description'] ); ?>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $card['url'] ) && '#' !== $card['url'] ) : ?>
					<a id="raka-hero-card-btn-<?php echo esc_attr( $card_uid ); ?>" href="<?php echo esc_url( $card['url'] ); ?>" class="mt-4 inline-flex items-center gap-2 text-xs uppercase tracking-widest text-neutral-600 dark:text-neutral-400 group-hover:text-red-600 dark:group-hover:text-red-400 font-mono transition-colors">
						<span id="raka-hero-card-btntxt-<?php echo esc_attr( $card_uid ); ?>"><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Explore', 'raka-preorder-theme' ) ); ?></span> <span id="raka-hero-card-btnarr-<?php echo esc_attr( $card_uid ); ?>" class="group-hover:translate-x-1 transition-transform">&rarr;</span>
					</a>
				<?php endif; ?>
			</div>
		<?php else : ?>
			<!-- WooCommerce Product Card Mode with Direct Add to Cart Action -->
			<div id="raka-hero-prod-card-<?php echo esc_attr( $card_uid ); ?>" class="pointer-events-auto block max-w-full sm:max-w-md lg:max-w-[420px] w-full p-4 sm:p-5 md:p-6 rounded-2xl sm:rounded-3xl bg-white/95 dark:bg-neutral-900/90 backdrop-blur-2xl border border-black/10 dark:border-white/15 theme-transition group hover:border-black/25 dark:hover:border-white/25 transition-all duration-300 <?php echo esc_attr( $custom_classes ); ?>">
				<!-- Header: Tag + Price -->
				<div id="raka-hero-card-top-<?php echo esc_attr( $card_uid ); ?>" class="flex items-center justify-between gap-3 mb-2.5 sm:mb-3">
					<?php if ( ! empty( $card['tag'] ) ) : ?>
						<span id="raka-hero-tag-<?php echo esc_attr( $card_uid ); ?>" class="inline-flex items-center px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-[10px] md:text-[11px] font-bold tracking-widest uppercase bg-red-500/10 dark:bg-red-500/15 text-red-600 dark:text-red-400 border border-red-500/20 dark:border-red-500/25 font-mono">
							<?php echo esc_html( $card['tag'] ); ?>
						</span>
					<?php endif; ?>
					<?php if ( ! empty( $card['price_html'] ) ) : ?>
						<div id="raka-hero-price-<?php echo esc_attr( $card_uid ); ?>" class="text-xs sm:text-sm md:text-base font-bold text-neutral-900 dark:text-white font-hn">
							<?php echo esc_html( $card['price_html'] ); ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Card Content: Image & Text -->
				<div id="raka-hero-body-<?php echo esc_attr( $card_uid ); ?>" class="flex items-start gap-3 sm:gap-4">
					<?php if ( ! empty( $card['image_url'] ) ) : ?>
						<a id="raka-hero-img-link-<?php echo esc_attr( $card_uid ); ?>" href="<?php echo esc_url( $card['url'] ); ?>" class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-xl sm:rounded-2xl overflow-hidden bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 shrink-0 group-hover:scale-105 transition-transform duration-300 block">
							<img id="raka-hero-img-<?php echo esc_attr( $card_uid ); ?>" src="<?php echo esc_url( $card['image_url'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" class="w-full h-full object-cover" />
						</a>
					<?php endif; ?>
					<div id="raka-hero-meta-<?php echo esc_attr( $card_uid ); ?>" class="flex-1 min-w-0">
						<?php if ( ! empty( $card['title'] ) ) : ?>
							<h3 id="raka-hero-title-<?php echo esc_attr( $card_uid ); ?>" class="text-sm sm:text-base md:text-lg font-bold text-black dark:text-white font-hn group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors leading-snug line-clamp-2">
								<a id="raka-hero-title-a-<?php echo esc_attr( $card_uid ); ?>" href="<?php echo esc_url( $card['url'] ); ?>">
									<?php echo esc_html( $card['title'] ); ?>
								</a>
							</h3>
						<?php endif; ?>
						<?php if ( ! empty( $card['description'] ) ) : ?>
							<p id="raka-hero-desc-<?php echo esc_attr( $card_uid ); ?>" class="text-[11px] sm:text-xs leading-relaxed text-black/70 dark:text-white/75 font-light font-hn mt-1 sm:mt-1.5 line-clamp-2">
								<?php echo esc_html( $card['description'] ); ?>
							</p>
						<?php endif; ?>
					</div>
				</div>

				<!-- Card CTA Action Footer: Direct Add to Cart + Details Link -->
				<div id="raka-hero-footer-<?php echo esc_attr( $card_uid ); ?>" class="mt-3 sm:mt-4 pt-2.5 sm:pt-3.5 border-t border-black/10 dark:border-white/10 flex items-center justify-between gap-2 sm:gap-3">
					<?php if ( ! empty( $card['product_id'] ) ) : ?>
						<button
							id="raka-hero-cart-btn-<?php echo esc_attr( $card_uid ); ?>"
							type="button"
							onclick="window.rakaCardAddToCart(<?php echo esc_attr( $card['product_id'] ); ?>, this)"
							class="raka-hero-add-to-cart-btn inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-neutral-900 hover:bg-red-600 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-[11px] sm:text-xs font-bold uppercase tracking-wider font-mono transition-all cursor-pointer"
						>
							<i id="raka-hero-cart-ico-<?php echo esc_attr( $card_uid ); ?>" class="ri-shopping-bag-3-line text-xs sm:text-sm flex-shrink-0"></i>
							<span id="raka-hero-cart-txt-<?php echo esc_attr( $card_uid ); ?>"><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Pre-Order Now', 'raka-preorder-theme' ) ); ?></span>
						</button>
					<?php else : ?>
						<a
							id="raka-hero-po-link-<?php echo esc_attr( $card_uid ); ?>"
							href="<?php echo esc_url( $card['url'] ); ?>"
							class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-neutral-900 hover:bg-red-600 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-[11px] sm:text-xs font-bold uppercase tracking-wider font-mono transition-all"
						>
							<span id="raka-hero-po-txt-<?php echo esc_attr( $card_uid ); ?>"><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Pre-Order Now', 'raka-preorder-theme' ) ); ?></span>
							<span id="raka-hero-po-arr-<?php echo esc_attr( $card_uid ); ?>" class="group-hover:translate-x-1 transition-transform">&rarr;</span>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $card['url'] ) && '#' !== $card['url'] ) : ?>
						<a id="raka-hero-detail-link-<?php echo esc_attr( $card_uid ); ?>" href="<?php echo esc_url( $card['url'] ); ?>" class="text-[11px] sm:text-xs font-medium text-neutral-500 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 inline-flex items-center gap-1 font-mono transition-colors">
							<span id="raka-hero-detail-txt-<?php echo esc_attr( $card_uid ); ?>"><?php esc_html_e( 'View Details', 'raka-preorder-theme' ); ?></span>
							<span id="raka-hero-detail-arr-<?php echo esc_attr( $card_uid ); ?>">&rarr;</span>
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

			<!-- Bottom Left: Caption Text Only -->
			<div id="hero-bottom-left-panel" class="absolute bottom-5 sm:bottom-6 md:bottom-8 left-4 sm:left-6 md:left-10 right-4 sm:right-auto pointer-events-auto flex flex-col items-start gap-2 max-w-sm z-20">
				<p id="hero-caption-text" class="text-[10px] md:text-[11px] tracking-[0.14em] uppercase leading-relaxed text-black/85 dark:text-white/85 font-hn font-medium theme-transition max-w-[280px] sm:max-w-none">
					<?php echo nl2br( esc_html( $hero_caption ) ); ?>
				</p>
			</div>

			<!-- Bottom Right: Categories & Brands Popup Buttons -->
			<div id="hero-bottom-right-panel" class="absolute bottom-5 sm:bottom-6 md:bottom-8 left-4 right-4 sm:left-auto sm:right-6 md:right-10 pointer-events-auto flex items-center justify-between sm:justify-end gap-2 sm:gap-3 z-20">
				<button
					id="hero-categories-trigger-btn"
					type="button"
					onclick="window.rakaOpenModal('categories')"
					class="group flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-full bg-white/95 dark:bg-black/90 hover:bg-white dark:hover:bg-black backdrop-blur-2xl border border-black/15 dark:border-white/25 text-xs sm:text-sm font-bold tracking-wider uppercase text-neutral-950 dark:text-white shadow-md hover:shadow-lg hover:scale-105 active:scale-95 transition-all duration-300 font-hn cursor-pointer whitespace-nowrap"
				>
					<i id="hero-categories-btn-icon" class="ri-grid-fill text-sm sm:text-base text-neutral-800 dark:text-neutral-200 opacity-80 group-hover:opacity-100 group-hover:rotate-12 transition-transform"></i>
					<span id="hero-categories-btn-text"><?php esc_html_e( 'Categories', 'raka-preorder-theme' ); ?></span>
				</button>

				<button
					id="hero-brands-trigger-btn"
					type="button"
					onclick="window.rakaOpenModal('brands')"
					class="group flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-full bg-white/95 dark:bg-black/90 hover:bg-white dark:hover:bg-black backdrop-blur-2xl border border-black/15 dark:border-white/25 text-xs sm:text-sm font-bold tracking-wider uppercase text-neutral-950 dark:text-white shadow-md hover:shadow-lg hover:scale-105 active:scale-95 transition-all duration-300 font-hn cursor-pointer whitespace-nowrap"
				>
					<i id="hero-brands-btn-icon" class="ri-fire-fill text-sm sm:text-base text-amber-500 group-hover:scale-125 transition-transform"></i>
					<span id="hero-brands-btn-text"><?php esc_html_e( 'Brands', 'raka-preorder-theme' ); ?></span>
				</button>
			</div>
		</div>

		<!-- 5. Dual Column Continuous Slide-Up Stream (Left & Right Flanks) -->
		<div id="hero-streams-container" class="absolute inset-0 z-20 pointer-events-none overflow-hidden">
			<!-- Left Flank Stream -->
			<div id="hero-stream-col-left" class="hero-stream-column absolute top-0 bottom-0 left-2.5 sm:left-6 md:left-10 lg:left-14 w-[92%] sm:w-[380px] lg:w-[420px] pointer-events-none overflow-hidden [mask-image:linear-gradient(to_bottom,transparent_0%,black_10%,black_90%,transparent_100%)]">
				<div id="hero-stream-track-left" class="hero-stream-track flex flex-col gap-4 sm:gap-6 pt-[80vh] pb-[60vh] will-change-transform">
					<?php foreach ( $left_hero_cards as $card_i => $card ) : ?>
						<div id="hero-stream-left-card-<?php echo esc_attr( (string) $card_i ); ?>" class="hero-stream-card pointer-events-auto">
							<?php raka_render_hero_card_item( $card ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Right Flank Stream -->
			<div id="hero-stream-col-right" class="hero-stream-column absolute top-0 bottom-0 right-2.5 sm:right-6 md:right-10 lg:right-14 w-[92%] sm:w-[380px] lg:w-[420px] pointer-events-none overflow-hidden hidden sm:block [mask-image:linear-gradient(to_bottom,transparent_0%,black_10%,black_90%,transparent_100%)]">
				<div id="hero-stream-track-right" class="hero-stream-track flex flex-col gap-4 sm:gap-6 pt-[110vh] pb-[60vh] will-change-transform">
					<?php foreach ( $right_hero_cards as $card_i => $card ) : ?>
						<div id="hero-stream-right-card-<?php echo esc_attr( (string) $card_i ); ?>" class="hero-stream-card pointer-events-auto">
							<?php raka_render_hero_card_item( $card ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- ── SECTION: FLASH SALE (LIMITED DROPS) ── -->
<?php if ( ! empty( $flash_sale_campaign['enabled'] ) && ! empty( $flash_sale_products ) ) : ?>
<section id="raka-flashsale-section" class="relative z-20 py-16 sm:py-20 md:py-24 bg-neutral-100 dark:bg-neutral-950 border-t border-neutral-300 dark:border-neutral-800/80 text-neutral-900 dark:text-white transition-colors duration-300 overflow-hidden">
	<div id="raka-flashsale-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Section Header -->
		<div id="raka-flashsale-header" class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 sm:pb-10 border-b border-neutral-300 dark:border-neutral-800">
			<!-- Title & Badge -->
			<div id="raka-flashsale-title-wrap" class="space-y-2.5">
				<div id="raka-flashsale-badge-row" class="flex items-center gap-2.5">
					<span id="raka-flashsale-pulse-dot" class="relative flex h-2.5 w-2.5">
						<span id="raka-flashsale-ping" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
						<span id="raka-flashsale-static-dot" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
					</span>
					<span id="raka-flashsale-tag" class="text-xs font-mono font-bold tracking-[0.2em] text-red-600 dark:text-red-400 uppercase">
						<?php echo esc_html( $flash_sale_campaign['tag'] ); ?>
					</span>
				</div>
				<h2 id="raka-flashsale-title" class="text-2xl sm:text-3xl md:text-4xl font-black font-hn tracking-tight text-neutral-950 dark:text-white flex items-center gap-3">
					<span id="raka-flashsale-title-text"><?php echo esc_html( $flash_sale_campaign['title'] ); ?></span>
					<i id="raka-flashsale-fire-icon" class="ri-flashlight-fill text-red-500 text-2xl sm:text-3xl"></i>
				</h2>
				<p id="raka-flashsale-desc" class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 font-light font-hn max-w-md">
					<?php echo esc_html( $flash_sale_campaign['desc'] ); ?>
				</p>
			</div>

			<!-- Live Countdown Timer -->
			<div id="raka-flashsale-timer-wrap" 
				class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 bg-white/90 dark:bg-neutral-900/90 border border-neutral-300 dark:border-neutral-800 rounded-2xl p-3 sm:p-4 shrink-0 shadow-sm"
				x-data="{
					remaining: <?php echo (int) $flash_sale_campaign['remaining_seconds']; ?>,
					hours: '<?php echo esc_js( sprintf( '%02d', floor( $flash_sale_campaign['remaining_seconds'] / 3600 ) ) ); ?>',
					mins: '<?php echo esc_js( sprintf( '%02d', floor( ( $flash_sale_campaign['remaining_seconds'] % 3600 ) / 60 ) ) ); ?>',
					secs: '<?php echo esc_js( sprintf( '%02d', $flash_sale_campaign['remaining_seconds'] % 60 ) ); ?>',
					timer: null,
					init() {
						this.update();
						this.timer = setInterval(() => {
							if (this.remaining > 0) {
								this.remaining--;
								this.update();
							} else {
								clearInterval(this.timer);
							}
						}, 1000);
					},
					update() {
						const h = Math.floor(this.remaining / 3600);
						const m = Math.floor((this.remaining % 3600) / 60);
						const s = this.remaining % 60;
						this.hours = String(h).padStart(2, '0');
						this.mins = String(m).padStart(2, '0');
						this.secs = String(s).padStart(2, '0');
					}
				}">
				<div id="raka-flashsale-timer-label-wrap" class="flex items-center gap-2">
					<i id="raka-flashsale-timer-icon" class="ri-time-line text-red-500 dark:text-red-400 text-lg"></i>
					<span id="raka-flashsale-timer-label" class="text-[11px] font-mono tracking-widest uppercase text-neutral-500 dark:text-neutral-400">
						<?php echo esc_html( $flash_sale_campaign['timer_label'] ); ?>
					</span>
				</div>
				<div id="raka-flashsale-countdown-clock" class="flex items-center gap-1.5 font-mono">
					<div id="raka-timer-box-hours" class="flex flex-col items-center justify-center w-12 h-11 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700/60">
						<span id="raka-timer-hours" class="text-base sm:text-lg font-bold text-neutral-950 dark:text-white tracking-wider" x-text="hours"><?php echo esc_html( sprintf( '%02d', floor( $flash_sale_campaign['remaining_seconds'] / 3600 ) ) ); ?></span>
						<span id="raka-timer-hours-sub" class="text-[9px] text-neutral-500 dark:text-neutral-400 font-sans uppercase">HRS</span>
					</div>
					<span id="raka-timer-colon-1" class="text-red-500 font-bold text-lg">:</span>
					<div id="raka-timer-box-mins" class="flex flex-col items-center justify-center w-12 h-11 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700/60">
						<span id="raka-timer-mins" class="text-base sm:text-lg font-bold text-neutral-950 dark:text-white tracking-wider" x-text="mins"><?php echo esc_html( sprintf( '%02d', floor( ( $flash_sale_campaign['remaining_seconds'] % 3600 ) / 60 ) ) ); ?></span>
						<span id="raka-timer-mins-sub" class="text-[9px] text-neutral-500 dark:text-neutral-400 font-sans uppercase">MIN</span>
					</div>
					<span id="raka-timer-colon-2" class="text-red-500 font-bold text-lg">:</span>
					<div id="raka-timer-box-secs" class="flex flex-col items-center justify-center w-12 h-11 rounded-xl bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-700/60">
						<span id="raka-timer-secs" class="text-base sm:text-lg font-bold text-red-600 dark:text-red-400 tracking-wider" x-text="secs"><?php echo esc_html( sprintf( '%02d', $flash_sale_campaign['remaining_seconds'] % 60 ) ); ?></span>
						<span id="raka-timer-secs-sub" class="text-[9px] text-neutral-500 dark:text-neutral-400 font-sans uppercase">SEC</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Products Grid -->
		<div id="raka-flashsale-grid" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-6 pt-8 sm:pt-10">
			<?php
			foreach ( $flash_sale_products as $item ) :
				$p_obj = wc_get_product( $item['id'] );
				if ( ! $p_obj ) {
					continue;
				}
				raka_render_product_card( $p_obj, [
					'id_prefix'     => 'flash',
					'is_flash_sale' => true,
					'discount_pct'  => $item['discount_pct'] ?? 0,
					'claimed_pct'   => $item['claimed_pct'] ?? 0,
					'left_count'    => $item['left_count'] ?? 0,
				] );
			endforeach;
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ── SECTION: OUR PRODUCTS (CATALOG & FILTER TABS) ── -->
<?php if ( ! empty( $catalog_products ) ) : ?>
<section id="raka-our-products-section" class="relative z-20 py-16 sm:py-20 md:py-24 bg-white dark:bg-neutral-900/60 border-t border-neutral-200 dark:border-neutral-800/80 text-neutral-900 dark:text-white transition-colors duration-300">
	<div id="raka-our-products-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
		<!-- Section Header -->
		<div id="raka-our-products-header" class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-neutral-200 dark:border-neutral-800/80">
			<div id="raka-our-products-title-wrap" class="space-y-2">
				<span id="raka-our-products-tag" class="text-xs font-mono font-bold tracking-[0.2em] text-neutral-500 dark:text-neutral-400 uppercase">
					<?php esc_html_e( 'CURATED CATALOG', 'raka-preorder-theme' ); ?>
				</span>
				<h2 id="raka-our-products-title" class="text-2xl sm:text-3xl md:text-4xl font-black font-hn tracking-tight text-neutral-950 dark:text-white">
					<?php esc_html_e( 'OUR PRODUCTS', 'raka-preorder-theme' ); ?>
				</h2>
				<p id="raka-our-products-desc" class="text-xs sm:text-sm text-neutral-600 dark:text-neutral-400 font-light font-hn max-w-xl">
					<?php esc_html_e( 'Explore custom keyboards, precision ergonomic gaming mice, and studio-grade audio gear built for uncompromising performance.', 'raka-preorder-theme' ); ?>
				</p>
			</div>

			<!-- All Products Link -->
			<div id="raka-our-products-header-link" class="shrink-0">
				<a id="raka-view-all-catalog-btn" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-widest text-neutral-500 dark:text-neutral-400 hover:text-neutral-950 dark:hover:text-white transition-colors group">
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
				class="raka-catalog-tab-btn active px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider transition-all duration-200 shrink-0 bg-neutral-950 text-white dark:bg-white dark:text-neutral-950 shadow-sm"
			>
				<?php esc_html_e( 'All Gear', 'raka-preorder-theme' ); ?>
			</button>
			<?php if ( ! empty( $catalog_categories ) ) : ?>
				<?php foreach ( $catalog_categories as $cat_slug => $cat_name ) : ?>
					<button
						id="raka-catalog-tab-<?php echo esc_attr( $cat_slug ); ?>"
						type="button"
						onclick="window.rakaFilterCatalog('<?php echo esc_js( $cat_slug ); ?>', this)"
						class="raka-catalog-tab-btn px-4 py-2 rounded-xl text-xs font-mono font-bold uppercase tracking-wider transition-all duration-200 shrink-0 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 dark:bg-neutral-800/80 dark:hover:bg-neutral-800 dark:text-neutral-300 border border-neutral-300 dark:border-neutral-700/50"
					>
						<?php echo esc_html( $cat_name ); ?>
					</button>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<!-- Product Grid -->
		<div id="raka-catalog-grid" class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-6">
			<?php
			foreach ( $catalog_products as $p ) :
				$prod_obj = wc_get_product( $p['id'] );
				if ( ! $prod_obj ) {
					continue;
				}
				raka_render_product_card( $prod_obj, [
					'id_prefix'     => 'cat',
					'data_category' => implode( ' ', (array) $p['cat_slugs'] ),
					'extra_classes' => 'raka-catalog-product-card',
				] );
			endforeach;
			?>
		</div>

		<!-- Bottom CTA Strip -->
		<div id="raka-catalog-bottom-cta-wrap" class="mt-12 sm:mt-16 text-center">
			<a id="raka-catalog-explore-btn" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>" class="inline-flex items-center justify-center gap-3 py-3.5 px-8 rounded-2xl bg-neutral-900 hover:bg-neutral-800 text-white dark:bg-neutral-800 dark:hover:bg-neutral-700 font-mono text-xs sm:text-sm font-bold uppercase tracking-widest border border-neutral-300 dark:border-neutral-700 hover:border-neutral-400 dark:hover:border-neutral-600 transition-all duration-300">
				<span id="raka-catalog-explore-txt"><?php esc_html_e( 'BROWSE ALL PRE-ORDERS & ACCESSORIES', 'raka-preorder-theme' ); ?></span>
				<i id="raka-catalog-explore-ico" class="ri-arrow-right-up-line text-base text-red-500 dark:text-red-400"></i>
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
		var isDark = document.documentElement.classList.contains('dark');
		var buttons = document.querySelectorAll('.raka-catalog-tab-btn');
		buttons.forEach(function(b) {
			b.classList.remove('active', 'bg-neutral-950', 'text-white', 'dark:bg-white', 'dark:text-neutral-950', 'shadow-sm');
			b.classList.add('bg-neutral-100', 'text-neutral-700', 'dark:bg-neutral-800/80', 'dark:text-neutral-300', 'border', 'border-neutral-300', 'dark:border-neutral-700/50');
		});

		if (btn) {
			btn.classList.add('active', 'bg-neutral-950', 'text-white', 'dark:bg-white', 'dark:text-neutral-950', 'shadow-sm');
			btn.classList.remove('bg-neutral-100', 'text-neutral-700', 'dark:bg-neutral-800/80', 'dark:text-neutral-300', 'border', 'border-neutral-300', 'dark:border-neutral-700/50');
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
<section id="raka-closing-section" class="relative min-h-screen bg-neutral-100 dark:bg-black text-neutral-950 dark:text-white flex flex-col justify-between overflow-hidden theme-transition transition-colors duration-300">
	<!-- Top rule -->
	<div id="raka-closing-top-rule-wrap" class="w-full px-6 md:px-12 lg:px-16 pt-16 md:pt-20">
		<div id="raka-closing-top-rule" class="h-px bg-black/10 dark:bg-white/10 w-full"></div>
	</div>

	<!-- Main -->
	<div id="raka-closing-main-body" class="flex-1 flex flex-col justify-center px-6 md:px-12 lg:px-16 py-16">
		<div id="raka-closing-subtitle" class="text-[10px] tracking-[0.3em] uppercase text-neutral-500 dark:text-white/30 mb-8 font-hn"><?php esc_html_e( 'Convictions', 'raka-preorder-theme' ); ?></div>
		<h2 id="raka-closing-heading" class="text-[8vw] sm:text-[7vw] md:text-[5vw] lg:text-[4vw] font-light leading-[1.15] tracking-[-0.02em] text-neutral-950 dark:text-white/90 max-w-5xl font-hn">
			We don't merely mark skin. <br class="hidden sm:block"/>
			<span id="raka-closing-heading-sub" class="text-neutral-500 dark:text-white/40"> We distill stories into permanence&mdash;</span> <br class="hidden sm:block"/>
			<span id="raka-closing-heading-end"> one stroke at a time.</span>
		</h2>
		<p id="raka-closing-desc" class="mt-10 text-xs md:text-sm leading-[1.8] text-neutral-600 dark:text-white/40 font-light max-w-md font-hn">
			Every work begins as a dialogue and arrives as a partnership. Your history becomes our medium. No shortcuts, no repetition—only purpose translated into ink.
		</p>
	</div>

	<!-- Footer -->
	<div id="raka-closing-footer-wrap" class="px-6 md:px-12 lg:px-16 pb-10">
		<div id="raka-closing-bot-rule" class="h-px bg-black/10 dark:bg-white/10 w-full mb-8"></div>
		<div id="raka-closing-footer-grid" class="flex flex-col md:flex-row items-start md:items-end justify-between gap-6">
			<div id="raka-closing-atelier-col">
				<div id="raka-closing-atelier-lbl" class="text-[10px] tracking-[0.3em] uppercase text-neutral-500 dark:text-white/30 mb-2 font-hn">Atelier</div>
				<div id="raka-closing-atelier-addr" class="text-sm text-neutral-700 dark:text-white/60 font-light leading-relaxed font-hn">
					Brooklyn, New York<br/>
					By private session only
				</div>
			</div>
			<div id="raka-closing-action-col" class="flex flex-col items-start md:items-end gap-3">
				<div id="raka-closing-action-lbl" class="text-[10px] tracking-[0.3em] uppercase text-neutral-500 dark:text-white/30 font-hn">Start your journey</div>
				<a id="raka-closing-consult-link" href="#" class="group inline-flex items-center gap-3 text-neutral-900 dark:text-white/80 hover:text-red-600 dark:hover:text-white transition-colors duration-300">
					<span id="raka-closing-consult-txt" class="text-sm font-light tracking-wide font-hn">Schedule a consultation</span>
					<span id="raka-closing-consult-line" class="w-8 h-px bg-black/30 dark:bg-white/40 group-hover:w-12 group-hover:bg-red-600 dark:group-hover:bg-white transition-all duration-300"></span>
				</a>
			</div>
			<div id="raka-closing-copy-col" class="text-right mt-4 md:mt-0 text-[10px] tracking-[0.2em] uppercase text-neutral-400 dark:text-white/20 font-hn">
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
		const overlayPng   = document.getElementById('hero-overlay-png-wrap');
		const heroSec1     = document.getElementById('hero-section-1');
		const trackLeft    = document.getElementById('hero-stream-track-left');
		const trackRight   = document.getElementById('hero-stream-track-right');

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
			const introDuration = 0.22;

			if (heroSec1) {
				let heroOpacity = 1;
				if (prog > 0.05) {
					const t = Math.min(1, (prog - 0.05) / (introDuration - 0.05));
					heroOpacity = Math.max(0, 1 - t);
				}
				heroSec1.style.opacity = heroOpacity.toFixed(3);
				heroSec1.style.transform = `translate3d(0, ${(prog * -30).toFixed(1)}px, 0)`;
				heroSec1.style.visibility = heroOpacity > 0.01 ? 'visible' : 'hidden';
				heroSec1.style.pointerEvents = heroOpacity > 0.3 ? 'auto' : 'none';
			}

			if (overlayPng) {
				let pngOpacity = 1;
				if (prog > 0.06) {
					const t = Math.min(1, (prog - 0.06) / (introDuration - 0.06));
					pngOpacity = Math.max(0, 1 - t);
				}
				overlayPng.style.opacity = pngOpacity.toFixed(3);
				overlayPng.style.visibility = pngOpacity > 0.01 ? 'visible' : 'hidden';
			}

			// 2. Dual Column Continuous Parallax Card Slide-Up Scrubbing
			const streamProg = Math.max(0, Math.min(1, (prog - 0.04) / 0.94));

			if (trackLeft) {
				const leftTotal = Math.max(0, trackLeft.scrollHeight - (window.innerHeight * 0.65));
				const leftTranslate = streamProg * leftTotal;
				trackLeft.style.transform = `translate3d(0, -${leftTranslate.toFixed(1)}px, 0)`;
			}

			if (trackRight) {
				const rightTotal = Math.max(0, trackRight.scrollHeight - (window.innerHeight * 0.65));
				const rightTranslate = streamProg * rightTotal;
				trackRight.style.transform = `translate3d(0, -${rightTranslate.toFixed(1)}px, 0)`;
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
