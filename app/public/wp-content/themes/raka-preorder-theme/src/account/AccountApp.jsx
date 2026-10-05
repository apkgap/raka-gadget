import { useState, useEffect } from '@wordpress/element';
import DashboardView from './components/DashboardView';
import OrdersView from './components/OrdersView';
import AddressesView from './components/AddressesView';
import SecurityView from './components/SecurityView';
import RewardsView from './components/RewardsView';
import OrderDetailModal from './components/OrderDetailModal';
import AddressEditModal from './components/AddressEditModal';
import LogoutModal from './components/LogoutModal';

export default function AccountApp() {
	const config = window.rakaAccountData || {
		apiUrl: '/wp-json/account/v1',
		passkeysApiUrl: '/wp-json/wc-passkeys/v1',
		nonce: '',
		logoutUrl: '/wp-login.php?action=logout',
		homeUrl: '/',
		currentUser: null,
	};

	const [ currentTab, setCurrentTab ] = useState( 'dashboard' );
	const [ loading, setLoading ] = useState( true );
	const [ overview, setOverview ] = useState( null );
	const [ orders, setOrders ] = useState( [] );
	const [ ordersLoading, setOrdersLoading ] = useState( false );
	const [ addresses, setAddresses ] = useState( null );
	const [ passkeys, setPasskeys ] = useState( [] );
	const [ toast, setToast ] = useState( null );

	// Modals state
	const [ selectedOrder, setSelectedOrder ] = useState( null );
	const [ editingAddressType, setEditingAddressType ] = useState( null );
	const [ showLogoutModal, setShowLogoutModal ] = useState( false );

	const showToast = ( message, type = 'success' ) => {
		setToast( { message, type } );
		setTimeout( () => setToast( null ), 3500 );
	};

	// Fetch Overview Data
	const fetchOverview = async () => {
		try {
			setLoading( true );
			const res = await fetch( `${ config.apiUrl }/overview`, {
				headers: { 'X-WP-Nonce': config.nonce },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setOverview( data );
			}
		} catch ( error ) {
			console.error( 'Error fetching overview:', error );
		} finally {
			setLoading( false );
		}
	};

	// Fetch Orders
	const fetchOrders = async ( status = 'all' ) => {
		try {
			setOrdersLoading( true );
			const res = await fetch( `${ config.apiUrl }/orders?status=${ status }`, {
				headers: { 'X-WP-Nonce': config.nonce },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setOrders( data.orders || [] );
			}
		} catch ( error ) {
			console.error( 'Error fetching orders:', error );
		} finally {
			setOrdersLoading( false );
		}
	};

	// Fetch Addresses
	const fetchAddresses = async () => {
		try {
			const res = await fetch( `${ config.apiUrl }/addresses`, {
				headers: { 'X-WP-Nonce': config.nonce },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setAddresses( data );
			}
		} catch ( error ) {
			console.error( 'Error fetching addresses:', error );
		}
	};

	// Fetch Passkeys
	const fetchPasskeys = async () => {
		try {
			const res = await fetch( `${ config.apiUrl }/passkeys`, {
				headers: { 'X-WP-Nonce': config.nonce },
			} );
			if ( res.ok ) {
				const data = await res.json();
				setPasskeys( data.passkeys || [] );
			}
		} catch ( error ) {
			console.error( 'Error fetching passkeys:', error );
		}
	};

	useEffect( () => {
		fetchOverview();
		fetchAddresses();
		fetchPasskeys();
	}, [] );

	useEffect( () => {
		if ( currentTab === 'orders' ) {
			fetchOrders();
		}
	}, [ currentTab ] );

	const tabs = [
		{ id: 'dashboard', label: 'ภาพรวม', icon: 'ri-dashboard-3-line' },
		{ id: 'rewards', label: 'ของรางวัล & แต้มสะสม', icon: 'ri-copper-coin-line', badge: overview?.stats?.points_balance },
		{ id: 'orders', label: 'คำสั่งซื้อ & พรีออเดอร์', icon: 'ri-shopping-bag-3-line', badge: overview?.stats?.total_orders },
		{ id: 'addresses', label: 'ที่อยู่จัดส่ง & ออกใบกำกับ', icon: 'ri-map-pin-user-line' },
		{ id: 'security', label: 'ความปลอดภัย & Passkeys', icon: 'ri-shield-keyhole-line', badge: passkeys?.length },
	];

	if ( loading && ! overview ) {
		return (
			<div id="raka-account-loading" className="min-h-[450px] flex flex-col items-center justify-center space-y-4 font-hn">
				<div id="raka-account-spinner" className="w-10 h-10 border-3 border-neutral-300 dark:border-neutral-700 border-t-red-600 rounded-full animate-spin"></div>
				<p id="raka-account-loading-text" className="text-xs sm:text-sm text-neutral-500 font-medium">กำลังโหลดข้อมูลบัญชีสมาชิก...</p>
			</div>
		);
	}

	const user = overview?.profile || {};
	const stats = overview?.stats || {};

	return (
		<div id="raka-account-app-root" className="w-full max-w-6xl mx-auto space-y-6 sm:space-y-8 font-hn text-neutral-900 dark:text-white transition-colors">

			{ /* Toast Alert */ }
			{ toast && (
				<div
					id="raka-account-toast"
					className={ `fixed top-20 right-6 z-50 px-4 py-3 rounded-2xl shadow-xl border backdrop-blur-md flex items-center gap-2.5 text-sm font-medium transition-all transform animate-bounce duration-300 ${
						toast.type === 'error'
							? 'bg-red-500/90 text-white border-red-400'
							: 'bg-neutral-900/90 dark:bg-white/95 text-white dark:text-neutral-900 border-black/10 dark:border-white/20'
					}` }
				>
					<i id="raka-toast-icon" className={ toast.type === 'error' ? 'ri-error-warning-line text-lg' : 'ri-checkbox-circle-line text-lg text-green-400 dark:text-green-600' }></i>
					<span id="raka-toast-message">{ toast.message }</span>
				</div>
			) }

			{ /* ── Top Header Bar / User Profile Banner ── */ }
			<div id="raka-account-banner" className="relative p-5 sm:p-7 rounded-3xl bg-neutral-100/80 dark:bg-neutral-900/80 border border-black/10 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-5 shadow-xs">
				<div id="raka-account-user-info" className="flex items-center gap-4">
					<div id="raka-account-avatar-wrap" className="relative shrink-0">
						<img
							id="raka-account-avatar-img"
							src={ user.avatar_url || 'https://www.gravatar.com/avatar/?d=mp' }
							alt={ user.display_name }
							className="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover border-2 border-white dark:border-neutral-800 shadow-sm"
						/>
						<span id="raka-account-status-dot" className="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-green-500 border-2 border-white dark:border-neutral-900"></span>
					</div>
					<div id="raka-account-details-col" className="space-y-0.5">
						<div id="raka-account-name-row" className="flex items-center gap-2 flex-wrap">
							<h1 id="raka-account-user-name" className="text-lg sm:text-xl font-bold tracking-tight text-neutral-950 dark:text-white">
								{ user.display_name || user.username }
							</h1>
							<span id="raka-account-badge-member" className="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-neutral-200 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300">
								Member
							</span>
						</div>
						<p id="raka-account-user-email" className="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
							{ user.email }
						</p>
						<p id="raka-account-member-since" className="text-[11px] text-neutral-400 dark:text-neutral-500">
							สมาชิกตั้งแต่ { user.member_since }
						</p>
					</div>
				</div>

				{ /* Actions */ }
				<div id="raka-account-actions-wrap" className="flex items-center gap-2.5 shrink-0">
					<a
						id="raka-account-preorder-link"
						href="/pre-order/"
						className="h-10 px-4 rounded-full bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 border border-black/10 dark:border-white/10 text-xs sm:text-sm font-semibold transition-all shadow-xs flex items-center gap-2"
					>
						<i id="raka-preorder-link-icon" className="ri-flashlight-line text-red-600"></i>
						<span>พรีออเดอร์</span>
					</a>
					<button
						type="button"
						id="raka-account-logout-btn"
						onClick={ () => setShowLogoutModal( true ) }
						className="h-10 px-4 rounded-full bg-neutral-900 hover:bg-neutral-800 dark:bg-neutral-800 dark:hover:bg-neutral-700 text-white text-xs sm:text-sm font-semibold border border-black/10 dark:border-white/10 transition-all shadow-xs flex items-center gap-2 cursor-pointer"
					>
						<i id="raka-logout-icon" className="ri-logout-box-r-line"></i>
						<span>ออกจากระบบ</span>
					</button>
				</div>
			</div>

			{ /* ── Tab Navigation Pills ── */ }
			<div id="raka-account-nav-wrap" className="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
				{ tabs.map( ( tab ) => (
					<button
						key={ tab.id }
						type="button"
						id={ `raka-tab-${ tab.id }` }
						onClick={ () => setCurrentTab( tab.id ) }
						className={ `h-11 px-4 sm:px-5 rounded-full text-xs sm:text-sm font-semibold transition-all shrink-0 flex items-center gap-2 cursor-pointer ${
							currentTab === tab.id
								? 'bg-neutral-950 text-white dark:bg-white dark:text-neutral-950 shadow-sm'
								: 'bg-neutral-100/80 hover:bg-neutral-200/70 dark:bg-neutral-900/60 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-400 border border-black/5 dark:border-white/5'
						}` }
					>
						<i id={ `raka-tab-icon-${ tab.id }` } className={ `${ tab.icon } text-base` }></i>
						<span>{ tab.label }</span>
						{ typeof tab.badge === 'number' && tab.badge > 0 && (
							<span
								id={ `raka-tab-badge-${ tab.id }` }
								className={ `px-1.5 py-0.2 rounded-full text-[10px] font-bold ${
									currentTab === tab.id
										? 'bg-red-500 text-white'
										: 'bg-black/10 dark:bg-white/10 text-inherit'
								}` }
							>
								{ tab.badge }
							</span>
						) }
					</button>
				) ) }
			</div>

			{ /* ── Tab Content Views ── */ }
			<div id="raka-account-views-container" className="pt-1">
				{ currentTab === 'dashboard' && (
					<DashboardView
						user={ user }
						stats={ stats }
						recentOrders={ overview?.recent_orders || [] }
						onSelectTab={ setCurrentTab }
						onViewOrder={ setSelectedOrder }
					/>
				) }

				{ currentTab === 'orders' && (
					<OrdersView
						orders={ orders }
						loading={ ordersLoading }
						onRefresh={ fetchOrders }
						onViewOrder={ setSelectedOrder }
					/>
				) }

				{ currentTab === 'addresses' && (
					<AddressesView
						config={ config }
						addresses={ addresses }
						onEditAddress={ ( type ) => setEditingAddressType( type ) }
						onRefreshAddresses={ fetchAddresses }
						showToast={ showToast }
					/>
				) }

				{ currentTab === 'security' && (
					<SecurityView
						config={ config }
						passkeys={ passkeys }
						onRefreshPasskeys={ fetchPasskeys }
						showToast={ showToast }
					/>
				) }

				{ currentTab === 'rewards' && (
					<RewardsView
						config={ config }
						showToast={ showToast }
					/>
				) }
			</div>

			{ /* ── Modals ── */ }
			{ selectedOrder && (
				<OrderDetailModal
					order={ selectedOrder }
					onClose={ () => setSelectedOrder( null ) }
				/>
			) }

			{ editingAddressType && addresses && (
				<AddressEditModal
					type={ editingAddressType }
					address={ addresses[ editingAddressType ] || {} }
					config={ config }
					onClose={ () => setEditingAddressType( null ) }
					onSuccess={ () => {
						fetchAddresses();
						setEditingAddressType( null );
						showToast( 'บันทึกข้อมูลที่อยู่เรียบร้อยแล้ว' );
					} }
				/>
			) }

			{ showLogoutModal && (
				<LogoutModal
					logoutUrl={ config.logoutUrl }
					onClose={ () => setShowLogoutModal( false ) }
				/>
			) }

		</div>
	);
}
