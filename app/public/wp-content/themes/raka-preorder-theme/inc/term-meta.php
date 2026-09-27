<?php
/**
 * Custom Term Meta for Taxonomies (RemixIcon & Media Icon Selection)
 *
 * @package raka-preorder-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * List of supported taxonomies for custom term icons
 */
function raka_get_icon_supported_taxonomies(): array {
	return (array) apply_filters( 'raka_icon_supported_taxonomies', [
		'product_cat',
		'product_brand',
		'product_tag',
		'category',
		'post_tag',
	] );
}

/**
 * Enqueue scripts & styles on taxonomy management pages in WP Admin
 */
function raka_term_meta_admin_assets( string $hook ): void {
	if ( ! in_array( $hook, [ 'edit-tags.php', 'term.php' ], true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->taxonomy, raka_get_icon_supported_taxonomies(), true ) ) {
		return;
	}

	// Enqueue WordPress Media Uploader
	wp_enqueue_media();

	// Enqueue RemixIcon for admin preview
	wp_enqueue_style(
		'remixicon-admin',
		'https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css',
		[],
		'4.2.0'
	);

	// Inline CSS & JS for the term icon picker
	wp_add_inline_style(
		'remixicon-admin',
		'
		.raka-term-icon-wrap {
			margin-top: 10px;
			max-width: 540px;
			background: #f8fafc;
			border: 1px solid #e2e8f0;
			border-radius: 8px;
			padding: 14px;
		}
		.raka-icon-type-toggle {
			display: flex;
			gap: 16px;
			margin-bottom: 12px;
			padding-bottom: 10px;
			border-bottom: 1px solid #e2e8f0;
		}
		.raka-icon-type-toggle label {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			font-weight: 600;
			cursor: pointer;
		}
		.raka-preview-box {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 44px;
			height: 44px;
			background: #ffffff;
			border: 1px solid #cbd5e1;
			border-radius: 8px;
			margin-right: 10px;
			font-size: 22px;
			color: #0f172a;
			overflow: hidden;
			vertical-align: middle;
		}
		.raka-preview-box img {
			max-width: 100%;
			max-height: 100%;
			object-fit: contain;
		}
		.raka-preset-icons {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
			margin-top: 10px;
			max-height: 120px;
			overflow-y: auto;
			padding: 4px;
			background: #ffffff;
			border: 1px solid #cbd5e1;
			border-radius: 6px;
		}
		.raka-preset-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 32px;
			height: 32px;
			border-radius: 4px;
			border: 1px solid #e2e8f0;
			background: #ffffff;
			cursor: pointer;
			font-size: 16px;
			color: #334155;
			transition: all 0.15s ease;
		}
		.raka-preset-btn:hover {
			background: #0f172a;
			color: #ffffff;
			border-color: #0f172a;
			transform: scale(1.08);
		}
		'
	);
}
add_action( 'admin_enqueue_scripts', 'raka_term_meta_admin_assets' );

/**
 * Render fields on "Add New Term" screen
 */
