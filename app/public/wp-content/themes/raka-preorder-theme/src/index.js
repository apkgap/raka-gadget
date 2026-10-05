import { render, createRoot } from '@wordpress/element';
import ThemeSettingsApp from './admin/ThemeSettingsApp';
import AccountApp from './account/AccountApp';
import LoyaltyRewardSelector from './account/components/LoyaltyRewardSelector';

document.addEventListener( 'DOMContentLoaded', () => {
	// Admin Theme Settings Root
	const adminContainer = document.getElementById( 'raka-theme-settings-app' );
	if ( adminContainer ) {
		if ( typeof createRoot === 'function' ) {
			createRoot( adminContainer ).render( <ThemeSettingsApp /> );
		} else if ( typeof render === 'function' ) {
			render( <ThemeSettingsApp />, adminContainer );
		}
	}

	// Frontend My Account React SPA Root
	const accountContainer = document.getElementById( 'raka-account-root' );
	if ( accountContainer ) {
		if ( typeof createRoot === 'function' ) {
			createRoot( accountContainer ).render( <AccountApp /> );
		} else if ( typeof render === 'function' ) {
			render( <AccountApp />, accountContainer );
		}
	}

	// Cart / Checkout Loyalty Reward Selector Root
	const rewardSelectorContainer = document.getElementById( 'raka-cart-loyalty-root' );
	if ( rewardSelectorContainer ) {
		if ( typeof createRoot === 'function' ) {
			createRoot( rewardSelectorContainer ).render( <LoyaltyRewardSelector config={ window.rakaAccountData } /> );
		} else if ( typeof render === 'function' ) {
			render( <LoyaltyRewardSelector config={ window.rakaAccountData } />, rewardSelectorContainer );
		}
	}
} );
