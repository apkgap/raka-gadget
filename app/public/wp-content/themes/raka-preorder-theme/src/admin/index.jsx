import { render } from '@wordpress/element';
import ThemeSettingsApp from './ThemeSettingsApp';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'raka-theme-settings-app' );
	if ( container ) {
		render( <ThemeSettingsApp />, container );
	}
} );
