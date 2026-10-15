import { useState, useEffect } from '@wordpress/element';
import {
	Button,
	Card,
	CardHeader,
	CardBody,
	TextControl,
	ToggleControl,
	Spinner,
	Notice,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function LoyaltySettingsTab( {
	options,
	onOptionsChange,
	wcProducts = [],
	notice,
	setNotice,
} ) {
	const loyaltyOpts = options.loyalty || {
		enabled: true,
		spend_per_point: 100,
		tier_threshold_silver: 500,
		tier_threshold_gold: 2000,
		preorder_points_multiplier: 1.0,
		lot_expiry_days: 365,
		reward_product_ids: [],
	};

	const [ subTab, setSubTab ] = useState( 'settings' );

	// Admin API state
	const [ users, setUsers] = useState( [] );
	const [ usersTotal, setUsersTotal ] = useState( 0 );
	const [ usersPage, setUsersPage ] = useState( 1 );
	const [ userSearch, setUserSearch ] = useState( '' );
	const [ usersLoading, setUsersLoading ] = useState( false );

	// Health state
	const [ health, setHealth ] = useState( null );
	const [ healthLoading, setHealthLoading ] = useState( false );

	// System logs state
	const [ logs, setLogs ] = useState( [] );
	const [ logsTotal, setLogsTotal ] = useState( 0 );
	const [ logsPage, setLogsPage ] = useState( 1 );
	const [ logLevel, setLogLevel ] = useState( 'all' );
	const [ logSearch, setLogSearch ] = useState( '' );
	const [ logsLoading, setLogsLoading ] = useState( false );

	// Audit logs state
	const [ auditLogs, setAuditLogs ] = useState( [] );
	const [ auditLoading, setAuditLoading ] = useState( false );

	// Modal states
	const [ selectedUserLedger, setSelectedUserLedger ] = useState( null );
	const [ ledgerEntries, setLedgerEntries ] = useState( [] );
	const [ ledgerLoading, setLedgerLoading ] = useState( false );

	const [ adjustModalUser, setAdjustModalUser ] = useState( null );
	const [ adjustAmount, setAdjustAmount ] = useState( '' );
	const [ adjustReason, setAdjustReason ] = useState( '' );
	const [ adjusting, setAdjusting ] = useState( false );

	// Reward Products search, filter & pagination state
	const [ productSearch, setProductSearch ] = useState( '' );
	const [ showOnlySelected, setShowOnlySelected ] = useState( false );
	const [ productPage, setProductPage ] = useState( 1 );
	const PRODUCTS_PER_PAGE = 24;

	const selectedIds = ( loyaltyOpts.reward_product_ids || [] ).map( Number );

	const filteredProducts = ( wcProducts || [] )
		.filter( ( p ) => {
			const isSelected = selectedIds.includes( Number( p.id ) );
			if ( showOnlySelected && ! isSelected ) {
				return false;
			}
			if ( productSearch.trim() ) {
				const term = productSearch.toLowerCase().trim();
				const titleMatch = ( p.title || '' ).toLowerCase().includes( term );
				const idMatch = String( p.id ).includes( term );
				return titleMatch || idMatch;
			}
			return true;
		} )
		.sort( ( a, b ) => {
			// If not searching, bubble selected items to top for convenience
			if ( ! productSearch.trim() && ! showOnlySelected ) {
				const aSel = selectedIds.includes( Number( a.id ) );
				const bSel = selectedIds.includes( Number( b.id ) );
				if ( aSel && ! bSel ) return -1;
				if ( ! aSel && bSel ) return 1;
			}
			return 0;
		} );

	const totalProductPages = Math.ceil( filteredProducts.length / PRODUCTS_PER_PAGE ) || 1;
	const pagedProducts = filteredProducts.slice(
		( productPage - 1 ) * PRODUCTS_PER_PAGE,
		productPage * PRODUCTS_PER_PAGE
	);

	const updateLoyaltyField = ( field, val ) => {
		const updated = {
			...loyaltyOpts,
			[ field ]: val,
		};
		onOptionsChange( 'loyalty', updated );
	};

	const fetchHealth = async () => {
		try {
			setHealthLoading( true );
			const res = await fetch( '/wp-json/account/v1/admin/loyalty/health', {
				headers: { 'X-WP-Nonce': window.rakaSettingsData?.nonce || '' },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setHealth( data );
			}
		} catch ( err ) {
			console.error( 'Health check error:', err );
		} finally {
			setHealthLoading( false );
		}
	};

	const fetchUsers = async ( page = 1, search = '' ) => {
		try {
			setUsersLoading( true );
			const res = await fetch( `/wp-json/account/v1/admin/loyalty/users?page=${ page }&limit=15&search=${ encodeURIComponent( search ) }`, {
				headers: { 'X-WP-Nonce': window.rakaSettingsData?.nonce || '' },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setUsers( data.users || [] );
				setUsersTotal( data.total || 0 );
				setUsersPage( page );
			}
		} catch ( err ) {
			console.error( 'Fetch users error:', err );
		} finally {
			setUsersLoading( false );
		}
	};

	const fetchLogs = async ( page = 1, level = 'all', search = '' ) => {
		try {
			setLogsLoading( true );
			const res = await fetch( `/wp-json/account/v1/admin/loyalty/logs?page=${ page }&limit=20&level=${ level }&search=${ encodeURIComponent( search ) }`, {
				headers: { 'X-WP-Nonce': window.rakaSettingsData?.nonce || '' },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setLogs( data.logs || [] );
				setLogsTotal( data.total || 0 );
				setLogsPage( page );
			}
		} catch ( err ) {
			console.error( 'Fetch logs error:', err );
		} finally {
			setLogsLoading( false );
		}
	};

	const fetchAuditLogs = async () => {
		try {
			setAuditLoading( true );
			const res = await fetch( '/wp-json/account/v1/admin/loyalty/audit?limit=25', {
				headers: { 'X-WP-Nonce': window.rakaSettingsData?.nonce || '' },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setAuditLogs( data.audit || [] );
			}
		} catch ( err ) {
			console.error( 'Fetch audit error:', err );
		} finally {
			setAuditLoading( false );
		}
	};

	const viewUserLedger = async ( user ) => {
		setSelectedUserLedger( user );
		try {
			setLedgerLoading( true );
			const res = await fetch( `/wp-json/account/v1/admin/loyalty/users/${ user.user_id }/ledger`, {
				headers: { 'X-WP-Nonce': window.rakaSettingsData?.nonce || '' },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setLedgerEntries( data.entries || [] );
			}
		} catch ( err ) {
			console.error( 'Fetch user ledger error:', err );
		} finally {
			setLedgerLoading( false );
		}
	};

	const handleAdjustSubmit = async ( e ) => {
		e.preventDefault();
		if ( ! adjustAmount || ! adjustReason || ! adjustModalUser ) {
			return;
		}

		try {
			setAdjusting( true );
			const res = await fetch( '/wp-json/account/v1/admin/loyalty/adjust-points', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.rakaSettingsData?.nonce || '',
				},
				body: JSON.stringify( {
					user_id: adjustModalUser.user_id,
					amount: parseFloat( adjustAmount ),
					reason: adjustReason,
				} ),
			} );

			const resData = await res.json();
			if ( ! res.ok || ! resData.success ) {
				throw new Error( resData.message || 'Adjust failed' );
			}

			setNotice( { type: 'success', message: `ปรับแต้มให้ ${ adjustModalUser.display_name } จำนวน ${ adjustAmount } แต้ม เรียบร้อยแล้ว` } );
			setAdjustModalUser( null );
			setAdjustAmount( '' );
			setAdjustReason( '' );
			fetchUsers( usersPage, userSearch );
			fetchHealth();
		} catch ( err ) {
			setNotice( { type: 'error', message: err.message || 'เกิดข้อผิดพลาดในการปรับปรุงแต้ม' } );
		} finally {
			setAdjusting( false );
		}
	};

	const handleReconcile = async () => {
		if ( ! window.confirm( 'ต้องการตรวจสอบและปรับปรุงความถูกต้องของยอดบัญชีทั้งหมดกับ Ledger หรือไม่?' ) ) {
			return;
		}

		try {
			const res = await fetch( '/wp-json/account/v1/admin/loyalty/reconcile', {
				method: 'POST',
				headers: { 'X-WP-Nonce': window.rakaSettingsData?.nonce || '' },
			} );
			const resData = await res.json();
			if ( res.ok && resData.success ) {
				setNotice( { type: 'success', message: resData.message } );
				fetchHealth();
				fetchUsers( usersPage, userSearch );
			} else {
				throw new Error( resData.message || 'Reconcile failed' );
			}
		} catch ( err ) {
			setNotice( { type: 'error', message: err.message } );
		}
	};

	const handlePurgeLogs = async () => {
		if ( ! window.confirm( 'ยืนยันการล้างประวัติ System Logs ทั้งหมดในฐานข้อมูล?' ) ) {
			return;
		}

		try {
			const res = await fetch( '/wp-json/account/v1/admin/loyalty/logs/purge', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.rakaSettingsData?.nonce || '',
				},
				body: JSON.stringify( { confirm: true } ),
			} );
			const resData = await res.json();
			if ( res.ok && resData.success ) {
				setNotice( { type: 'success', message: resData.message } );
				fetchLogs( 1, logLevel, logSearch );
				fetchHealth();
			} else {
				throw new Error( resData.message || 'Purge failed' );
			}
		} catch ( err ) {
			setNotice( { type: 'error', message: err.message } );
		}
	};

	useEffect( () => {
		if ( subTab === 'users' ) {
			fetchUsers( 1, userSearch );
		} else if ( subTab === 'health' ) {
			fetchHealth();
			fetchLogs( 1, logLevel, logSearch );
			fetchAuditLogs();
		}
	}, [ subTab ] );

	const toggleRewardProduct = ( pid ) => {
		const curr = loyaltyOpts.reward_product_ids || [];
		const exists = curr.includes( pid );
		const next = exists ? curr.filter( ( id ) => id !== pid ) : [ ...curr, pid ];
		updateLoyaltyField( 'reward_product_ids', next );
	};

	const updateRewardProductPoints = ( pid, pointsVal ) => {
		const currPts = { ...( loyaltyOpts.reward_product_points || {} ) };
		const num = Math.max( 1, parseFloat( pointsVal ) || 1 );
		currPts[ pid ] = num;
		updateLoyaltyField( 'reward_product_points', currPts );
	};

	return (
		<div style={ { spaceY: '20px' } }>
			{ /* Sub-navigation Pills */ }
			<div style={ { display: 'flex', gap: '8px', marginBottom: '20px', borderBottom: '1px solid #e2e8f0', paddingBottom: '12px' } }>
				<button
					type="button"
					onClick={ () => setSubTab( 'settings' ) }
					style={ {
						padding: '8px 16px',
						borderRadius: '8px',
						border: 'none',
						background: subTab === 'settings' ? '#0f172a' : '#f1f5f9',
						color: subTab === 'settings' ? '#fff' : '#475569',
						fontWeight: 600,
						fontSize: '13px',
						cursor: 'pointer',
						display: 'flex',
						alignItems: 'center',
						gap: '6px',
					} }
				>
					<i className="ri-settings-4-line" />
					<span>การตั้งค่า & กฎการสะสมแต้ม</span>
				</button>
				<button
					type="button"
					onClick={ () => setSubTab( 'users' ) }
					style={ {
						padding: '8px 16px',
						borderRadius: '8px',
						border: 'none',
						background: subTab === 'users' ? '#0f172a' : '#f1f5f9',
						color: subTab === 'users' ? '#fff' : '#475569',
						fontWeight: 600,
						fontSize: '13px',
						cursor: 'pointer',
						display: 'flex',
						alignItems: 'center',
						gap: '6px',
					} }
				>
					<i className="ri-user-star-line" />
					<span>สมาชิก & Ledger ตรวจสอบยอด</span>
				</button>
				<button
					type="button"
					onClick={ () => setSubTab( 'health' ) }
					style={ {
						padding: '8px 16px',
						borderRadius: '8px',
						border: 'none',
						background: subTab === 'health' ? '#0f172a' : '#f1f5f9',
						color: subTab === 'health' ? '#fff' : '#475569',
						fontWeight: 600,
						fontSize: '13px',
						cursor: 'pointer',
						display: 'flex',
						alignItems: 'center',
						gap: '6px',
					} }
				>
					<i className="ri-heart-pulse-line" />
					<span>System Health, Audit & Logs</span>
				</button>
			</div>

			{ /* ── TAB 1: Loyalty Settings ── */ }
			{ subTab === 'settings' && (
				<div style={ { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(360px, 1fr))', gap: '20px' } }>
					{ /* Main Rules Card */ }
					<Card>
						<CardHeader>
							<h3 style={ { margin: 0, fontSize: '15px', fontWeight: 700 } }>
								{ __( 'กติกาการคำนวณแต้ม (Earning Rules)', 'raka-preorder-theme' ) }
							</h3>
						</CardHeader>
						<CardBody style={ { display: 'flex', flexDirection: 'column', gap: '16px' } }>
							<ToggleControl
								label={ __( 'เปิดใช้งานระบบ Loyalty & Rewards Engine', 'raka-preorder-theme' ) }
								help={ __( 'หากปิด สมาชิกจะไม่ได้รับแต้มและไม่สามารถแลกของรางวัลได้', 'raka-preorder-theme' ) }
								checked={ !! loyaltyOpts.enabled }
								onChange={ ( val ) => updateLoyaltyField( 'enabled', val ) }
							/>

							<TextControl
								label={ __( 'ยอดใช้จ่ายต่อ 1 แต้ม (บาท)', 'raka-preorder-theme' ) }
								help={ __( 'ค่าเริ่มต้น 100 บาท = 1 แต้ม (คำนวณจากยอดสุทธิ)', 'raka-preorder-theme' ) }
								type="number"
								value={ loyaltyOpts.spend_per_point }
								onChange={ ( val ) => updateLoyaltyField( 'spend_per_point', parseFloat( val ) || 100 ) }
							/>

							<TextControl
								label={ __( 'ตัวคูณแต้มสินค้าพรีออเดอร์ (Pre-order Multiplier)', 'raka-preorder-theme' ) }
								help={ __( 'เช่น 1.0 (ปกติ) หรือ 1.5 (รับแต้มเพิ่ม 50% เมื่อสั่งพรีออเดอร์)', 'raka-preorder-theme' ) }
								type="number"
								step="0.1"
								value={ loyaltyOpts.preorder_points_multiplier }
								onChange={ ( val ) => updateLoyaltyField( 'preorder_points_multiplier', parseFloat( val ) || 1.0 ) }
							/>

							<TextControl
								label={ __( 'อายุของแต้มสะสมในแต่ละล็อต (วัน)', 'raka-preorder-theme' ) }
								help={ __( 'ค่าเริ่มต้น 365 วัน (1 ปี) นับจากวันที่คำสั่งซื้อเสร็จสมบูรณ์', 'raka-preorder-theme' ) }
								type="number"
								value={ loyaltyOpts.lot_expiry_days }
								onChange={ ( val ) => updateLoyaltyField( 'lot_expiry_days', parseInt( val, 10 ) || 365 ) }
							/>
						</CardBody>
					</Card>

					{ /* Tier Thresholds Card */ }
					<Card>
						<CardHeader>
							<h3 style={ { margin: 0, fontSize: '15px', fontWeight: 700 } }>
								{ __( 'เกณฑ์ระดับสมาชิก (Tier Thresholds)', 'raka-preorder-theme' ) }
							</h3>
						</CardHeader>
						<CardBody style={ { display: 'flex', flexDirection: 'column', gap: '16px' } }>
							<p style={ { fontSize: '12px', color: '#64748b', margin: 0 } }>
								{ __( 'ระดับสมาชิกคำนวณจากแต้มสะสมตลอดชีพ (Lifetime Earned) โดยไม่มีวันตกชั้น', 'raka-preorder-theme' ) }
							</p>

							<div style={ { padding: '12px', background: '#f8fafc', borderRadius: '8px', border: '1px solid #e2e8f0' } }>
								<strong style={ { fontSize: '13px', color: '#854d0e' } }>Bronze Tier</strong>: 0 แต้ม (เริ่มต้น)
							</div>

							<TextControl
								label={ __( 'Silver Tier Threshold (แต้มสะสมตลอดชีพ)', 'raka-preorder-theme' ) }
								help={ __( 'ค่าเริ่มต้น 500 แต้ม', 'raka-preorder-theme' ) }
								type="number"
								value={ loyaltyOpts.tier_threshold_silver }
								onChange={ ( val ) => updateLoyaltyField( 'tier_threshold_silver', parseFloat( val ) || 500 ) }
							/>

							<TextControl
								label={ __( 'Gold Tier Threshold (แต้มสะสมตลอดชีพ)', 'raka-preorder-theme' ) }
								help={ __( 'ค่าเริ่มต้น 2,000 แต้ม', 'raka-preorder-theme' ) }
								type="number"
								value={ loyaltyOpts.tier_threshold_gold }
								onChange={ ( val ) => updateLoyaltyField( 'tier_threshold_gold', parseFloat( val ) || 2000 ) }
							/>
						</CardBody>
					</Card>

					{ /* Reward Catalog Products Card */ }
					<Card style={ { gridColumn: '1 / -1' } }>
						<CardHeader style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px' } }>
							<div>
								<h3 style={ { margin: 0, fontSize: '15px', fontWeight: 700 } }>
									{ __( 'สินค้าในแคตตาล็อกของรางวัล (กำหนดแต้มแลกรายสินค้า)', 'raka-preorder-theme' ) }
								</h3>
								<span style={ { fontSize: '12px', color: '#64748b' } }>
									{ __( 'เลือกไว้แล้ว', 'raka-preorder-theme' ) } <strong style={ { color: '#ef4444' } }>{ ( loyaltyOpts.reward_product_ids || [] ).length }</strong> { __( 'รายการ', 'raka-preorder-theme' ) }
								</span>
							</div>

							{ /* Search & Filter Controls */ }
							<div style={ { display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap' } }>
								<input
									type="text"
									placeholder={ __( 'ค้นหาชื่อสินค้า...', 'raka-preorder-theme' ) }
									value={ productSearch }
									onChange={ ( e ) => {
										setProductSearch( e.target.value );
										setProductPage( 1 );
									} }
									style={ {
										padding: '6px 12px',
										fontSize: '13px',
										borderRadius: '6px',
										border: '1px solid #cbd5e1',
										minWidth: '200px',
										outline: 'none',
									} }
								/>

								<button
									type="button"
									onClick={ () => {
										setShowOnlySelected( ! showOnlySelected );
										setProductPage( 1 );
									} }
									style={ {
										padding: '6px 12px',
										fontSize: '12px',
										fontWeight: 600,
										borderRadius: '6px',
										border: showOnlySelected ? '1px solid #ef4444' : '1px solid #cbd5e1',
										background: showOnlySelected ? '#fef2f2' : '#ffffff',
										color: showOnlySelected ? '#ef4444' : '#475569',
										cursor: 'pointer',
									} }
								>
									{ showOnlySelected ? __( 'แสดงทั้งหมด', 'raka-preorder-theme' ) : __( 'แสดงเฉพาะที่เลือก', 'raka-preorder-theme' ) }
								</button>
							</div>
						</CardHeader>
						<CardBody>
							<p style={ { fontSize: '12px', color: '#64748b', marginBottom: '16px' } }>
								{ __( 'คลิกเลือกสินค้า WooCommerce ที่ต้องการเปิดให้สมาชิกระดับ 100 แต้มขึ้นไปเลือกแลกฟรี (ระบบจะตั้งราคา 0฿ พร้อม HMAC Signature ป้องกันการดัดแปลง)', 'raka-preorder-theme' ) }
							</p>

							{ filteredProducts.length === 0 ? (
								<div style={ { padding: '40px', textAlign: 'center', color: '#94a3b8', fontSize: '13px', background: '#f8fafc', borderRadius: '8px' } }>
									{ __( 'ไม่พบสินค้าที่ตรงกับคำค้นหา', 'raka-preorder-theme' ) }
								</div>
							) : (
								<div style={ { display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(240px, 1fr))', gap: '12px', padding: '4px' } }>
									{ pagedProducts.map( ( p ) => {
										const isSelected = selectedIds.includes( Number( p.id ) );
										const currentPts = ( loyaltyOpts.reward_product_points || {} )[ p.id ] ?? 100;
										return (
											<div
												key={ p.id }
												style={ {
													display: 'flex',
													flexDirection: 'column',
													gap: '8px',
													padding: '10px 12px',
													borderRadius: '10px',
													border: isSelected ? '2px solid #ef4444' : '1px solid #e2e8f0',
													background: isSelected ? '#fef2f2' : '#ffffff',
													boxShadow: isSelected ? '0 2px 8px rgba(239, 68, 68, 0.15)' : 'none',
													transition: 'all 0.15s ease',
												} }
											>
												<div
													onClick={ () => toggleRewardProduct( p.id ) }
													style={ {
														display: 'flex',
														alignItems: 'center',
														gap: '10px',
														cursor: 'pointer',
													} }
												>
													<input
														type="checkbox"
														checked={ isSelected }
														onChange={ () => {} }
														style={ {
															margin: 0,
															cursor: 'pointer',
															accentColor: '#ef4444',
															width: '16px',
															height: '16px',
														} }
													/>
													{ p.image_url && (
														<img
															src={ p.image_url }
															alt={ p.title }
															style={ {
																width: '38px',
																height: '38px',
																objectFit: 'cover',
																borderRadius: '6px',
																background: '#f1f5f9',
																border: isSelected ? '1px solid #fca5a5' : '1px solid #e2e8f0',
															} }
														/>
													) }
													<div style={ { flex: 1, minWidth: 0 } }>
														<div style={ { fontSize: '12px', fontWeight: isSelected ? 700 : 600, color: isSelected ? '#991b1b' : '#0f172a', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' } }>
															{ p.title }
														</div>
														<div style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '2px' } }>
															<span style={ { fontSize: '11px', color: '#64748b' } }>
																{ p.price || '0฿' }
															</span>
															{ isSelected && (
																<span style={ {
																	fontSize: '10px',
																	fontWeight: 700,
																	color: '#ffffff',
																	background: '#ef4444',
																	padding: '1px 6px',
																	borderRadius: '4px',
																	letterSpacing: '0.02em',
																} }>
																	ACTIVE
																</span>
															) }
														</div>
													</div>
												</div>

												{ /* Points Input (Only active when selected) */ }
												{ isSelected && (
													<div
														style={ {
															display: 'flex',
															alignItems: 'center',
															justifyContent: 'space-between',
															gap: '8px',
															paddingTop: '6px',
															borderTop: '1px dashed #fca5a5',
														} }
														onClick={ ( e ) => e.stopPropagation() }
													>
														<span style={ { fontSize: '11px', fontWeight: 600, color: '#7f1d1d' } }>
															{ __( 'แต้มที่ใช้แลก:', 'raka-preorder-theme' ) }
														</span>
														<div style={ { display: 'flex', alignItems: 'center', gap: '4px' } }>
															<input
																type="number"
																min="1"
																step="1"
																value={ currentPts }
																onChange={ ( e ) => updateRewardProductPoints( p.id, e.target.value ) }
																style={ {
																	width: '75px',
																	height: '26px',
																	fontSize: '12px',
																	fontWeight: 700,
																	color: '#b91c1c',
																	textAlign: 'right',
																	borderRadius: '4px',
																	border: '1px solid #f87171',
																	background: '#ffffff',
																	padding: '2px 6px',
																	outline: 'none',
																} }
															/>
															<span style={ { fontSize: '11px', color: '#7f1d1d' } }>{ __( 'แต้ม', 'raka-preorder-theme' ) }</span>
														</div>
													</div>
												) }
											</div>
										);
									} ) }
								</div>
							) }

							{ /* Pagination Controls */ }
							{ totalProductPages > 1 && (
								<div style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '16px', paddingTop: '12px', borderTop: '1px solid #e2e8f0', fontSize: '12px', color: '#64748b' } }>
									<div>
										{ __( 'แสดงหน้า', 'raka-preorder-theme' ) } { productPage } / { totalProductPages } ({ filteredProducts.length } { __( 'รายการ', 'raka-preorder-theme' ) })
									</div>
									<div style={ { display: 'flex', gap: '6px' } }>
										<button
											type="button"
											disabled={ productPage <= 1 }
											onClick={ () => setProductPage( ( prev ) => Math.max( 1, prev - 1 ) ) }
											style={ {
												padding: '6px 12px',
												borderRadius: '6px',
												border: '1px solid #cbd5e1',
												background: productPage <= 1 ? '#f1f5f9' : '#ffffff',
												cursor: productPage <= 1 ? 'not-allowed' : 'pointer',
												fontSize: '12px',
												fontWeight: 600,
											} }
										>
											{ __( '« ก่อนหน้า', 'raka-preorder-theme' ) }
										</button>

										{ Array.from( { length: totalProductPages }, ( _, i ) => i + 1 )
											.filter( ( p ) => p === 1 || p === totalProductPages || Math.abs( p - productPage ) <= 2 )
											.map( ( p, idx, arr ) => {
												const prevP = arr[ idx - 1 ];
												const showEllipsis = prevP && p - prevP > 1;
												return (
													<span key={ p } style={ { display: 'inline-flex', alignItems: 'center', gap: '6px' } }>
														{ showEllipsis && <span style={ { padding: '0 4px', color: '#94a3b8' } }>...</span> }
														<button
															type="button"
															onClick={ () => setProductPage( p ) }
															style={ {
																width: '32px',
																height: '32px',
																borderRadius: '6px',
																border: p === productPage ? '1px solid #ef4444' : '1px solid #cbd5e1',
																background: p === productPage ? '#ef4444' : '#ffffff',
																color: p === productPage ? '#ffffff' : '#334155',
																fontSize: '12px',
																fontWeight: p === productPage ? 700 : 500,
																cursor: 'pointer',
															} }
														>
															{ p }
														</button>
													</span>
												);
											} ) }

										<button
											type="button"
											disabled={ productPage >= totalProductPages }
											onClick={ () => setProductPage( ( prev ) => Math.min( totalProductPages, prev + 1 ) ) }
											style={ {
												padding: '6px 12px',
												borderRadius: '6px',
												border: '1px solid #cbd5e1',
												background: productPage >= totalProductPages ? '#f1f5f9' : '#ffffff',
												cursor: productPage >= totalProductPages ? 'not-allowed' : 'pointer',
												fontSize: '12px',
												fontWeight: 600,
											} }
										>
											{ __( 'ถัดไป »', 'raka-preorder-theme' ) }
										</button>
									</div>
								</div>
							) }
						</CardBody>
					</Card>
				</div>
			) }

			{ /* ── TAB 2: Users & Ledger ── */ }
			{ subTab === 'users' && (
				<Card>
					<CardHeader style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center' } }>
						<h3 style={ { margin: 0, fontSize: '15px', fontWeight: 700 } }>
							สมาชิกและยอดแต้มสะสม ({ usersTotal } คน)
						</h3>
						<div style={ { display: 'flex', gap: '8px' } }>
							<input
								type="text"
								placeholder="ค้นหาชื่อ, อีเมล, ID..."
								value={ userSearch }
								onChange={ ( e ) => setUserSearch( e.target.value ) }
								onKeyDown={ ( e ) => e.key === 'Enter' && fetchUsers( 1, userSearch ) }
								style={ { padding: '4px 10px', fontSize: '12px', borderRadius: '6px', border: '1px solid #cbd5e1' } }
							/>
							<Button variant="secondary" onClick={ () => fetchUsers( 1, userSearch ) } size="small">
								ค้นหา
							</Button>
						</div>
					</CardHeader>
					<CardBody>
						{ usersLoading ? (
							<div style={ { padding: '40px', textAlign: 'center' } }><Spinner /></div>
						) : (
							<div style={ { overflowX: 'auto' } }>
								<table style={ { width: '100%', borderCollapse: 'collapse', fontSize: '12px' } }>
									<thead>
										<tr style={ { background: '#f8fafc', borderBottom: '1px solid #e2e8f0', textAlign: 'left' } }>
											<th style={ { padding: '10px' } }>User ID</th>
											<th style={ { padding: '10px' } }>ชื่อ / บัญชี</th>
											<th style={ { padding: '10px' } }>อีเมล</th>
											<th style={ { padding: '10px' } }>ระดับ (Tier)</th>
											<th style={ { padding: '10px', textAlign: 'right' } }>แต้มคงเหลือ</th>
											<th style={ { padding: '10px', textAlign: 'right' } }>แต้มที่จองไว้</th>
											<th style={ { padding: '10px', textAlign: 'right' } }>สะสมตลอดชีพ</th>
											<th style={ { padding: '10px', textAlign: 'center' } }>การจัดการ</th>
										</tr>
									</thead>
									<tbody>
										{ users.map( ( u ) => (
											<tr key={ u.user_id } style={ { borderBottom: '1px solid #f1f5f9' } }>
												<td style={ { padding: '10px', fontFamily: 'monospace' } }>#{ u.user_id }</td>
												<td style={ { padding: '10px', fontWeight: 600 } }>{ u.display_name || u.user_login }</td>
												<td style={ { padding: '10px', color: '#64748b' } }>{ u.user_email }</td>
												<td style={ { padding: '10px' } }>
													<span style={ {
														padding: '2px 8px',
														borderRadius: '12px',
														fontSize: '11px',
														fontWeight: 700,
														textTransform: 'uppercase',
														background: u.tier === 'gold' ? '#fef3c7' : ( u.tier === 'silver' ? '#f1f5f9' : '#fed7aa' ),
														color: u.tier === 'gold' ? '#b45309' : ( u.tier === 'silver' ? '#475569' : '#c2410c' ),
													} }>
														{ u.tier }
													</span>
												</td>
												<td style={ { padding: '10px', textAlign: 'right', fontWeight: 700, color: '#dc2626' } }>
													{ Number( u.balance ).toLocaleString() }
												</td>
												<td style={ { padding: '10px', textAlign: 'right', color: '#d97706' } }>
													{ Number( u.reserved ).toLocaleString() }
												</td>
												<td style={ { padding: '10px', textAlign: 'right', color: '#475569' } }>
													{ Number( u.lifetime_earned ).toLocaleString() }
												</td>
												<td style={ { padding: '10px', textAlign: 'center' } }>
													<div style={ { display: 'flex', gap: '4px', justifyContent: 'center' } }>
														<Button variant="secondary" size="small" onClick={ () => viewUserLedger( u ) }>
															Ledger
														</Button>
														<Button variant="primary" size="small" onClick={ () => setAdjustModalUser( u ) }>
															ปรับแต้ม
														</Button>
													</div>
												</td>
											</tr>
										) ) }
									</tbody>
								</table>
							</div>
						) }
					</CardBody>
				</Card>
			) }

			{ /* ── TAB 3: Health & Observability ── */ }
			{ subTab === 'health' && (
				<div style={ { display: 'flex', flexDirection: 'column', gap: '20px' } }>
					{ /* Health Summary Cards */ }
					<div style={ { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '16px' } }>
						<Card>
							<CardBody>
								<div style={ { fontSize: '11px', fontWeight: 600, color: '#64748b', textTransform: 'uppercase' } }>
									หนี้สินแต้มรวมในระบบ (Liability)
								</div>
								<div style={ { fontSize: '24px', fontWeight: 800, color: '#0f172a', marginTop: '4px' } }>
									{ Number( health?.total_liability || 0 ).toLocaleString() } แต้ม
								</div>
							</CardBody>
						</Card>

						<Card>
							<CardBody>
								<div style={ { fontSize: '11px', fontWeight: 600, color: '#64748b', textTransform: 'uppercase' } }>
									ข้อผิดพลาดใน 24 ชั่วโมง
								</div>
								<div style={ { fontSize: '24px', fontWeight: 800, color: ( health?.errors_24h || 0 ) > 0 ? '#ef4444' : '#22c55e', marginTop: '4px' } }>
									{ health?.errors_24h || 0 } ครั้ง
								</div>
							</CardBody>
						</Card>

						<Card>
							<CardBody>
								<div style={ { fontSize: '11px', fontWeight: 600, color: '#64748b', textTransform: 'uppercase' } }>
									สถานะ Ledger Reconciliation
								</div>
								<div style={ { fontSize: '16px', fontWeight: 800, color: health?.mismatches_count === 0 ? '#22c55e' : '#ef4444', marginTop: '8px' } }>
									{ health?.mismatches_count === 0 ? '✓ ยอดตรงกัน 100%' : `⚠ พบ ${ health?.mismatches_count } ยอดไม่ตรงกัน` }
								</div>
							</CardBody>
						</Card>

						<Card>
							<CardBody style={ { display: 'flex', flexDirection: 'column', gap: '8px', justifyContent: 'center' } }>
								<Button variant="secondary" onClick={ handleReconcile } size="small">
									<i className="ri-refresh-line" style={ { marginRight: '4px' } } />
									Reconcile บัญชีทั้งหมด
								</Button>
								<Button variant="secondary" isDestructive onClick={ handlePurgeLogs } size="small">
									<i className="ri-delete-bin-line" style={ { marginRight: '4px' } } />
									ล้าง System Logs เก่า
								</Button>
							</CardBody>
						</Card>
					</div>

					{ /* System Logs Card */ }
					<Card>
						<CardHeader style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center' } }>
							<h3 style={ { margin: 0, fontSize: '15px', fontWeight: 700 } }>
								System Trace Logs ({ logsTotal } รายการ)
							</h3>
							<div style={ { display: 'flex', gap: '8px' } }>
								<select
									value={ logLevel }
									onChange={ ( e ) => {
										setLogLevel( e.target.value );
										fetchLogs( 1, e.target.value, logSearch );
									} }
									style={ { fontSize: '12px', padding: '4px 8px', borderRadius: '6px', border: '1px solid #cbd5e1' } }
								>
									<option value="all">ทุกระดับ</option>
									<option value="error">Error & Critical</option>
									<option value="warning">Warning</option>
									<option value="info">Info</option>
								</select>
								<input
									type="text"
									placeholder="ค้นหาข้อความ, request_id..."
									value={ logSearch }
									onChange={ ( e ) => setLogSearch( e.target.value ) }
									onKeyDown={ ( e ) => e.key === 'Enter' && fetchLogs( 1, logLevel, logSearch ) }
									style={ { padding: '4px 10px', fontSize: '12px', borderRadius: '6px', border: '1px solid #cbd5e1' } }
								/>
							</div>
						</CardHeader>
						<CardBody>
							{ logsLoading ? (
								<div style={ { padding: '30px', textAlign: 'center' } }><Spinner /></div>
							) : (
								<div style={ { maxHeight: '350px', overflowY: 'auto', fontFamily: 'monospace', fontSize: '11px' } }>
									{ logs.map( ( l ) => (
										<div
											key={ l.id }
											style={ {
												padding: '6px 8px',
												borderBottom: '1px solid #f1f5f9',
												display: 'flex',
												gap: '8px',
												alignItems: 'baseline',
												background: l.level === 'error' || l.level === 'critical' ? '#fef2f2' : 'transparent',
											} }
										>
											<span style={ { color: '#94a3b8' } }>{ l.created_at }</span>
											<span style={ {
												padding: '1px 5px',
												borderRadius: '4px',
												fontWeight: 700,
												fontSize: '10px',
												textTransform: 'uppercase',
												background: l.level === 'error' ? '#ef4444' : ( l.level === 'warning' ? '#f59e0b' : '#3b82f6' ),
												color: '#fff',
											} }>
												{ l.level }
											</span>
											<span style={ { color: '#64748b' } }>[{ l.event_code }]</span>
											<span style={ { color: '#0f172a', flex: 1 } }>{ l.message }</span>
											{ l.request_id && <span style={ { color: '#94a3b8', fontSize: '10px' } }>{ l.request_id }</span> }
										</div>
									) ) }
								</div>
							) }
						</CardBody>
					</Card>

					{ /* Audit Trail Card */ }
					<Card>
						<CardHeader>
							<h3 style={ { margin: 0, fontSize: '15px', fontWeight: 700 } }>
								Admin Audit Trail (ประวัติการกระทำของแอดมิน)
							</h3>
						</CardHeader>
						<CardBody>
							{ auditLoading ? (
								<div style={ { padding: '20px', textAlign: 'center' } }><Spinner /></div>
							) : (
								<div style={ { maxHeight: '250px', overflowY: 'auto', fontSize: '12px' } }>
									{ auditLogs.map( ( a ) => (
										<div key={ a.id } style={ { padding: '8px', borderBottom: '1px solid #f1f5f9', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } }>
											<div>
												<span style={ { fontWeight: 700, color: '#0f172a' } }>{ a.action }</span>
												<span style={ { color: '#64748b', marginLeft: '6px' } }>โดย { a.display_name || a.user_login || `Admin #${ a.actor_id }` }</span>
												{ a.reason && <p style={ { margin: '2px 0 0 0', color: '#475569', fontSize: '11px' } }>{ a.reason }</p> }
											</div>
											<span style={ { color: '#94a3b8', fontSize: '11px' } }>{ a.created_at }</span>
										</div>
									) ) }
								</div>
							) }
						</CardBody>
					</Card>
				</div>
			) }

			{ /* ── Modal: User Ledger Inspector ── */ }
			{ selectedUserLedger && (
				<div style={ {
					position: 'fixed',
					top: 0,
					left: 0,
					right: 0,
					bottom: 0,
					background: 'rgba(0,0,0,0.5)',
					zIndex: 99999,
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					padding: '20px',
				} }>
					<div style={ {
						background: '#fff',
						borderRadius: '12px',
						maxWidth: '750px',
						width: '100%',
						maxHeight: '85vh',
						display: 'flex',
						flexDirection: 'column',
						boxShadow: '0 25px 50px -12px rgba(0,0,0,0.25)',
						overflow: 'hidden',
					} }>
						<div style={ { padding: '16px 20px', borderBottom: '1px solid #e2e8f0', display: 'flex', justifyContent: 'space-between', alignItems: 'center' } }>
							<h3 style={ { margin: 0, fontSize: '15px', fontWeight: 700 } }>
								Ledger รายการแต้มของ: { selectedUserLedger.display_name || selectedUserLedger.user_login } (#{ selectedUserLedger.user_id })
							</h3>
							<Button variant="secondary" onClick={ () => setSelectedUserLedger( null ) } size="small">✕</Button>
						</div>
						<div style={ { padding: '16px 20px', overflowY: 'auto', flex: 1 } }>
							{ ledgerLoading ? (
								<div style={ { padding: '40px', textAlign: 'center' } }><Spinner /></div>
							) : (
								<table style={ { width: '100%', borderCollapse: 'collapse', fontSize: '12px' } }>
									<thead>
										<tr style={ { background: '#f8fafc', borderBottom: '1px solid #e2e8f0', textAlign: 'left' } }>
											<th style={ { padding: '8px' } }>ID</th>
											<th style={ { padding: '8px' } }>ประเภท</th>
											<th style={ { padding: '8px' } }>ออเดอร์</th>
											<th style={ { padding: '8px', textAlign: 'right' } }>จำนวนแต้ม</th>
											<th style={ { padding: '8px', textAlign: 'right' } }>ยอดคงเหลือหลังทำ</th>
											<th style={ { padding: '8px' } }>วันที่</th>
										</tr>
									</thead>
									<tbody>
										{ ledgerEntries.map( ( le ) => (
											<tr key={ le.id } style={ { borderBottom: '1px solid #f1f5f9' } }>
												<td style={ { padding: '8px', fontFamily: 'monospace' } }>#{ le.id }</td>
												<td style={ { padding: '8px', fontWeight: 600 } }>{ le.event_type }</td>
												<td style={ { padding: '8px' } }>{ le.order_id > 0 ? `#${ le.order_id }` : '-' }</td>
												<td style={ { padding: '8px', textAlign: 'right', fontWeight: 700, color: le.amount > 0 ? '#16a34a' : '#dc2626' } }>
													{ le.amount > 0 ? `+${ le.amount }` : le.amount }
												</td>
												<td style={ { padding: '8px', textAlign: 'right', color: '#475569' } }>{ le.balance_after }</td>
												<td style={ { padding: '8px', color: '#94a3b8' } }>{ le.created_at }</td>
											</tr>
										) ) }
									</tbody>
								</table>
							) }
						</div>
					</div>
				</div>
			) }

			{ /* ── Modal: Adjust Points ── */ }
			{ adjustModalUser && (
				<div style={ {
					position: 'fixed',
					top: 0,
					left: 0,
					right: 0,
					bottom: 0,
					background: 'rgba(0,0,0,0.5)',
					zIndex: 99999,
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					padding: '20px',
				} }>
					<form onSubmit={ handleAdjustSubmit } style={ {
						background: '#fff',
						borderRadius: '12px',
						maxWidth: '480px',
						width: '100%',
						padding: '20px',
						boxShadow: '0 25px 50px -12px rgba(0,0,0,0.25)',
						display: 'flex',
						flexDirection: 'column',
						gap: '16px',
					} }>
						<div style={ { borderBottom: '1px solid #e2e8f0', paddingBottom: '10px' } }>
							<h3 style={ { margin: 0, fontSize: '16px', fontWeight: 700 } }>
								ปรับปรุงยอดแต้ม (Adjust Points)
							</h3>
							<p style={ { margin: '4px 0 0 0', fontSize: '12px', color: '#64748b' } }>
								สมาชิก: { adjustModalUser.display_name || adjustModalUser.user_login } (ยอดปัจจุบัน: { adjustModalUser.balance } แต้ม)
							</p>
						</div>

						<TextControl
							label="จำนวนแต้ม (+ เพิ่ม, - ลด)"
							help="ตัวอย่าง: 100 เพื่อเพิ่มแต้ม, -50 เพื่อหักแต้ม"
							type="number"
							value={ adjustAmount }
							onChange={ ( v ) => setAdjustAmount( v ) }
							required
						/>

						<TextControl
							label="เหตุผลในการปรับปรุง (Mandatory Reason)"
							help="จะถูกบันทึกใน Audit Trail และ Ledger อย่างถาวร"
							value={ adjustReason }
							onChange={ ( v ) => setAdjustReason( v ) }
							required
						/>

						<div style={ { display: 'flex', justifyContent: 'flex-end', gap: '8px', marginTop: '10px' } }>
							<Button variant="secondary" onClick={ () => setAdjustModalUser( null ) }>
								ยกเลิก
							</Button>
							<Button variant="primary" type="submit" isBusy={ adjusting } disabled={ adjusting }>
								บันทึกการปรับแต้ม
							</Button>
						</div>
					</form>
				</div>
			) }
		</div>
	);
}
