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
$total_cards         = count( $hero_cards );
// Snappy fast scroll: ~35vh per card instead of 100vh
$hero_section_height = max( 220, ( $total_cards * 35 ) + 80 ) . 'vh';
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
			class="absolute inset-0 w-full h-full object-cover gpu-accel pointer-events-none"
			style="opacity: 0; will-change: transform, opacity;"
		></video>

		<!-- 2. Canvas Layer (Ultra Smooth WebCodecs Frame Blitter) -->
		<canvas
			id="hero-canvas"
			width="1280"
			height="720"
			class="absolute inset-0 w-full h-full object-cover gpu-accel pointer-events-none transition-opacity duration-300"
			style="opacity: 0; will-change: transform, opacity;"
		></canvas>

		<!-- 3. Hero Overlay Image (Figma asset: Pure white wings & gold angel) -->
		<div id="hero-overlay-png-wrap" class="absolute inset-0 z-30 pointer-events-none gpu-accel transition-opacity duration-150">
			<img
				id="hero-overlay-png"
				alt=""
				class="w-full h-full object-cover"
				src="<?php echo esc_url( $hero_overlay_url ); ?>"
			/>
		</div>

		<!-- 4. Hero Content (Section 1) -->
		<div id="hero-section-1" class="absolute inset-0 z-10 flex flex-col pointer-events-none gpu-accel">
			<div class="flex justify-center mt-12 md:mt-14">
				<h1 id="hero-main-title" class="text-[20vw] md:text-[22vw] font-black tracking-[-0.04em] leading-[0.75] text-black dark:text-white uppercase select-none font-hn theme-transition">
					<?php echo esc_html( $hero_main_title ); ?>
				</h1>
			</div>
			<div class="absolute bottom-6 left-6 md:left-10 md:bottom-8">
				<p class="text-[9px] md:text-[10px] tracking-[0.15em] uppercase leading-[1.8] text-black/80 dark:text-white/80 font-hn theme-transition">
					<?php echo nl2br( esc_html( $hero_caption ) ); ?>
				</p>
			</div>
		</div>

		<!-- 5. Dynamic Repeater Product Cards (Scroll Popup Overlay Cards) -->
		<?php foreach ( $hero_cards as $idx => $card ) :
			$pos = $card['position'] ?? 'left';
			// Generous top clearance so cards never collide with or slide under the fixed header
			$pos_classes = 'items-start justify-start p-6 md:p-12 lg:p-16 pt-28 md:pt-36 lg:pt-40';
			if ( 'right' === $pos ) {
				$pos_classes = 'items-start justify-end p-6 md:p-12 lg:p-16 pt-28 md:pt-36 lg:pt-40';
			} elseif ( 'center' === $pos ) {
				$pos_classes = 'items-center justify-center p-6 md:p-12 pt-28 md:pt-36';
			} elseif ( 'bottom_left' === $pos ) {
				$pos_classes = 'items-end justify-start p-6 md:p-12 lg:p-16 pb-16 md:pb-24';
			} elseif ( 'bottom_right' === $pos ) {
				$pos_classes = 'items-end justify-end p-6 md:p-12 lg:p-16 pb-16 md:pb-24';
			}
		?>
			<?php if ( ! empty( $card['show_custom_text'] ) ) : ?>
				<!-- Custom Statement / Atmospheric Text Card Mode -->
				<div id="hero-card-slide-<?php echo esc_attr( $idx ); ?>" class="hero-card-slide hero-sub-section absolute inset-0 z-20 gpu-accel flex <?php echo esc_attr( $pos_classes ); ?> pointer-events-none" style="opacity: 0; visibility: hidden; transform: translate3d(0, 20px, 0);">
					<<?php echo ! empty( $card['url'] ) && '#' !== $card['url'] ? 'a href="' . esc_url( $card['url'] ) . '"' : 'div'; ?> class="pointer-events-auto block max-w-md md:max-w-lg w-full p-6 md:p-8 rounded-3xl bg-white/90 dark:bg-black/85 backdrop-blur-2xl border border-black/10 dark:border-white/15 shadow-2xl theme-transition group hover:border-black/30 dark:hover:border-white/30 transition-all duration-300">
						<?php if ( ! empty( $card['tag'] ) ) : ?>
							<div class="text-[11px] tracking-[0.25em] uppercase text-black/50 dark:text-white/50 mb-3 font-hn"><?php echo esc_html( $card['tag'] ); ?></div>
						<?php endif; ?>
						<?php if ( ! empty( $card['title'] ) ) : ?>
							<h3 class="text-xl md:text-2xl font-black text-black dark:text-white mb-3 font-hn group-hover:translate-x-1 transition-transform leading-tight"><?php echo esc_html( $card['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $card['description'] ) ) : ?>
							<p class="text-sm md:text-base leading-[1.7] text-black/80 dark:text-white/85 font-light font-hn">
								<?php echo esc_html( $card['description'] ); ?>
							</p>
						<?php endif; ?>
						<?php if ( ! empty( $card['url'] ) && '#' !== $card['url'] ) : ?>
							<div class="mt-5 inline-flex items-center gap-2 text-xs uppercase tracking-widest text-black/60 dark:text-white/60 group-hover:text-black dark:group-hover:text-white font-hn">
								<span><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Explore', 'raka-preorder-theme' ) ); ?></span> <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
							</div>
						<?php endif; ?>
					</<?php echo ! empty( $card['url'] ) && '#' !== $card['url'] ? 'a' : 'div'; ?>>
				</div>
			<?php else : ?>
				<!-- WooCommerce Product Card Mode with Direct Add to Cart Action -->
				<div id="hero-card-slide-<?php echo esc_attr( $idx ); ?>" class="hero-card-slide hero-sub-section absolute inset-0 z-20 gpu-accel flex <?php echo esc_attr( $pos_classes ); ?> pointer-events-none" style="opacity: 0; visibility: hidden; transform: translate3d(0, 20px, 0);">
					<div class="pointer-events-auto block max-w-md md:max-w-lg w-full p-6 md:p-7 rounded-3xl bg-white/90 dark:bg-black/85 backdrop-blur-2xl border border-black/10 dark:border-white/15 shadow-2xl theme-transition group hover:border-black/30 dark:hover:border-white/30 transition-all duration-300">
						<!-- Header: Tag + Price -->
						<div class="flex items-center justify-between gap-3 mb-4">
							<?php if ( ! empty( $card['tag'] ) ) : ?>
								<span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] md:text-[11px] font-bold tracking-widest uppercase bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 font-hn">
									<?php echo esc_html( $card['tag'] ); ?>
								</span>
							<?php endif; ?>
							<?php if ( ! empty( $card['price_html'] ) ) : ?>
								<div class="text-sm md:text-base font-bold text-neutral-900 dark:text-white font-hn">
									<?php echo esc_html( $card['price_html'] ); ?>
								</div>
							<?php endif; ?>
						</div>

						<!-- Card Content: Image & Text -->
						<div class="flex items-start gap-4 md:gap-5">
							<?php if ( ! empty( $card['image_url'] ) ) : ?>
								<a href="<?php echo esc_url( $card['url'] ); ?>" class="w-20 h-20 md:w-24 md:h-24 rounded-2xl overflow-hidden bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10 shrink-0 group-hover:scale-105 transition-transform duration-300 block">
									<img src="<?php echo esc_url( $card['image_url'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>" class="w-full h-full object-cover" />
								</a>
							<?php endif; ?>
							<div class="flex-1 min-w-0">
								<?php if ( ! empty( $card['title'] ) ) : ?>
									<h3 class="text-lg md:text-xl font-bold text-black dark:text-white font-hn group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors leading-snug line-clamp-2">
										<a href="<?php echo esc_url( $card['url'] ); ?>">
											<?php echo esc_html( $card['title'] ); ?>
										</a>
									</h3>
								<?php endif; ?>
								<?php if ( ! empty( $card['description'] ) ) : ?>
									<p class="text-xs md:text-sm leading-relaxed text-black/70 dark:text-white/75 font-light font-hn mt-2 line-clamp-3">
										<?php echo esc_html( $card['description'] ); ?>
									</p>
								<?php endif; ?>
							</div>
						</div>

						<!-- Card CTA Action Footer: Direct Add to Cart + Details Link -->
						<div class="mt-5 pt-4 border-t border-black/10 dark:border-white/10 flex items-center justify-between gap-3">
							<?php if ( ! empty( $card['product_id'] ) ) : ?>
								<button
									type="button"
									onclick="window.rakaCardAddToCart(<?php echo esc_attr( $card['product_id'] ); ?>, this)"
									class="raka-hero-add-to-cart-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 text-xs font-bold uppercase tracking-wider font-hn shadow-md hover:shadow-lg transition-all cursor-pointer"
								>
									<i class="ri-shopping-bag-3-line text-sm flex-shrink-0"></i>
									<span><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Pre-Order Now', 'raka-preorder-theme' ) ); ?></span>
								</button>
							<?php else : ?>
								<a
									href="<?php echo esc_url( $card['url'] ); ?>"
									class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 text-xs font-bold uppercase tracking-wider font-hn shadow-md hover:shadow-lg transition-all"
								>
									<span><?php echo esc_html( ! empty( $card['button_text'] ) ? $card['button_text'] : __( 'Pre-Order Now', 'raka-preorder-theme' ) ); ?></span>
									<span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
								</a>
							<?php endif; ?>

							<?php if ( ! empty( $card['url'] ) && '#' !== $card['url'] ) : ?>
								<a href="<?php echo esc_url( $card['url'] ); ?>" class="text-xs font-semibold text-black/60 dark:text-white/60 hover:text-black dark:hover:text-white inline-flex items-center gap-1 font-hn transition-colors">
									<span><?php esc_html_e( 'View Details', 'raka-preorder-theme' ); ?></span>
									<span>&rarr;</span>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>

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

		function applyPositions(prog, scrollPx) {
			// 1. Hero Main Title & Overlay PNG (Intro Slot)
			const totalSlots = Math.max(2, totalCards + 1);
			const introSlotLen = 1 / totalSlots;

			if (heroSec1) {
				const introProg = Math.min(1, prog / (introSlotLen * 0.7));
				const heroOpacity = Math.max(0, 1 - introProg);
				heroSec1.style.opacity = heroOpacity.toFixed(3);
				heroSec1.style.transform = `translate3d(0, ${(introProg * -25).toFixed(1)}px, 0)`;
				heroSec1.style.visibility = heroOpacity > 0.01 ? 'visible' : 'hidden';
			}

			if (overlayPng) {
				const pngProg = Math.min(1, prog / (introSlotLen * 0.9));
				const pngOpacity = Math.max(0, 1 - pngProg);
				overlayPng.style.opacity = pngOpacity.toFixed(3);
				overlayPng.style.visibility = pngOpacity > 0.01 ? 'visible' : 'hidden';
			}

			// 2. Sequential Cards Scrubbing (Fast, Snappy Single-Card Transitions)
			if (totalCards > 0) {
				const cardProgressStart = introSlotLen * 0.85;
				const cardProgressRange = 1 - cardProgressStart;
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
						sec.style.transform = 'translate3d(0, 15px, 0)';
						return;
					}

					// Local progress within this card's dedicated slot [0.0 -> 1.0]
					const local = (prog - mySlotStart) / cardSlotSize;

					let opacity = 0;
					let translateY = 0;

					if (local < 0.15) {
						// Fast enter phase (0.0 -> 0.15)
						const enterT = local / 0.15;
						opacity = enterT;
						translateY = (1 - enterT) * 12;
					} else if (local > 0.85) {
						// Fast exit phase (0.85 -> 1.0)
						const exitT = (local - 0.85) / 0.15;
						opacity = 1 - exitT;
						translateY = exitT * -12;
					} else {
						// Active steady phase (0.15 -> 0.85)
						opacity = 1;
						translateY = 0;
					}

					sec.style.visibility = 'visible';
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
			const scrollPx = Math.max(0, scrollY - start);
			applyPositions(progress, scrollPx);
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
