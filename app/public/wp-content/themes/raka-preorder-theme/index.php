<?php
/**
 * Main index template — mounts the React Preorder App.
 *
 * @package raka-preorder-theme
 */

get_header();
?>
<main id="raka-site-main">
	<div id="raka-home-container" style="width:100%;max-width:1024px;">
		<div id="preorder-raka-single-app" class="w:full" data-post-id="">
			<!-- React app mounts here -->
			<div id="preorder-raka-loading-box" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:96px 0;color:#64748b;">
				<div id="preorder-raka-loading-spinner" style="position:relative;width:48px;height:48px;margin-bottom:20px;animation:spin 1s linear infinite;" aria-hidden="true">
					<?php for ( $dot_index = 0; $dot_index < 12; $dot_index++ ) : ?>
						<span
							id="preorder-raka-loading-dot-<?php echo esc_attr( (string) $dot_index ); ?>"
							style="<?php echo esc_attr(
								'position:absolute;left:50%;top:50%;display:block;width:6px;height:6px;border-radius:9999px;background:#3b82f6;' .
								'opacity:' . ( 0.2 + ( $dot_index / 12 ) * 0.8 ) . ';' .
								'transform:translate(-50%,-50%) rotate(' . ( $dot_index * 30 ) . 'deg) translateY(-20px);'
							); ?>"
						></span>
					<?php endfor; ?>
				</div>
				<span id="preorder-raka-loading-text" style="font-size:13px;font-weight:500;letter-spacing:0.5px;opacity:0.6;">กำลังโหลดระบบพรีออเดอร์...</span>
			</div>
		</div>
	</div>
</main>
<style id="raka-spin-keyframe">
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
<?php
get_footer();
