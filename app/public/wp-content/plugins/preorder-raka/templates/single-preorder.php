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

<main id="preorder-raka-primary-main" class="site-main transition:colors duration:300ms min-h:100vh pt:40 pb:80 bg:slate-50 dark:bg:slate-950">
	<div id="preorder-raka-container-div" class="mx:auto px:16 max-w:1024">
		<div id="preorder-raka-single-app" class="w:full" data-post-id="<?php echo esc_attr( (string) $post_id ); ?>">
			<!-- React single app mounts here -->
			<div id="preorder-raka-loading-box" class="flex flex:col ai:center jc:center py:96 color:slate-500 dark:color:slate-400">
				<div id="preorder-raka-loading-spinner" class="relative w:64 h:64 animate:spin mb:24" aria-hidden="true">
					<?php for ( $dot_index = 0; $dot_index < 12; $dot_index++ ) : ?>
						<span
							id="preorder-raka-loading-dot-<?php echo esc_attr( (string) $dot_index ); ?>"
							class="absolute left:50% top:50% display:block w:12 h:12 r:9999 bg:#e11d48"
							style="<?php echo esc_attr( 'opacity: ' . ( 0.25 + ( $dot_index / 12 ) * 0.75 ) . '; transform: translate(-50%, -50%) rotate(' . ( $dot_index * 30 ) . 'deg) translateY(-28px);' ); ?>"
						></span>
					<?php endfor; ?>
				</div>
				<span id="preorder-raka-loading-text" class="f:14 font:medium letter-spacing:1">กำลังโหลดข้อมูลสถานะพรีออเดอร์...</span>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