function raka_taxonomy_add_icon_fields( string $taxonomy ): void {
	wp_nonce_field( 'raka_term_icon_save', 'raka_term_icon_nonce' );
	?>
	<div id="raka-term-icon-add-group" class="form-field term-icon-wrap">
		<label for="raka_term_icon_type"><?php esc_html_e( 'Term Icon (RemixIcon / Media)', 'raka-preorder-theme' ); ?></label>
		
		<div id="raka-term-icon-panel" class="raka-term-icon-wrap">
			<!-- Icon Type Selector -->
			<div id="raka-icon-type-radios" class="raka-icon-type-toggle">
				<label id="raka-label-type-none">
					<input id="raka_icon_type_none" type="radio" name="raka_icon_type" value="none" checked="checked" onchange="window.rakaToggleIconFields(this.value)" />
					<?php esc_html_e( 'None', 'raka-preorder-theme' ); ?>
				</label>
				<label id="raka-label-type-remixicon">
					<input id="raka_icon_type_remixicon" type="radio" name="raka_icon_type" value="remixicon" onchange="window.rakaToggleIconFields(this.value)" />
					<?php esc_html_e( 'RemixIcon Class', 'raka-preorder-theme' ); ?>
				</label>
				<label id="raka-label-type-media">
					<input id="raka_icon_type_media" type="radio" name="raka_icon_type" value="media" onchange="window.rakaToggleIconFields(this.value)" />
					<?php esc_html_e( 'Media Upload', 'raka-preorder-theme' ); ?>
				</label>
			</div>

			<!-- RemixIcon Section -->
			<div id="raka-remixicon-section" style="display: none;">
				<div id="raka-remixicon-input-wrap" style="display: flex; align-items: center; gap: 8px;">
					<div id="raka-remixicon-preview" class="raka-preview-box">
						<i id="raka-remixicon-preview-i" class="ri-apps-2-line"></i>
					</div>
					<input
						id="raka_remixicon_input"
						type="text"
						name="raka_remixicon"
						value=""
						placeholder="e.g. ri-keyboard-line"
						style="flex: 1;"
						oninput="window.rakaUpdateRemixIconPreview(this.value)"
					/>
				</div>
				<p class="description" style="margin-top: 6px;">
					<?php esc_html_e( 'Type any RemixIcon class (e.g. ri-mouse-line) or pick from presets below:', 'raka-preorder-theme' ); ?>
				</p>
				<?php raka_render_preset_icon_picker(); ?>
			</div>

			<!-- Media Upload Section -->
			<div id="raka-media-section" style="display: none;">
				<div id="raka-media-input-wrap" style="display: flex; align-items: center; gap: 8px;">
					<div id="raka-media-preview" class="raka-preview-box">
						<img id="raka-media-preview-img" src="" alt="" style="display: none;" />
						<i id="raka-media-placeholder-i" class="ri-image-line" style="color: #94a3b8;"></i>
					</div>
					<input id="raka_media_icon_id" type="hidden" name="raka_media_icon_id" value="" />
					<button id="raka-btn-media-choose" type="button" class="button" onclick="window.rakaChooseMediaIcon()"><?php esc_html_e( 'Select from Media', 'raka-preorder-theme' ); ?></button>
					<button id="raka-btn-media-remove" type="button" class="button" style="display: none; color: #dc2626;" onclick="window.rakaRemoveMediaIcon()"><?php esc_html_e( 'Remove', 'raka-preorder-theme' ); ?></button>
				</div>
				<p class="description" style="margin-top: 6px;">
					<?php esc_html_e( 'Upload or choose an SVG / PNG / WEBP icon from the WordPress Media Library.', 'raka-preorder-theme' ); ?>
				</p>
			</div>
		</div>
	</div>
	<?php
	raka_render_term_icon_js();
}

/**
 * Render fields on "Edit Term" screen
 */
