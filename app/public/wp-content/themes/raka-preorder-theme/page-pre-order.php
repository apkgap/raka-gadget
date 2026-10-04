<?php
/**
 * Template Name: Pre-Order Hero & Tracking Center
 * The template for displaying the Pre-Order page with two-column hero and central tracking form.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$video_url = get_template_directory_uri() . '/assets/images/a9-air.mp4';
?>

<style>
	:root {
		--po-ink: #0d0f0f;
		--po-ink-soft: #5b6260;
		--po-line: #e8ebea;
		--po-panel: #ffffff;
		--po-box: #f5f7f6;
		--po-radius-panel: 30px;
		--po-radius-box: 18px;
		--po-pad: clamp(16px, 2vw, 32px);
	}

	.dark {
		--po-ink: #f5f7f6;
		--po-ink-soft: #9aa29f;
		--po-line: rgba(255, 255, 255, 0.1);
		--po-panel: #11141b;
		--po-box: #181d26;
	}

	.raka-po-hero {
		position: relative;
		min-height: calc(100vh - 72px);
		width: 100%;
		overflow: hidden;
		isolation: isolate;
		background: #eef2f1;
		transition: background-color 0.3s ease;
	}

	.dark .raka-po-hero {
		background: #080a10;
	}

	.raka-po-hero__bg {
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		object-fit: cover;
		z-index: -2;
		transform-origin: center;
		animation: po-in-bg 1.35s cubic-bezier(.16, 1, .3, 1) both;
	}

	.raka-po-hero__scrim {
		position: absolute;
		inset: 0;
		z-index: -1;
		background: linear-gradient(90deg, rgba(238, 242, 241, 0.95) 0%, rgba(238, 242, 241, 0.7) 48%, rgba(238, 242, 241, 0.15) 75%, rgba(238, 242, 241, 0) 100%);
		animation: po-in-fade 1s ease both 0.08s;
	}

	.dark .raka-po-hero__scrim {
		background: linear-gradient(90deg, rgba(8, 10, 16, 0.96) 0%, rgba(8, 10, 16, 0.75) 48%, rgba(8, 10, 16, 0.25) 75%, rgba(8, 10, 16, 0) 100%);
	}

	.raka-po-hero__inner {
		position: relative;
		min-height: calc(100vh - 72px);
		width: 100%;
		padding: clamp(10px, 1.2vw, 22px);
		display: grid;
		grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.72fr);
		gap: clamp(10px, 1.2vw, 22px);
		align-items: stretch;
	}

	.raka-po-panel {
		position: relative;
		background: var(--po-panel);
		border-radius: var(--po-radius-panel);
		padding: var(--po-pad);
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		min-height: 0;
		box-shadow: 0 24px 60px -30px rgba(16, 32, 28, 0.28);
		border: 1px solid var(--po-line);
		z-index: 10;
		transition: background-color 0.3s ease, border-color 0.3s ease;
	}

	.dark .raka-po-panel {
		box-shadow: 0 24px 60px -30px rgba(0, 0, 0, 0.75);
	}

	/* Topbar inside panel */
	.raka-po-topbar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		flex: 0 0 auto;
		padding-bottom: 12px;
		border-bottom: 1px solid var(--po-line);
	}

	.raka-po-brand-tag {
		display: inline-flex;
		align-items: center;
		gap: 8px;
		font-size: clamp(12px, 0.9vw, 14px);
		font-weight: 700;
		letter-spacing: 0.05em;
		text-transform: uppercase;
		color: var(--po-ink);
	}

	.raka-po-radar {
		position: relative;
		display: inline-flex;
		width: 8px;
		height: 8px;
	}

	.raka-po-radar-ping {
		position: absolute;
		display: inline-flex;
		width: 100%;
		height: 100%;
		border-radius: 9999px;
		background-color: #ef4444;
		opacity: 0.75;
		animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
	}

	.raka-po-radar-dot {
		position: relative;
		display: inline-flex;
		border-radius: 9999px;
		width: 8px;
		height: 8px;
		background-color: #dc2626;
	}

	.raka-po-connect {
		display: flex;
		align-items: center;
		gap: clamp(6px, 0.6vw, 10px);
		border: 1px solid var(--po-line);
		border-radius: 999px;
		padding: 4px 10px;
		font-size: 11px;
		font-weight: 600;
		letter-spacing: 0.05em;
		text-transform: uppercase;
		color: var(--po-ink-soft);
	}

	/* Center Stage & Form */
	.raka-po-stage {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
		justify-content: center;
		padding: clamp(14px, 2vh, 24px) 0;
		min-height: 0;
	}

	.raka-po-headline {
		font-size: clamp(26px, 2.8vw, 42px);
		line-height: 1.1;
		letter-spacing: -0.035em;
		font-weight: 400;
		color: var(--po-ink);
		margin-bottom: 6px;
	}

	.raka-po-headline b {
		font-weight: 800;
	}

	.raka-po-subtitle {
		font-size: clamp(12px, 0.95vw, 14px);
		color: var(--po-ink-soft);
		margin-bottom: clamp(14px, 1.8vh, 22px);
		line-height: 1.4;
	}

	.raka-po-form-wrapper {
		width: 100%;
		border-radius: var(--po-radius-box);
		background: var(--po-box);
		border: 1px solid var(--po-line);
		padding: clamp(12px, 1.5vw, 20px);
		box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
		transition: background-color 0.3s ease, border-color 0.3s ease;
	}

	/* Bottom Row 3 Cards */
	.raka-po-cards {
		flex: 0 0 auto;
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: clamp(8px, 0.8vw, 14px);
		margin-top: 14px;
	}

	.raka-po-card {
		background: var(--po-box);
		border-radius: var(--po-radius-box);
		padding: clamp(10px, 1vw, 16px);
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		gap: 8px;
		border: 1px solid var(--po-line);
		transition: transform 0.25s ease, box-shadow 0.25s ease;
	}

	.raka-po-card:hover {
		transform: translateY(-2px);
		box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.1);
	}

	.raka-po-card__title {
		font-size: clamp(11.5px, 0.85vw, 13.5px);
		font-weight: 700;
		line-height: 1.25;
		color: var(--po-ink);
		letter-spacing: -0.01em;
	}

	.raka-po-card__note {
		font-size: clamp(10.5px, 0.75vw, 12px);
		line-height: 1.35;
		color: var(--po-ink-soft);
	}

	.raka-po-stat__num {
		font-size: clamp(18px, 1.8vw, 26px);
		font-weight: 800;
		letter-spacing: -0.04em;
		line-height: 1;
		color: var(--po-ink);
	}

	.raka-po-stat__label {
		font-size: 10px;
		color: var(--po-ink-soft);
		margin-top: 2px;
		display: block;
		text-transform: uppercase;
		font-weight: 600;
	}

	/* Side Column (Right Video view & floating buttons) */
	.raka-po-side {
		position: relative;
		min-height: 0;
		padding: clamp(8px, 1vw, 18px);
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	.raka-po-side__top {
		position: absolute;
		top: clamp(8px, 1vw, 18px);
		right: clamp(8px, 1vw, 18px);
		display: flex;
		align-items: center;
		gap: clamp(8px, 0.7vw, 12px);
		z-index: 20;
	}

	.raka-po-pill-btn {
		width: clamp(40px, 3.2vw, 50px);
		height: clamp(40px, 3.2vw, 50px);
		border-radius: 50%;
		background: rgba(255, 255, 255, 0.92);
		backdrop-filter: blur(12px);
		-webkit-backdrop-filter: blur(12px);
		display: grid;
		place-items: center;
		box-shadow: 0 10px 26px -14px rgba(0, 0, 0, 0.35);
		color: #0d0f0f;
		border: 1px solid rgba(255, 255, 255, 0.4);
		transition: transform 0.2s ease, background-color 0.2s ease;
		cursor: pointer;
	}

	.raka-po-pill-btn:hover {
		transform: translateY(-2px) scale(1.05);
		background: #ffffff;
	}

	.raka-po-account-chip {
		display: flex;
		align-items: center;
		gap: 8px;
		background: rgba(255, 255, 255, 0.92);
		backdrop-filter: blur(12px);
		-webkit-backdrop-filter: blur(12px);
		border-radius: 999px;
		padding: 4px 6px 4px 12px;
		box-shadow: 0 10px 26px -14px rgba(0, 0, 0, 0.35);
		border: 1px solid rgba(255, 255, 255, 0.4);
		color: #0d0f0f;
		font-size: 13px;
		font-weight: 600;
		transition: transform 0.2s ease, background-color 0.2s ease;
		cursor: pointer;
	}

	.raka-po-account-chip:hover {
		transform: translateY(-2px);
		background: #ffffff;
	}

	.raka-po-arrow-fab {
		position: absolute;
		right: clamp(12px, 2vw, 36px);
		bottom: clamp(12px, 2vw, 36px);
		width: clamp(56px, 4.8vw, 84px);
		height: clamp(56px, 4.8vw, 84px);
		border-radius: 50%;
		background: #ffffff;
		color: #0d0f0f;
		display: grid;
		place-items: center;
		box-shadow: 0 18px 40px -20px rgba(0, 0, 0, 0.45);
		border: 1px solid rgba(255, 255, 255, 0.8);
		transition: transform 0.3s ease, background-color 0.2s ease;
		z-index: 20;
		cursor: pointer;
	}

	.raka-po-arrow-fab:hover {
		transform: rotate(45deg) scale(1.06);
		background: #ffffff;
	}

	/* Keyframes */
	@keyframes po-in-bg {
		from { opacity: 0; transform: scale(1.06); }
		to { opacity: 1; transform: scale(1); }
	}

	@keyframes po-in-fade {
		from { opacity: 0; }
		to { opacity: 1; }
	}

	@keyframes po-in-up {
		from { opacity: 0; transform: translate3d(0, 16px, 0); }
		to { opacity: 1; transform: translate3d(0, 0, 0); }
	}

	.raka-po-panel {
		animation: po-in-up 0.78s cubic-bezier(.16, 1, .3, 1) both 0.1s;
	}

	/* Responsive Breakpoints */
	@media (max-width: 1100px) {
		.raka-po-hero__inner {
			grid-template-columns: minmax(0, 1fr);
		}
		.raka-po-side {
			position: absolute;
			inset: clamp(10px, 1.2vw, 22px);
			pointer-events: none;
		}
		.raka-po-side__top {
			pointer-events: auto;
		}
		.raka-po-arrow-fab {
			display: none;
		}
		.raka-po-panel {
			padding-top: clamp(54px, 7vw, 76px);
		}
	}

	@media (max-width: 820px) {
		.raka-po-hero {
			min-height: calc(100svh - 64px);
			height: auto;
		}
		.raka-po-hero__scrim {
			background: linear-gradient(180deg, rgba(238, 242, 241, 0.95) 0%, rgba(238, 242, 241, 0.8) 50%, rgba(238, 242, 241, 0.5) 100%);
		}
		.dark .raka-po-hero__scrim {
			background: linear-gradient(180deg, rgba(8, 10, 16, 0.96) 0%, rgba(8, 10, 16, 0.85) 50%, rgba(8, 10, 16, 0.6) 100%);
		}
		.raka-po-hero__inner {
			padding: 10px;
			min-height: calc(100svh - 80px);
		}
		.raka-po-panel {
			padding: 14px;
			padding-top: 60px;
			border-radius: 22px;
		}
		.raka-po-headline {
			font-size: clamp(24px, 6.5vw, 32px);
		}
		.raka-po-cards {
			grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
			gap: 8px;
		}
		.raka-po-side__top {
			top: 16px;
			right: 16px;
		}
	}

	@media (max-width: 480px) {
		.raka-po-cards {
			grid-template-columns: 1fr;
		}
		.raka-po-account-chip span {
			display: none;
		}
	}
</style>

<!-- HERO SECTION (2-COLUMN MINDORA STYLE WITH LOCAL A9-AIR.MP4 BACKGROUND) -->
<section id="raka-preorder-hero-section" class="raka-po-hero">

	<!-- 1. Background Video Layer (Assets/images/a9-air.mp4) -->
	<video
		id="raka-preorder-bg-video"
		class="raka-po-hero__bg"
		autoplay
		muted
		loop
		playsinline
		preload="auto"
	>
		<source src="<?php echo esc_url( $video_url ); ?>" type="video/mp4">
	</video>

	<!-- 2. Ambient Scrim Layer for Text & Form Legibility -->
	<div id="raka-preorder-scrim" class="raka-po-hero__scrim"></div>

	<!-- 3. Two-Column Inner Container -->
	<div id="raka-preorder-inner" class="raka-po-hero__inner">

		<!-- COLUMN 1: White Boxed Container (Houses Raka Header Topbar & Pre-Order System) -->
		<div id="raka-preorder-panel" class="raka-po-panel">

			<!-- Panel Top Header Info -->
			<div id="raka-preorder-topbar" class="raka-po-topbar">
				<div id="raka-preorder-brand-tag" class="raka-po-brand-tag">
					<span id="raka-po-radar-indicator" class="raka-po-radar">
						<span class="raka-po-radar-ping"></span>
						<span class="raka-po-radar-dot"></span>
					</span>
					<span><?php esc_html_e( 'Pre-Order Tracking Hub', 'raka-preorder-theme' ); ?></span>
				</div>
				<div id="raka-preorder-connect-badge" class="raka-po-connect">
					<i class="ri-shield-check-fill text-emerald-500 text-sm"></i>
					<span><?php esc_html_e( 'Verified System', 'raka-preorder-theme' ); ?></span>
				</div>
			</div>

			<!-- Center Stage & Pre-Order Form Mount -->
			<div id="raka-preorder-stage" class="raka-po-stage">
				<h1 id="raka-preorder-headline" class="raka-po-headline">
					<b>Track</b> Your Gear<br/>
					with <b>Raka Pre-Order</b>
				</h1>
				<p id="raka-preorder-subtitle" class="raka-po-subtitle">
					<?php esc_html_e( 'Enter your order ID, citizen last 4 digits, or telephone number to check live allocation and shipment schedule.', 'raka-preorder-theme' ); ?>
				</p>

				<!-- Pre-Order Form Mount inside white box -->
				<div id="raka-preorder-form-mount" class="raka-po-form-wrapper">
					<?php echo do_shortcode( '[preorder_raka]' ); ?>
				</div>
			</div>

			<!-- Bottom Row: 3 Feature Cards -->
			<div id="raka-preorder-cards" class="raka-po-cards">
				<!-- Card 1 -->
				<div id="raka-po-card-1" class="raka-po-card">
					<div class="flex items-center gap-2">
						<i class="ri-award-fill text-amber-500 text-base"></i>
						<h3 class="raka-po-card__title"><?php esc_html_e( '100% Genuine', 'raka-preorder-theme' ); ?></h3>
					</div>
					<p class="raka-po-card__note"><?php esc_html_e( 'Official warranty & authentic gadget guaranteed.', 'raka-preorder-theme' ); ?></p>
				</div>

				<!-- Card 2 -->
				<div id="raka-po-card-2" class="raka-po-card">
					<div class="flex items-baseline justify-between">
						<span class="raka-po-stat__num">99.8%</span>
						<i class="ri-flashlight-fill text-red-500 text-sm"></i>
					</div>
					<span class="raka-po-stat__label"><?php esc_html_e( 'On-Time Batch Fulfillment', 'raka-preorder-theme' ); ?></span>
				</div>

				<!-- Card 3 -->
				<div id="raka-po-card-3" class="raka-po-card">
					<div class="flex items-center gap-2">
						<i class="ri-truck-fill text-blue-500 text-base"></i>
						<h3 class="raka-po-card__title"><?php esc_html_e( 'Express Courier', 'raka-preorder-theme' ); ?></h3>
					</div>
					<p class="raka-po-card__note"><?php esc_html_e( 'Insured domestic shipping upon stock arrival.', 'raka-preorder-theme' ); ?></p>
				</div>
			</div>

		</div>

		<!-- COLUMN 2: Floating Controls & Video Display -->
		<div id="raka-preorder-side" class="raka-po-side">

			<!-- Top Floating Controls -->
			<div id="raka-preorder-side-top" class="raka-po-side__top">
				<a
					id="raka-po-catalog-btn"
					href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>"
					class="raka-po-pill-btn"
					aria-label="<?php esc_attr_e( 'Explore Catalog', 'raka-preorder-theme' ); ?>"
					title="<?php esc_attr_e( 'Explore Catalog', 'raka-preorder-theme' ); ?>"
				>
					<i class="ri-store-2-line text-lg"></i>
				</a>

				<a
					id="raka-po-account-chip"
					href="<?php echo esc_url( home_url( '/account' ) ); ?>"
					class="raka-po-account-chip"
				>
					<i class="ri-user-smile-line text-base text-red-600 dark:text-red-400"></i>
					<span><?php esc_html_e( 'My Account', 'raka-preorder-theme' ); ?></span>
				</a>
			</div>

			<!-- Bottom Right Floating Explore FAB -->
			<a
				id="raka-po-arrow-fab"
				href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop' ) ); ?>"
				class="raka-po-arrow-fab"
				aria-label="<?php esc_attr_e( 'Explore Products', 'raka-preorder-theme' ); ?>"
				title="<?php esc_attr_e( 'Explore Products', 'raka-preorder-theme' ); ?>"
			>
				<svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M7 17L17 7M8 7h9v9"/>
				</svg>
			</a>

		</div>

	</div>

</section>

<?php
get_footer();
