import { useState, useEffect } from '@wordpress/element';

export default function LoyaltyRewardSelector( { config } ) {
	const apiUrl = config?.apiUrl || '/wp-json/account/v1';
	const nonce = config?.nonce || window.rakaAccountData?.nonce || '';

	const [ data, setData ] = useState( null );
	const [ loading, setLoading ] = useState( true );
	const [ actionLoading, setActionLoading ] = useState( false );
	const [ selectedId, setSelectedId ] = useState( null );
	const [ message, setMessage ] = useState( null );

	const fetchOverview = async () => {
		try {
			setLoading( true );
			const res = await fetch( `${ apiUrl }/rewards/overview`, {
				headers: { 'X-WP-Nonce': nonce },
			} );
			if ( res.ok ) {
				const result = await res.json();
				setData( result );
			}
		} catch ( err ) {
			console.error( 'Error fetching reward catalog for cart:', err );
		} finally {
			setLoading( false );
		}
	};

	useEffect( () => {
		fetchOverview();
	}, [] );

	const handleApply = async ( productId ) => {
		try {
			setActionLoading( true );
			setSelectedId( productId );
			setMessage( null );

			const res = await fetch( `${ apiUrl }/cart/apply-reward`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce,
				},
				body: JSON.stringify( { product_id: productId } ),
			} );

			const resData = await res.json();
			if ( ! res.ok || ! resData.success ) {
				throw new Error( resData.message || 'ไม่สามารถแลกของรางวัลได้' );
			}

			setMessage( { text: resData.message, type: 'success' } );

			// Trigger WooCommerce cart update if available
			if ( window.jQuery ) {
				window.jQuery( document.body ).trigger( 'wc_update_cart' );
				window.jQuery( document.body ).trigger( 'updated_cart_totals' );
				window.jQuery( document.body ).trigger( 'update_checkout' );
			} else {
				window.location.reload();
			}
		} catch ( err ) {
			setMessage( { text: err.message, type: 'error' } );
		} finally {
			setActionLoading( false );
			setSelectedId( null );
		}
	};

	const handleRemove = async () => {
		try {
			setActionLoading( true );
			setMessage( null );

			const res = await fetch( `${ apiUrl }/cart/remove-reward`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce,
				},
			} );

			const resData = await res.json();
			if ( ! res.ok || ! resData.success ) {
				throw new Error( resData.message || 'ไม่สามารถนำของรางวัลออกได้' );
			}

			setMessage( { text: resData.message, type: 'success' } );

			if ( window.jQuery ) {
				window.jQuery( document.body ).trigger( 'wc_update_cart' );
				window.jQuery( document.body ).trigger( 'updated_cart_totals' );
				window.jQuery( document.body ).trigger( 'update_checkout' );
			} else {
				window.location.reload();
			}
		} catch ( err ) {
			setMessage( { text: err.message, type: 'error' } );
		} finally {
			setActionLoading( false );
		}
	};

	if ( loading || ! data ) {
		return null;
	}

	const account = data.account || {};
	const catalog = data.catalog || [];
	const available = account.available || 0;

	if ( catalog.length === 0 ) {
		return null;
	}

	return (
		<div id="raka-cart-loyalty-box" className="p-4 sm:p-5 rounded-2xl bg-neutral-50 dark:bg-neutral-900 border border-black/5 dark:border-white/5 space-y-3 font-hn my-4">
			<div id="raka-cart-loyalty-header" className="flex items-center justify-between">
				<div id="raka-cart-loyalty-title-wrap" className="flex items-center gap-2">
					<i id="raka-cart-loyalty-icon" className="ri-copper-coin-line text-lg text-red-600"></i>
					<h4 id="raka-cart-loyalty-title" className="text-xs sm:text-sm font-bold text-neutral-950 dark:text-white">
						สิทธิพิเศษแลกของรางวัล (100 แต้ม)
					</h4>
				</div>
				<span id="raka-cart-loyalty-bal-tag" className="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-red-600/10 text-red-600 dark:text-red-400">
					คุณมี { Number( available ).toLocaleString() } แต้ม
				</span>
			</div>

			{ message && (
				<div
					id="raka-cart-loyalty-alert"
					className={ `p-2.5 rounded-xl text-xs font-medium flex items-center gap-2 ${
						message.type === 'error'
							? 'bg-red-500/10 text-red-600 border border-red-500/20'
							: 'bg-green-500/10 text-green-600 border border-green-500/20'
					}` }
				>
					<i id="raka-cart-alert-icon" className={ message.type === 'error' ? 'ri-error-warning-line' : 'ri-checkbox-circle-line' }></i>
					<span id="raka-cart-alert-text">{ message.text }</span>
				</div>
			) }

			<div id="raka-cart-rewards-grid" className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
				{ catalog.map( ( item ) => (
					<div
						key={ item.id }
						id={ `raka-cart-reward-${ item.id }` }
						className="p-2.5 rounded-xl bg-white dark:bg-neutral-800 border border-black/5 dark:border-white/5 flex items-center justify-between gap-2 shadow-xs"
					>
						<div id={ `raka-cart-reward-left-${ item.id }` } className="flex items-center gap-2.5 min-w-0">
							<img
								id={ `raka-cart-reward-img-${ item.id }` }
								src={ item.image_url }
								alt={ item.name }
								className="w-11 h-11 rounded-lg object-cover bg-neutral-100 dark:bg-neutral-700 shrink-0"
							/>
							<div id={ `raka-cart-reward-meta-${ item.id }` } className="min-w-0 space-y-0.5">
								<p id={ `raka-cart-reward-name-${ item.id }` } className="text-xs font-bold text-neutral-900 dark:text-white truncate">
									{ item.name }
								</p>
								<p id={ `raka-cart-reward-pts-${ item.id }` } className="text-[11px] text-red-600 dark:text-red-400 font-semibold">
									ฟรี (100 แต้ม)
								</p>
							</div>
						</div>

						<button
							type="button"
							id={ `raka-cart-apply-btn-${ item.id }` }
							disabled={ ! item.redeemable || actionLoading }
							onClick={ () => handleApply( item.id ) }
							className={ `px-3 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 cursor-pointer ${
								item.redeemable
									? 'bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-950'
									: 'bg-neutral-100 dark:bg-neutral-700 text-neutral-400 cursor-not-allowed'
							}` }
						>
							{ actionLoading && selectedId === item.id ? '...' : ( item.redeemable ? 'แลก' : 'แต้มไม่พอ' ) }
						</button>
					</div>
				) ) }
			</div>
		</div>
	);
}