function raka_taxonomy_edit_icon_fields( WP_Term $term, string $taxonomy ): void {
	wp_nonce_field( 'raka_term_icon_save', 'raka_term_icon_nonce' );

	$icon_type     = (string) get_term_meta( $term->term_id, 'raka_icon_type', true );
	$remixicon     = (string) get_term_meta( $term->term_id, 'raka_remixicon', true );
	$media_icon_id = (int) get_term_meta( $term->term_id, 'raka_media_icon_id', true );
	$media_url     = $media_icon_id ? wp_get_attachment_image_url( $media_icon_id, 'thumbnail' ) : '';

	if ( empty( $icon_type ) ) {
		if ( ! empty( $remixicon ) ) {
			$icon_type = 'remixicon';
		} elseif ( $media_icon_id > 0 ) {
			$icon_type = 'media';
		} else {
			$icon_type = 'none';
		}
	}
	?>
	<tr id="raka-term-icon-row" class="form-field term-icon-wrap">
		<th scope="row" valign="top">
			<label for="raka_term_icon_type"><?php esc_html_e( 'Term Icon', 'raka-preorder-theme' ); ?></label>
		</th>
		<td>
			<div id="raka-term-icon-panel" class="raka-term-icon-wrap">
				<!-- Icon Type Selector -->
				<div id="raka-icon-type-radios" class="raka-icon-type-toggle">
					<label id="raka-label-type-none">
						<input id="raka_icon_type_none" type="radio" name="raka_icon_type" value="none" <?php checked( $icon_type, 'none' ); ?> onchange="window.rakaToggleIconFields(this.value)" />
						<?php esc_html_e( 'None', 'raka-preorder-theme' ); ?>
					</label>
					<label id="raka-label-type-remixicon">
						<input id="raka_icon_type_remixicon" type="radio" name="raka_icon_type" value="remixicon" <?php checked( $icon_type, 'remixicon' ); ?> onchange="window.rakaToggleIconFields(this.value)" />
						<?php esc_html_e( 'RemixIcon Class', 'raka-preorder-theme' ); ?>
					</label>
					<label id="raka-label-type-media">
						<input id="raka_icon_type_media" type="radio" name="raka_icon_type" value="media" <?php checked( $icon_type, 'media' ); ?> onchange="window.rakaToggleIconFields(this.value)" />
						<?php esc_html_e( 'Media Upload', 'raka-preorder-theme' ); ?>
					</label>
				</div>

				<!-- RemixIcon Section -->
				<div id="raka-remixicon-section" style="<?php echo $icon_type === 'remixicon' ? '' : 'display: none;'; ?>">
					<div id="raka-remixicon-input-wrap" style="display: flex; align-items: center; gap: 8px;">
						<div id="raka-remixicon-preview" class="raka-preview-box">
							<i id="raka-remixicon-preview-i" class="<?php echo esc_attr( $remixicon ?: 'ri-apps-2-line' ); ?>"></i>
						</div>
						<input
							id="raka_remixicon_input"
							type="text"
							name="raka_remixicon"
							value="<?php echo esc_attr( $remixicon ); ?>"
							placeholder="e.g. ri-keyboard-line"
							style="flex: 1;"
							oninput="window.rakaUpdateRemixIconPreview(this.value)"
						/>
					</div>
					<p class="description" style="margin-top: 6px;">
						<?php esc_html_e( 'Type any RemixIcon class (e.g. ri-mouse-line) or pick from presets below:', 'raka-preorder-theme' ); ?>
					</p>
					<?php raka_render_preset_icon_picker(); ?>
				</div>

				<!-- Media Upload Section -->
				<div id="raka-media-section" style="<?php echo $icon_type === 'media' ? '' : 'display: none;'; ?>">
					<div id="raka-media-input-wrap" style="display: flex; align-items: center; gap: 8px;">
						<div id="raka-media-preview" class="raka-preview-box">
							<img id="raka-media-preview-img" src="<?php echo esc_url( $media_url ); ?>" alt="" style="<?php echo $media_url ? '' : 'display: none;'; ?>" />
							<i id="raka-media-placeholder-i" class="ri-image-line" style="<?php echo $media_url ? 'display: none;' : 'color: #94a3b8;'; ?>"></i>
						</div>
						<input id="raka_media_icon_id" type="hidden" name="raka_media_icon_id" value="<?php echo esc_attr( (string) $media_icon_id ); ?>" />
						<button id="raka-btn-media-choose" type="button" class="button" onclick="window.rakaChooseMediaIcon()"><?php esc_html_e( 'Select from Media', 'raka-preorder-theme' ); ?></button>
						<button id="raka-btn-media-remove" type="button" class="button" style="<?php echo $media_icon_id ? 'color: #dc2626;' : 'display: none; color: #dc2626;'; ?>" onclick="window.rakaRemoveMediaIcon()"><?php esc_html_e( 'Remove', 'raka-preorder-theme' ); ?></button>
					</div>
					<p class="description" style="margin-top: 6px;">
						<?php esc_html_e( 'Upload or choose an SVG / PNG / WEBP icon from the WordPress Media Library.', 'raka-preorder-theme' ); ?>
					</p>
				</div>
			</div>
		</td>
	</tr>
	<?php
	raka_render_term_icon_js();
}

/**
 * Render Preset Quick Pick Icons
 */
