import { useState, useEffect, useCallback } from '@wordpress/element';

export function useLoyalty( config ) {
	const apiUrl = config?.apiUrl || '/wp-json/account/v1';
	const nonce = config?.nonce || '';

	const [ data, setData ] = useState( null );
	const [ loading, setLoading ] = useState( true );
	const [ historyFilter, setHistoryFilter ] = useState( 'all' );
	const [ historyItems, setHistoryItems ] = useState( [] );
	const [ hasMoreHistory, setHasMoreHistory ] = useState( false );
	const [ nextCursor, setNextCursor ] = useState( null );
	const [ historyLoading, setHistoryLoading ] = useState( false );
	const [ actionLoading, setActionLoading ] = useState( false );
	const [ error, setError ] = useState( null );

	const fetchOverview = useCallback( async ( filter = 'all', cursor = null, append = false ) => {
		try {
			if ( append ) {
				setHistoryLoading( true );
			} else {
				setLoading( true );
			}
			setError( null );

			let url = `${ apiUrl }/rewards/overview?type=${ filter }&limit=10`;
			if ( cursor ) {
				url += `&cursor=${ cursor }`;
			}

			const res = await fetch( url, {
				headers: { 'X-WP-Nonce': nonce },
			} );

			if ( ! res.ok ) {
				throw new Error( 'Failed to fetch rewards overview' );
			}

			const result = await res.json();

			if ( append ) {
				setHistoryItems( ( prev ) => [ ...prev, ...( result.history?.items || [] ) ] );
			} else {
				setData( result );
				setHistoryItems( result.history?.items || [] );
			}

			setHasMoreHistory( !! result.history?.has_more );
			setNextCursor( result.history?.next_cursor || null );
		} catch ( err ) {
			console.error( 'Loyalty fetch error:', err );
			setError( err.message );
		} finally {
			setLoading( false );
			setHistoryLoading( false );
		}
	}, [ apiUrl, nonce ] );

	useEffect( () => {
		fetchOverview( historyFilter );
	}, [ historyFilter, fetchOverview ] );

	const loadMoreHistory = useCallback( () => {
		if ( nextCursor && ! historyLoading ) {
			fetchOverview( historyFilter, nextCursor, true );
		}
	}, [ nextCursor, historyLoading, historyFilter, fetchOverview ] );

	const applyReward = useCallback( async ( productId ) => {
		try {
			setActionLoading( true );
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
				throw new Error( resData.message || 'แลกของรางวัลไม่สำเร็จ' );
			}

			// Revalidate overview
			await fetchOverview( historyFilter );
			return resData;
		} finally {
			setActionLoading( false );
		}
	}, [ apiUrl, nonce, historyFilter, fetchOverview ] );

	const removeReward = useCallback( async () => {
		try {
			setActionLoading( true );
			const res = await fetch( `${ apiUrl }/cart/remove-reward`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': nonce,
				},
			} );

			const resData = await res.json();
			if ( ! res.ok || ! resData.success ) {
				throw new Error( resData.message || 'นำของรางวัลออกไม่สำเร็จ' );
			}

			await fetchOverview( historyFilter );
			return resData;
		} finally {
			setActionLoading( false );
		}
	}, [ apiUrl, nonce, historyFilter, fetchOverview ] );

	return {
		data,
		loading,
		historyFilter,
		setHistoryFilter,
		historyItems,
		hasMoreHistory,
		historyLoading,
		loadMoreHistory,
		applyReward,
		removeReward,
		actionLoading,
		error,
		refresh: () => fetchOverview( historyFilter ),
	};
}
