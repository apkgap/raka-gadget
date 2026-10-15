<?php
/**
 * Single Pre-order Detail Template.
 *
 * @package preorder-raka
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$post_id = get_the_ID();
?>

<!-- SINGLE PRE-ORDER DETAIL SECTION (EXPANDED SPACIOUS CONTAINER) -->
<main id="raka-preorder-single-main" class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center bg-neutral-50 dark:bg-neutral-950 py-8 sm:py-12 md:py-16 px-3 sm:px-6 md:px-8 transition-colors duration-300">

	<div id="raka-preorder-single-wrapper" class="w-full max-w-6xl mx-auto flex flex-col items-center">

		<!-- Mount PreOrderApp with data-post-id -->
		<div id="preorder-raka-single-app" class="w-full max-w-5xl mx-auto text-left rounded-3xl overflow-hidden border border-neutral-200/80 dark:border-neutral-800 transition-all duration-300" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<div id="preorder-raka-loading-box" class="flex flex-col items-center justify-center py-28 text-neutral-400 dark:text-neutral-500 font-sans">
				<div id="preorder-raka-loading-spinner" class="relative w-12 h-12 animate-spin mb-4" aria-hidden="true">
					<i class="ri-loader-4-line text-4xl text-red-600 dark:text-red-500"></i>
				</div>
				<span id="preorder-raka-loading-text" class="text-sm font-medium tracking-wider"><?php esc_html_e( 'กำลังโหลดข้อมูลสถานะพรีออเดอร์...', 'raka-preorder-theme' ); ?></span>
			</div>
		</div>

	</div>

</main>

<?php
get_footer();
