<?php
/**
 * The template for displaying a single product.
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();


while ( have_posts() ) :
	the_post();
	global $product;
	if ( ! is_a( $product, 'WC_Product' ) ) {
		$product = wc_get_product( get_the_ID() );
	}

	$product_id         = $product->get_id();
	$product_title      = $product->get_name();
	$product_type       = $product->get_type();
	$product_price_html = $product->get_price_html();
	$is_variable        = $product->is_type( 'variable' );
	$image_id           = $product->get_image_id();
	$main_img_url       = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : wc_placeholder_img_src( 'full' );
	$gallery_ids        = $product->get_gallery_image_ids();
	$all_images         = array_unique( array_filter( array_merge( [ $image_id ], $gallery_ids ) ) );

	$brands     = wp_get_post_terms( $product_id, 'product_brand' );
	$brand_name = ( ! is_wp_error( $brands ) && ! empty( $brands ) ) ? $brands[0]->name : 'ATK Gear';

	$cats     = wp_get_post_terms( $product_id, 'product_cat' );
	$cat_name = ( ! is_wp_error( $cats ) && ! empty( $cats ) ) ? $cats[0]->name : 'Peripherals';
	$cat_link = ( ! is_wp_error( $cats ) && ! empty( $cats ) ) ? get_term_link( $cats[0] ) : home_url( '/shop' );

	$available_variations_json = '[]';
	if ( $is_variable ) {
		$variations_data = $product->get_available_variations();
		$available_variations_json = wp_json_encode( $variations_data );
	}
?>

<main id="single-product-main" class="min-h-screen pt-28 md:pt-36 pb-24 bg-cement dark:bg-obsidian text-black dark:text-white transition-colors duration-300">
	<div id="single-product-container" class="container">

		<!-- ── Breadcrumbs ── -->
		<?php raka_render_breadcrumbs( 'product-breadcrumbs', 'mb-8 md:mb-12' ); ?>

		<!-- ── Product Hero Grid (2 Columns) ── -->
		<div id="product-hero-grid" class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-start">

			<!-- Left Column: Gallery & Image Display -->
			<div id="product-gallery-col" class="lg:col-span-7 flex flex-col-reverse md:flex-row gap-4 sm:gap-6 sticky top-28">
				
				<!-- Thumbnails Strip -->
				<?php if ( count( $all_images ) > 1 ) : ?>
					<div id="product-thumbnails-list" class="flex md:flex-col gap-3 overflow-x-auto md:overflow-y-auto md:max-h-[560px] pb-2 md:pb-0 scrollbar-none">
						<?php
						$thumb_idx = 0;
						foreach ( $all_images as $thumb_id ) :
							$t_url = wp_get_attachment_image_url( $thumb_id, 'medium' );
							$f_url = wp_get_attachment_image_url( $thumb_id, 'full' );
							$active_cls = ( $thumb_idx === 0 ) ? 'border-black dark:border-white ring-1 ring-black dark:ring-white opacity-100' : 'border-neutral-200 dark:border-neutral-800 opacity-60 hover:opacity-100';
						?>
							<button
								id="thumb-btn-<?php echo esc_attr( (string) $thumb_id ); ?>"
								type="button"
								onclick="window.selectProductImage('<?php echo esc_url( $f_url ); ?>', this)"
								class="product-thumb-btn relative w-16 h-16 sm:w-20 sm:h-20 flex-shrink-0 rounded-lg overflow-hidden bg-neutral-100 dark:bg-neutral-900 border transition-all duration-300 focus:outline-none cursor-pointer <?php echo esc_attr( $active_cls ); ?>"
							>
								<img id="thumb-img-<?php echo esc_attr( (string) $thumb_id ); ?>" src="<?php echo esc_url( $t_url ); ?>" alt="<?php echo esc_attr( $product_title ); ?>" class="w-full h-full object-contain p-1" />
							</button>
						<?php
							$thumb_idx++;
						endforeach;
						?>
					</div>
				<?php endif; ?>

				<!-- Main Preview Image Stage -->
				<div id="product-main-stage" class="relative flex-1 rounded-2xl bg-neutral-100 dark:bg-neutral-900/80 border border-neutral-200/80 dark:border-neutral-800/80 p-6 sm:p-10 flex items-center justify-center min-h-[380px] sm:min-h-[480px] md:min-h-[560px] overflow-hidden group">
					<div id="product-stage-badge" class="absolute top-4 left-4 z-10 px-3 py-1 rounded-full text-[10px] font-mono tracking-widest uppercase bg-black/5 dark:bg-white/10 backdrop-blur-md text-neutral-600 dark:text-neutral-400 border border-black/10 dark:border-white/10">
						<span>VERIFIED GEAR</span>
					</div>

					<img
						id="product-active-image"
						src="<?php echo esc_url( $main_img_url ); ?>"
						alt="<?php echo esc_attr( $product_title ); ?>"
						class="w-full max-h-[460px] object-contain transition-all duration-500 group-hover:scale-105"
					/>
				</div>
			</div>

			<!-- Right Column: Product Info, Options & Purchase Flow -->
			<div id="product-details-col" class="lg:col-span-5 flex flex-col space-y-8">
				
				<!-- Meta: Brand & SKU -->
				<div id="product-meta-header" class="flex items-center justify-between">
					<?php
					$main_brand_logo = ( ! is_wp_error( $brands ) && ! empty( $brands ) ) ? raka_get_brand_logo_url( $brands[0] ) : '';
					if ( $main_brand_logo ) :
					?>
						<div id="product-brand-logo-wrap" class="flex items-center gap-2">
							<img id="product-brand-logo-img" src="<?php echo esc_url( $main_brand_logo ); ?>" alt="<?php echo esc_attr( $brand_name ); ?>" class="h-4 sm:h-5 w-auto max-w-[100px] object-contain filter dark:invert opacity-90" />
						</div>
					<?php else : ?>
						<span id="product-brand-tag" class="text-xs uppercase tracking-[0.25em] font-mono font-semibold text-neutral-500 dark:text-neutral-400">
							<?php echo esc_html( $brand_name ); ?>
						</span>
					<?php endif; ?>
					<span id="product-sku-tag" class="text-xs font-mono text-neutral-400 dark:text-neutral-500">
						SKU: <span id="product-dynamic-sku"><?php echo esc_html( $product->get_sku() ?: 'ATK-' . $product_id ); ?></span>
					</span>
				</div>

				<!-- Title & Price Block -->
				<div id="product-title-price-block" class="space-y-4">
					<h1 id="product-title-heading" class="text-3xl sm:text-4xl md:text-5xl font-black uppercase tracking-tight text-neutral-900 dark:text-white leading-[1.08] font-hn">
						<?php echo esc_html( $product_title ); ?>
					</h1>

					<div id="product-price-container" class="flex items-baseline gap-4 text-2xl sm:text-3xl font-light text-neutral-900 dark:text-white font-hn">
						<div id="product-active-price" class="font-normal text-neutral-900 dark:text-white">
							<?php echo wp_kses_post( $product_price_html ); ?>
						</div>
						<span id="product-tax-note" class="text-xs font-mono text-neutral-400 uppercase tracking-wider">Incl. VAT</span>
					</div>
				</div>

				<!-- Short Description -->
				<div id="product-short-desc" class="text-sm text-neutral-600 dark:text-neutral-300 font-light leading-relaxed border-t border-b border-black/10 dark:border-white/10 py-5 font-hn">
					<?php echo wp_kses_post( $product->get_short_description() ?: '<p>' . esc_html( $product_title ) . ' designed with flagship esports components, rapid response, and tournament precision.</p>' ); ?>
				</div>

				<!-- ── Interactive Variation Form (for Variable Products) ── -->
				<?php if ( $is_variable ) : ?>
					<form id="product-variation-form" class="space-y-6" data-variations="<?php echo esc_attr( $available_variations_json ); ?>">
						<?php
						$attributes = $product->get_variation_attributes();
						foreach ( $attributes as $attribute_name => $options ) :
							$attr_label   = wc_attribute_label( $attribute_name );
							$attr_slug    = sanitize_title( $attribute_name );
							$is_color_attr = ( strpos( strtolower( $attr_slug ), 'color' ) !== false || strpos( strtolower( $attr_slug ), 'colour' ) !== false );
						?>
							<div id="attr-group-<?php echo esc_attr( $attr_slug ); ?>" class="space-y-3">
								<div id="attr-label-wrap-<?php echo esc_attr( $attr_slug ); ?>" class="flex items-center justify-between text-xs uppercase tracking-[0.2em] font-mono">
									<span id="attr-title-<?php echo esc_attr( $attr_slug ); ?>" class="font-medium text-neutral-800 dark:text-neutral-200"><?php echo esc_html( $attr_label ); ?>:</span>
									<span id="attr-selected-val-<?php echo esc_attr( $attr_slug ); ?>" class="text-neutral-900 dark:text-neutral-100 font-semibold"><?php echo esc_html( $options[0] ?? '' ); ?></span>
								</div>

								<?php if ( $is_color_attr ) : ?>
									<!-- ── CIRCLE COLOR SWATCHES ── -->
									<div id="attr-options-<?php echo esc_attr( $attr_slug ); ?>" class="flex flex-wrap items-center gap-3.5 pt-1">
										<?php
										foreach ( $options as $opt_idx => $option_val ) :
											$is_active    = ( $opt_idx === 0 );
											$swatch_style = raka_get_swatch_css( $option_val );
											$active_ring  = $is_active
												? 'ring-2 ring-black dark:ring-white ring-offset-2 ring-offset-cement dark:ring-offset-obsidian scale-110 shadow-md'
												: 'opacity-85 hover:opacity-100 hover:scale-105';
										?>
											<button
												id="btn-opt-<?php echo esc_attr( $attr_slug . '-' . sanitize_title( $option_val ) ); ?>"
												type="button"
												data-attr="<?php echo esc_attr( $attr_slug ); ?>"
												data-val="<?php echo esc_attr( $option_val ); ?>"
												title="<?php echo esc_attr( $option_val ); ?>"
												onclick="window.onVariationOptionSelect(this)"
												class="variation-color-circle relative w-9 h-9 sm:w-10 sm:h-10 rounded-full transition-all duration-200 cursor-pointer focus:outline-none flex items-center justify-center p-0.5 <?php echo esc_attr( $active_ring ); ?>"
												style="<?php echo esc_attr( $swatch_style ); ?>"
											>
												<span id="dot-check-<?php echo esc_attr( $attr_slug . '-' . sanitize_title( $option_val ) ); ?>" class="swatch-check-icon <?php echo $is_active ? 'opacity-100' : 'opacity-0'; ?> transition-opacity text-sm <?php echo ( stripos( $swatch_style, '#F8F9FA' ) !== false || stripos( $swatch_style, '#FACC15' ) !== false ) ? 'text-black' : 'text-white'; ?>">
													<i id="icon-check-<?php echo esc_attr( $attr_slug . '-' . sanitize_title( $option_val ) ); ?>" class="ri-check-line font-bold"></i>
												</span>
											</button>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									<!-- ── STANDARD BUTTON PILLS FOR MODEL / SWITCHES ── -->
									<div id="attr-options-<?php echo esc_attr( $attr_slug ); ?>" class="flex flex-wrap gap-2.5">
										<?php
										foreach ( $options as $opt_idx => $option_val ) :
											$is_active = ( $opt_idx === 0 );
											$btn_cls = $is_active
												? 'bg-black text-white dark:bg-white dark:text-black border-black dark:border-white shadow-sm'
												: 'bg-transparent text-neutral-700 dark:text-neutral-300 border-neutral-300 dark:border-neutral-700 hover:border-black dark:hover:border-white';
										?>
											<button
												id="btn-opt-<?php echo esc_attr( $attr_slug . '-' . sanitize_title( $option_val ) ); ?>"
												type="button"
												data-attr="<?php echo esc_attr( $attr_slug ); ?>"
												data-val="<?php echo esc_attr( $option_val ); ?>"
												onclick="window.onVariationOptionSelect(this)"
												class="variation-pill-btn px-4 py-2.5 rounded-lg border text-xs sm:text-sm uppercase tracking-wider font-mono font-medium transition-all duration-200 cursor-pointer focus:outline-none <?php echo esc_attr( $btn_cls ); ?>"
											>
												<?php echo esc_html( $option_val ); ?>
											</button>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>

						<!-- Hidden standard WooCommerce form elements -->
						<input id="hidden-variation-id" type="hidden" name="variation_id" value="" />
						<input id="hidden-product-id" type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product_id ); ?>" />
					</form>
				<?php endif; ?>

				<!-- ── Purchase Action Block (Quantity + Add to Cart) ── -->
				<div id="product-action-block" class="space-y-4 pt-2">
					<div id="qty-cart-row" class="flex items-center gap-4">
						
						<!-- Quantity Selector -->
						<div id="product-qty-picker" class="flex items-center rounded-xl border border-neutral-300 dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-900 p-1">
							<button id="btn-qty-minus" type="button" onclick="window.changeProductQty(-1)" class="w-10 h-10 flex items-center justify-center rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-black/10 dark:hover:bg-white/10 transition-colors cursor-pointer focus:outline-none">
								<i id="icon-qty-minus" class="ri-subtract-line"></i>
							</button>
							<input id="product-qty-input" type="number" name="quantity" value="1" min="1" max="99" class="w-12 text-center font-mono font-bold bg-transparent text-sm text-neutral-900 dark:text-white focus:outline-none border-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
							<button id="btn-qty-plus" type="button" onclick="window.changeProductQty(1)" class="w-10 h-10 flex items-center justify-center rounded-lg text-neutral-700 dark:text-neutral-300 hover:bg-black/10 dark:hover:bg-white/10 transition-colors cursor-pointer focus:outline-none">
								<i id="icon-qty-plus" class="ri-add-line"></i>
							</button>
						</div>

						<!-- Add to Cart Button -->
						<button
							id="btn-add-to-cart"
							type="button"
							onclick="window.executeAddToCart()"
							class="flex-1 h-12 sm:h-14 px-8 rounded-xl bg-black text-white dark:bg-white dark:text-black font-semibold text-xs sm:text-sm uppercase tracking-[0.25em] flex items-center justify-center gap-3 transition-all duration-300 hover:opacity-90 hover:scale-[1.01] active:scale-[0.99] cursor-pointer shadow-lg font-hn"
						>
							<i id="icon-cart-btn" class="ri-shopping-bag-3-line text-lg"></i>
							<span id="btn-cart-text">ADD TO CART</span>
						</button>
					</div>

					<!-- Direct Pre-Order / Inquire Action -->
					<div id="product-secondary-actions" class="flex items-center gap-3">
						<a
							id="btn-direct-inquire"
							href="https://line.me"
							target="_blank"
							rel="noopener noreferrer"
							class="w-full h-11 rounded-xl border border-neutral-300 dark:border-neutral-700 hover:border-black dark:hover:border-white text-neutral-800 dark:text-neutral-200 text-xs uppercase tracking-[0.2em] font-medium flex items-center justify-center gap-2 transition-colors duration-200 font-mono"
						>
							<i id="icon-inquire" class="ri-chat-3-line text-base"></i>
							<span>INQUIRE VIA LINE / CONCIERGE</span>
						</a>
					</div>
				</div>

				<!-- ── Perks / Guarantees ── -->
				<div id="product-perks-grid" class="grid grid-cols-2 gap-4 pt-4 border-t border-black/10 dark:border-white/10 text-xs text-neutral-500 dark:text-neutral-400 font-mono">
					<div id="perk-1" class="flex items-center gap-2.5">
						<i id="perk-icon-1" class="ri-shield-check-line text-base text-neutral-800 dark:text-neutral-200"></i>
						<span>100% AUTHENTIC ATK GEAR</span>
					</div>
					<div id="perk-2" class="flex items-center gap-2.5">
						<i id="perk-icon-2" class="ri-truck-line text-base text-neutral-800 dark:text-neutral-200"></i>
						<span>FAST TRACK INSURED DELIVERY</span>
					</div>
					<div id="perk-3" class="flex items-center gap-2.5">
						<i id="perk-icon-3" class="ri-award-line text-base text-neutral-800 dark:text-neutral-200"></i>
						<span>1 YEAR OFFICIAL WARRANTY</span>
					</div>
					<div id="perk-4" class="flex items-center gap-2.5">
						<i id="perk-icon-4" class="ri-refresh-line text-base text-neutral-800 dark:text-neutral-200"></i>
						<span>7-DAY DEFECT REPLACEMENT</span>
					</div>
				</div>

				<!-- ── Expandable Details Accordions ── -->
				<div id="product-accordions-wrap" class="space-y-3 pt-6">
					
					<!-- Accordion 1: Description & Specs -->
					<details id="acc-specs" class="group rounded-xl border border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-100/50 dark:bg-neutral-900/40 p-4 transition-all" open>
						<summary id="sum-specs" class="flex items-center justify-between cursor-pointer list-none text-xs uppercase tracking-[0.2em] font-bold text-neutral-900 dark:text-white font-mono">
							<span>PRODUCT SPECIFICATIONS</span>
							<i id="icon-acc-specs" class="ri-arrow-down-s-line text-lg transition-transform group-open:rotate-180"></i>
						</summary>
						<div id="content-specs" class="pt-4 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed font-light font-hn space-y-4">
							<?php the_content(); ?>
						</div>
					</details>

					<!-- Accordion 2: Shipping & Support -->
					<details id="acc-shipping" class="group rounded-xl border border-neutral-200/80 dark:border-neutral-800/80 bg-neutral-100/50 dark:bg-neutral-900/40 p-4 transition-all">
						<summary id="sum-shipping" class="flex items-center justify-between cursor-pointer list-none text-xs uppercase tracking-[0.2em] font-bold text-neutral-900 dark:text-white font-mono">
							<span>DELIVERY & PRE-ORDER POLICIES</span>
							<i id="icon-acc-shipping" class="ri-arrow-down-s-line text-lg transition-transform group-open:rotate-180"></i>
						</summary>
						<div id="content-shipping" class="pt-4 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed font-light font-hn space-y-2">
							<p>All in-stock products ship within 24-48 hours via Kerry / Flash Express with insured tracking.</p>
							<p>For Pre-Order batch items, estimated arrival dates are calculated upon official factory dispatch and updated in real-time.</p>
						</div>
					</details>
				</div>

			</div>
		</div>

		<!-- ── Related Products Section (ATK Gear) ── -->
		<?php
		$related_products = wc_get_products( [
			'limit'   => 4,
			'exclude' => [ $product_id ],
			'tax_query' => [
				[
					'taxonomy' => 'product_brand',
					'field'    => 'term_id',
					'terms'    => ! is_wp_error( $brands ) && ! empty( $brands ) ? $brands[0]->term_id : 20,
				],
			],
		] );

		if ( ! empty( $related_products ) ) :
		?>
			<div id="related-products-section" class="mt-28 md:mt-36 pt-16 border-t border-black/10 dark:border-white/10">
				<div id="related-header-wrap" class="flex items-center justify-between mb-10">
					<div id="related-titles">
						<span id="related-sub" class="text-[10px] tracking-[0.3em] uppercase text-neutral-400 dark:text-neutral-500 font-mono">// CURATED PAIRINGS</span>
						<h2 id="related-main" class="text-2xl sm:text-3xl md:text-4xl font-black uppercase tracking-tight text-neutral-900 dark:text-white font-hn">MORE FROM <?php echo esc_html( $brand_name ); ?></h2>
					</div>
					<a id="related-view-all" href="<?php echo esc_url( home_url( '/shop' ) ); ?>" class="group inline-flex items-center gap-2 text-xs uppercase tracking-[0.2em] font-semibold text-neutral-800 dark:text-neutral-200 hover:opacity-50 transition-opacity font-mono">
						<span>VIEW CATALOGUE</span>
						<i id="related-arrow" class="ri-arrow-right-line text-sm transition-transform group-hover:translate-x-1"></i>
					</a>
				</div>

				<div id="related-products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
					<?php
					foreach ( $related_products as $rel_p ) :
						raka_render_product_card( $rel_p );
					endforeach;
					?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</main>

<!-- ── Client Script: Interactive Variations & Gallery Switcher ── -->
<script id="product-interaction-script">
(function initProductSingle() {
	const mainImg = document.getElementById('product-active-image');
	const form = document.getElementById('product-variation-form');
	const priceEl = document.getElementById('product-active-price');
	const skuEl = document.getElementById('product-dynamic-sku');
	const hiddenVarId = document.getElementById('hidden-variation-id');
	const qtyInput = document.getElementById('product-qty-input');

	let variationsData = [];
	if (form) {
		try {
			variationsData = JSON.parse(form.getAttribute('data-variations') || '[]');
		} catch (e) {
			variationsData = [];
		}
	}

	// 1. Image selection from thumbnail strip
	window.selectProductImage = function(imgUrl, clickedBtn) {
		if (mainImg) {
			mainImg.style.opacity = '0.3';
			setTimeout(() => {
				mainImg.src = imgUrl;
				mainImg.style.opacity = '1';
			}, 150);
		}
		document.querySelectorAll('.product-thumb-btn').forEach(btn => {
			btn.classList.remove('border-black', 'dark:border-white', 'ring-1', 'ring-black', 'dark:ring-white', 'opacity-100');
			btn.classList.add('border-neutral-200', 'dark:border-neutral-800', 'opacity-60');
		});
		if (clickedBtn) {
			clickedBtn.classList.remove('border-neutral-200', 'dark:border-neutral-800', 'opacity-60');
			clickedBtn.classList.add('border-black', 'dark:border-white', 'ring-1', 'ring-black', 'dark:ring-white', 'opacity-100');
		}
	};

	// 2. Quantity Change Handler
	window.changeProductQty = function(delta) {
		if (!qtyInput) return;
		let val = parseInt(qtyInput.value, 10) || 1;
		val = Math.max(1, Math.min(99, val + delta));
		qtyInput.value = val;
	};

	// 3. Variation Option Select Handler (Circles & Pills)
	window.onVariationOptionSelect = function(btn) {
		const attrSlug = btn.getAttribute('data-attr');
		const val = btn.getAttribute('data-val');
		const parentGroup = document.getElementById('attr-options-' + attrSlug);

		if (parentGroup) {
			// Handle circle color swatches
			const circles = parentGroup.querySelectorAll('.variation-color-circle');
			if (circles.length > 0) {
				circles.forEach(b => {
					b.classList.remove('ring-2', 'ring-black', 'dark:ring-white', 'ring-offset-2', 'ring-offset-cement', 'dark:ring-offset-obsidian', 'scale-110', 'shadow-md');
					b.classList.add('opacity-85');
					const check = b.querySelector('.swatch-check-icon');
					if (check) check.classList.replace('opacity-100', 'opacity-0');
				});

				btn.classList.remove('opacity-85');
				btn.classList.add('ring-2', 'ring-black', 'dark:ring-white', 'ring-offset-2', 'ring-offset-cement', 'dark:ring-offset-obsidian', 'scale-110', 'shadow-md');
				const activeCheck = btn.querySelector('.swatch-check-icon');
				if (activeCheck) activeCheck.classList.replace('opacity-0', 'opacity-100');
			}

			// Handle regular text pill buttons
			const pills = parentGroup.querySelectorAll('.variation-pill-btn');
			if (pills.length > 0) {
				pills.forEach(b => {
					b.classList.remove('bg-black', 'text-white', 'dark:bg-white', 'dark:text-black', 'border-black', 'dark:border-white', 'shadow-sm');
					b.classList.add('bg-transparent', 'text-neutral-700', 'dark:text-neutral-300', 'border-neutral-300', 'dark:border-neutral-700');
				});
				btn.classList.remove('bg-transparent', 'text-neutral-700', 'dark:text-neutral-300', 'border-neutral-300', 'dark:border-neutral-700');
				btn.classList.add('bg-black', 'text-white', 'dark:bg-white', 'dark:text-black', 'border-black', 'dark:border-white', 'shadow-sm');
			}
		}

		// Update Selected Attribute Value Label in Real-time
		const labelEl = document.getElementById('attr-selected-val-' + attrSlug);
		if (labelEl) labelEl.textContent = val;

		// Match variation
		matchActiveVariation();
	};

	function matchActiveVariation() {
		if (!form || !variationsData.length) return;

		// Collect currently selected attributes from active circles or pills
		const selected = {};
		form.querySelectorAll('.variation-color-circle.ring-2, .variation-pill-btn.bg-black, .variation-pill-btn.dark\\:bg-white').forEach(btn => {
			const attr = btn.getAttribute('data-attr');
			const val = btn.getAttribute('data-val');
			selected['attribute_' + attr] = val;
		});

		// Find matching variation
		const matched = variationsData.find(v => {
			return Object.keys(v.attributes).every(k => {
				const requiredVal = v.attributes[k];
				if (!requiredVal) return true; // Any value matches
				const cleanReq = requiredVal.trim();
				const curVal = (selected[k] || selected['attribute_' + k] || '').trim();
				return curVal === cleanReq;
			});
		}) || variationsData[0];

		if (matched) {
			if (hiddenVarId) hiddenVarId.value = matched.variation_id;
			if (priceEl && matched.price_html) {
				priceEl.innerHTML = matched.price_html;
			}
			if (skuEl && matched.sku) {
				skuEl.textContent = matched.sku;
			}
			if (matched.image && matched.image.src && mainImg) {
				mainImg.src = matched.image.src;
			}
		}
	}

	// 4. Add To Cart Execution
	window.executeAddToCart = function() {
		const cartBtn = document.getElementById('btn-add-to-cart');
		const cartTxt = document.getElementById('btn-cart-text');
		const cartIcon = document.getElementById('icon-cart-btn');

		const pId = <?php echo (int) $product_id; ?>;
		const varId = hiddenVarId ? parseInt(hiddenVarId.value, 10) : 0;
		const qty = qtyInput ? parseInt(qtyInput.value, 10) : 1;

		if (cartBtn) {
			cartBtn.disabled = true;
			if (cartTxt) cartTxt.textContent = 'ADDING...';
			if (cartIcon) cartIcon.className = 'ri-loader-4-line animate-spin text-lg';
		}

		const formData = new FormData();
		formData.append('product_id', pId);
		formData.append('quantity', qty);
		if (varId) {
			formData.append('variation_id', varId);
		}

		fetch('<?php echo esc_url( home_url( '/?wc-ajax=add_to_cart' ) ); ?>', {
			method: 'POST',
			body: formData
		})
		.then(res => res.json())
		.then(data => {
			if (cartBtn) {
				cartBtn.disabled = false;
				if (cartTxt) cartTxt.textContent = 'ADDED TO BAG!';
				if (cartIcon) cartIcon.className = 'ri-check-line text-lg';
				setTimeout(() => {
					if (cartTxt) cartTxt.textContent = 'ADD TO CART';
					if (cartIcon) cartIcon.className = 'ri-shopping-bag-3-line text-lg';
				}, 2000);
			}
		})
		.catch(err => {
			window.location.href = '<?php echo esc_url( wc_get_cart_url() ); ?>?add-to-cart=' + (varId || pId) + '&quantity=' + qty;
		});
	};

	// Initial match on page load
	matchActiveVariation();
})();
</script>

<?php
endwhile;

get_footer();
