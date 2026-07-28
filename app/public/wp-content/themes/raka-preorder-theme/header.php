<!DOCTYPE html>
<html id="raka-theme-html" class="dark" <?php language_attributes(); ?>>
<head id="raka-theme-head">
	<meta id="raka-meta-charset" class="meta-tag" charset="<?php bloginfo( 'charset' ); ?>">
	<meta id="raka-meta-viewport" class="meta-tag" name="viewport" content="width=device-width, initial-scale=1.0">
	<title id="raka-theme-title"><?php wp_title( '|', true, 'right' ); ?></title>
	<?php wp_head(); ?>

	<!-- Apply saved theme immediately to prevent FOUC -->
	<script id="raka-theme-init">
		(function() {
			var stored = localStorage.getItem('preorder_raka_theme');
			if (stored === 'light') {
				document.documentElement.classList.remove('dark');
				document.documentElement.classList.add('light');
			} else {
				document.documentElement.classList.add('dark');
				document.documentElement.classList.remove('light');
			}
		})();
	</script>
</head>
<body id="raka-theme-body">
	<?php wp_body_open(); ?>
	<div id="raka-site-wrapper">

		<header id="raka-site-header">
			<div id="raka-header-container">

				<!-- Logo -->
				<a id="raka-logo-link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<div id="raka-header-logo">R</div>
					<div id="raka-header-logo-text-block">
						<span id="raka-logo-text">RAKA GADGET</span>
						<span id="raka-logo-subtext">PRE-ORDER TRACKER</span>
					</div>
				</a>

				<!-- Right side -->
				<div id="raka-header-right">
					<span id="raka-nav-badge">ระบบเช็คสถานะพรีออเดอร์</span>

					<!-- Dark/Light toggle — syncs with React plugin via class on <html> -->
					<button id="raka-theme-toggle-btn" aria-label="Toggle dark/light mode">
						<svg id="raka-icon-sun" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
						</svg>
						<svg id="raka-icon-moon" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
						</svg>
					</button>
				</div>
			</div>
		</header>

		<script id="raka-theme-toggle-script">
			(function() {
				// Update icon based on current html class
				function syncIcon() {
					var dark  = document.documentElement.classList.contains('dark');
					var sun   = document.getElementById('raka-icon-sun');
					var moon  = document.getElementById('raka-icon-moon');
					if (!sun || !moon) return;
					sun.style.display  = dark ? 'block' : 'none';
					moon.style.display = dark ? 'none'  : 'block';
				}

				// Toggle handler — simply flip class on <html>
				// React's MutationObserver will pick up the class change and update its state
				var btn = document.getElementById('raka-theme-toggle-btn');
				if (btn) {
					btn.addEventListener('click', function() {
						var html   = document.documentElement;
						var isDark = html.classList.contains('dark');
						if (isDark) {
							html.classList.remove('dark');
							html.classList.add('light');
							localStorage.setItem('preorder_raka_theme', 'light');
						} else {
							html.classList.remove('light');
							html.classList.add('dark');
							localStorage.setItem('preorder_raka_theme', 'dark');
						}
						syncIcon();
					});
				}

				// Sync icon on page load
				syncIcon();

				// Keep icon in sync when React toggles the class from inside
				var obs = new MutationObserver(syncIcon);
				obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
			})();
		</script>