function raka_render_preset_icon_picker(): void {
	$presets = [
		'ri-mouse-line'          => 'Mouse',
		'ri-keyboard-line'       => 'Keyboard',
		'ri-headphone-line'      => 'Headset',
		'ri-gamepad-line'        => 'Controller',
		'ri-computer-line'       => 'Desktop',
		'ri-tv-line'             => 'Monitor',
		'ri-cpu-line'            => 'Hardware',
		'ri-sound-module-line'   => 'Audio Module',
		'ri-plug-line'           => 'Cable / Adapter',
		'ri-battery-charge-line' => 'Battery',
		'ri-bluetooth-line'      => 'Wireless',
		'ri-wifi-line'           => 'High Speed Dongle',
		'ri-box-3-line'          => 'Collectible Box',
		'ri-archive-line'        => 'Archive',
		'ri-flashlight-line'     => 'Quick Pro',
		'ri-fire-line'           => 'Hot Release',
		'ri-sparkling-line'      => 'Exclusive Special',
		'ri-star-line'           => 'Featured',
		'ri-shield-flash-line'   => 'Tournament Grade',
		'ri-price-tag-3-line'    => 'Accessories',
		'ri-apps-2-line'         => 'All Categories',
		'ri-macbook-line'        => 'Laptop',
		'ri-smartphone-line'     => 'Mobile',
		'ri-vip-diamond-line'    => 'Ultra Rare',
	];
	?>
	<div id="raka-preset-icons-grid" class="raka-preset-icons">
		<?php foreach ( $presets as $cls => $title ) : ?>
			<button
				id="btn-preset-<?php echo esc_attr( $cls ); ?>"
				type="button"
				class="raka-preset-btn"
				title="<?php echo esc_attr( $title . ' (' . $cls . ')' ); ?>"
				onclick="window.rakaSetPresetRemixIcon('<?php echo esc_attr( $cls ); ?>')"
			>
				<i id="ico-preset-<?php echo esc_attr( $cls ); ?>" class="<?php echo esc_attr( $cls ); ?>"></i>
			</button>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render JavaScript controller for Term Icon UI
 */
function raka_render_term_icon_js(): void {
	static $rendered = false;
	if ( $rendered ) {
		return;
	}
	$rendered = true;
	?>
	<script id="raka-term-icon-manager-js">
		window.rakaToggleIconFields = function(type) {
			var remixSec = document.getElementById('raka-remixicon-section');
			var mediaSec = document.getElementById('raka-media-section');
			if (remixSec) remixSec.style.display = (type === 'remixicon') ? 'block' : 'none';
			if (mediaSec) mediaSec.style.display = (type === 'media') ? 'block' : 'none';
		};

		window.rakaUpdateRemixIconPreview = function(val) {
			var iconEl = document.getElementById('raka-remixicon-preview-i');
			if (iconEl) {
				iconEl.className = val.trim() ? val.trim() : 'ri-apps-2-line';
			}
		};

		window.rakaSetPresetRemixIcon = function(cls) {
			var input = document.getElementById('raka_remixicon_input');
			if (input) {
				input.value = cls;
				window.rakaUpdateRemixIconPreview(cls);
			}
		};

		window.rakaChooseMediaIcon = function() {
			if (typeof wp === 'undefined' || !wp.media) return;
			var frame = wp.media({
				title: 'Select Term Icon',
				button: { text: 'Use this Icon' },
				multiple: false
			});
			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				var idInput = document.getElementById('raka_media_icon_id');
				var previewImg = document.getElementById('raka-media-preview-img');
				var placeholderI = document.getElementById('raka-media-placeholder-i');
				var removeBtn = document.getElementById('raka-btn-media-remove');

				if (idInput) idInput.value = attachment.id;
				if (previewImg) {
					var url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
					previewImg.src = url;
					previewImg.style.display = 'block';
				}
				if (placeholderI) placeholderI.style.display = 'none';
				if (removeBtn) removeBtn.style.display = 'inline-block';
			});
			frame.open();
		};

		window.rakaRemoveMediaIcon = function() {
			var idInput = document.getElementById('raka_media_icon_id');
			var previewImg = document.getElementById('raka-media-preview-img');
			var placeholderI = document.getElementById('raka-media-placeholder-i');
			var removeBtn = document.getElementById('raka-btn-media-remove');

			if (idInput) idInput.value = '';
			if (previewImg) {
				previewImg.src = '';
				previewImg.style.display = 'none';
			}
			if (placeholderI) placeholderI.style.display = 'block';
			if (removeBtn) removeBtn.style.display = 'none';
		};
	</script>
	<?php
}

/**
 * Save term icon metadata
 */
