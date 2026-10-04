<?php
/**
 * Single Pre-order React Mounting Template.
 *
 * @package preorder-raka
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$post_id = get_the_ID();
?>

<!-- SINGLE PRE-ORDER DETAIL SECTION (CLEAN THEME BACKGROUND) -->
<main id="raka-preorder-single-main" class="w-full min-h-[calc(100vh-80px)] flex items-center justify-center bg-neutral-50 dark:bg-neutral-950 py-12 sm:py-16 md:py-20 px-4 sm:px-6 md:px-8 transition-colors duration-300">

	<div id="raka-preorder-single-wrapper" class="w-full max-w-4xl mx-auto flex flex-col items-center text-center">

		<!-- Mount PreOrderApp with data-post-id -->
		<div id="preorder-raka-single-app" class="w-full max-w-2xl mx-auto text-left rounded-3xl shadow-xl shadow-black/5 dark:shadow-black/40 overflow-hidden bg-white dark:bg-neutral-900 border border-neutral-200/80 dark:border-neutral-800" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<div id="preorder-raka-loading-box" class="flex flex-col items-center justify-center py-24 text-neutral-400 dark:text-neutral-500">
				<div id="preorder-raka-loading-spinner" class="relative w-12 h-12 animate-spin mb-4" aria-hidden="true">
					<i class="ri-loader-4-line text-4xl text-neutral-900 dark:text-white"></i>
				</div>
				<span id="preorder-raka-loading-text" class="text-sm font-medium tracking-wider">กำลังโหลดข้อมูลสถานะพรีออเดอร์...</span>
			</div>
		</div>

	</div>

</main>

<?php
get_footer();
