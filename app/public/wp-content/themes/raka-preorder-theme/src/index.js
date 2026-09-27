import { render, createRoot } from '@wordpress/element';
import ThemeSettingsApp from './admin/ThemeSettingsApp';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'raka-theme-settings-app' );
	if ( container ) {
		if ( typeof createRoot === 'function' ) {
			createRoot( container ).render( <ThemeSettingsApp /> );
		} else if ( typeof render === 'function' ) {
			render( <ThemeSettingsApp />, container );
		}
	}
} );