function raka_save_term_icon_fields( int $term_id, int $tt_id = 0 ): void {
	if ( ! isset( $_POST['raka_term_icon_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['raka_term_icon_nonce'] ) ), 'raka_term_icon_save' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_categories' ) && ! current_user_can( 'edit_terms' ) ) {
		return;
	}

	// 1. Icon Type
	if ( isset( $_POST['raka_icon_type'] ) ) {
		$icon_type = sanitize_key( wp_unslash( $_POST['raka_icon_type'] ) );
		if ( in_array( $icon_type, [ 'none', 'remixicon', 'media' ], true ) ) {
			update_term_meta( $term_id, 'raka_icon_type', $icon_type );
		}
	}

	// 2. RemixIcon Class
	if ( isset( $_POST['raka_remixicon'] ) ) {
		$remixicon = sanitize_text_field( wp_unslash( $_POST['raka_remixicon'] ) );
		update_term_meta( $term_id, 'raka_remixicon', $remixicon );
	}

	// 3. Media Icon ID
	if ( isset( $_POST['raka_media_icon_id'] ) ) {
		$media_icon_id = absint( $_POST['raka_media_icon_id'] );
		update_term_meta( $term_id, 'raka_media_icon_id', $media_icon_id );
	}
}

/**
 * Register Taxonomy Hooks
 */
function raka_init_taxonomy_icon_meta(): void {
	$taxonomies = raka_get_icon_supported_taxonomies();
	foreach ( $taxonomies as $tax ) {
		add_action( "{$tax}_add_form_fields", 'raka_taxonomy_add_icon_fields', 10, 1 );
		add_action( "{$tax}_edit_form_fields", 'raka_taxonomy_edit_icon_fields', 10, 2 );
		add_action( "created_{$tax}", 'raka_save_term_icon_fields', 10, 2 );
		add_action( "edited_{$tax}", 'raka_save_term_icon_fields', 10, 2 );

		// Add custom Icon column in admin table
		add_filter( "manage_edit-{$tax}_columns", 'raka_add_term_icon_column' );
		add_filter( "manage_{$tax}_custom_column", 'raka_render_term_icon_column', 10, 3 );
	}
}
add_action( 'init', 'raka_init_taxonomy_icon_meta' );

/**
 * Add "Icon" column in admin terms table
 */
function raka_add_term_icon_column( array $columns ): array {
	$new_columns = [];
	foreach ( $columns as $key => $title ) {
		if ( $key === 'name' ) {
			$new_columns['raka_term_icon'] = __( 'Icon', 'raka-preorder-theme' );
		}
		$new_columns[ $key ] = $title;
	}
	if ( ! isset( $new_columns['raka_term_icon'] ) ) {
		$new_columns['raka_term_icon'] = __( 'Icon', 'raka-preorder-theme' );
	}
	return $new_columns;
}

/**
 * Render Icon in admin terms table column
 */
function raka_render_term_icon_column( string $content, string $column_name, int $term_id ): string {
	if ( $column_name !== 'raka_term_icon' ) {
		return $content;
	}

	$icon_html = raka_get_term_icon_html( $term_id, 'font-size: 18px; vertical-align: middle; display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: #f1f5f9; border-radius: 4px;' );
	return ! empty( $icon_html ) ? $icon_html : '<span style="color:#cbd5e1;">&mdash;</span>';
}

/**
 * Helper: Get Term Icon Data Array
 */
function raka_get_term_icon_data( $term_id ): array {
	if ( is_a( $term_id, 'WP_Term' ) ) {
		$term_id = $term_id->term_id;
	}
	$term_id = absint( $term_id );
	if ( ! $term_id ) {
		return [ 'type' => 'none', 'remixicon' => '', 'media_id' => 0, 'media_url' => '' ];
	}

	$type      = (string) get_term_meta( $term_id, 'raka_icon_type', true );
	$remixicon = (string) get_term_meta( $term_id, 'raka_remixicon', true );
	$media_id  = (int) get_term_meta( $term_id, 'raka_media_icon_id', true );
	$media_url = $media_id ? (string) wp_get_attachment_image_url( $media_id, 'thumbnail' ) : '';

	if ( empty( $type ) || $type === 'none' ) {
		if ( ! empty( $remixicon ) ) {
			$type = 'remixicon';
		} elseif ( $media_id > 0 ) {
			$type = 'media';
		} else {
			$type = 'none';
		}
	}

	return [
		'type'      => $type,
		'remixicon' => $remixicon,
		'media_id'  => $media_id,
		'media_url' => $media_url,
	];
}

/**
 * Helper: Render Term Icon HTML
 */
function raka_get_term_icon_html( $term_id, string $extra_style_or_class = '', int $size = 20 ): string {
	$data = raka_get_term_icon_data( $term_id );
	if ( $data['type'] === 'remixicon' && ! empty( $data['remixicon'] ) ) {
		return sprintf(
			'<i id="term-icon-%1$d" class="%2$s" style="%3$s"></i>',
			absint( is_a( $term_id, 'WP_Term' ) ? $term_id->term_id : $term_id ),
			esc_attr( $data['remixicon'] ),
			esc_attr( $extra_style_or_class )
		);
	} elseif ( $data['type'] === 'media' && ! empty( $data['media_url'] ) ) {
		return sprintf(
			'<img id="term-icon-%1$d" src="%2$s" alt="" style="width:%3$dpx;height:%3$dpx;object-fit:contain;%4$s" loading="lazy" />',
			absint( is_a( $term_id, 'WP_Term' ) ? $term_id->term_id : $term_id ),
			esc_url( $data['media_url'] ),
			absint( $size ),
			esc_attr( $extra_style_or_class )
		);
	}
	return '';
}
