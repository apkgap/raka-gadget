import { useState, useEffect, useRef } from '@wordpress/element';
import {
	Button,
	Card,
	CardHeader,
	CardBody,
	TabPanel,
	TextControl,
	TextareaControl,
	SelectControl,
	ToggleControl,
	Notice,
	Spinner,
	Flex,
	FlexItem,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Modern Segmented Tab Switcher Component.
 */
function TabSwitcher({ tabs, activeTab, onSelect, style }) {
	return (
		<div
			style={{
				display: 'inline-flex',
				background: '#f1f5f9',
				padding: '2px',
				borderRadius: '6px',
				border: '1px solid #e2e8f0',
				gap: '2px',
				...style,
			}}
		>
			{tabs.map((t) => {
				const isActive = activeTab === t.id;
				return (
					<button
						key={t.id}
						type="button"
						onClick={() => onSelect(t.id)}
						style={{
							border: 'none',
							background: isActive ? '#ffffff' : 'transparent',
							color: isActive ? '#0f172a' : '#64748b',
							fontWeight: isActive ? 600 : 500,
							fontSize: '11px',
							padding: '4px 10px',
							borderRadius: '4px',
							cursor: 'pointer',
							boxShadow: isActive ? '0 1px 2px rgba(0,0,0,0.08)' : 'none',
							display: 'inline-flex',
							alignItems: 'center',
							gap: '4px',
							transition: 'all 0.15s ease',
						}}
					>
						{t.icon && <i className={t.icon} style={{ fontSize: '13px', color: isActive ? '#2563eb' : '#94a3b8' }} />}
						<span>{t.label}</span>
					</button>
				);
			})}
		</div>
	);
}

/**
 * Compact Reusable WordPress Media Modal Uploader Component.
 */
function MediaUploaderControl({
	label,
	value,
	onChange,
	help,
	mediaType = 'image',
	title = __('Select Media', 'raka-preorder-theme'),
	buttonText = __('Use This Media', 'raka-preorder-theme'),
}) {
	const openModal = () => {
		if (!window.wp || !window.wp.media) {
			alert(__('WordPress Media library is not loaded. Please refresh the page.', 'raka-preorder-theme'));
			return;
		}

		const frame = window.wp.media({
			title,
			button: { text: buttonText },
			multiple: false,
			library: { type: mediaType },
		});

		frame.on('select', () => {
			const attachment = frame.state().get('selection').first().toJSON();
			if (attachment && attachment.url) {
				onChange(attachment.url);
			}
		});

		frame.open();
	};

	return (
		<div style={{ marginBottom: '8px' }}>
			{label && (
				<label style={{ display: 'block', fontWeight: 600, fontSize: '12px', marginBottom: '4px', color: '#334155' }}>
					{label}
				</label>
			)}

			{value ? (
				<div
					style={{
						display: 'flex',
						alignItems: 'center',
						gap: '10px',
						padding: '8px 12px',
						background: '#ffffff',
						border: '1px solid #e2e8f0',
						borderRadius: '8px',
					}}
				>
					{mediaType === 'image' && (
						<img
							src={value}
							alt=""
							style={{
								width: '44px',
								height: '44px',
								objectFit: 'cover',
								borderRadius: '6px',
								border: '1px solid #cbd5e1',
								background: '#0f172a',
								flexShrink: 0,
							}}
						/>
					)}
					{mediaType === 'video' && (
						<div
							style={{
								width: '44px',
								height: '44px',
								borderRadius: '6px',
								background: '#0f172a',
								color: '#fff',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'center',
								flexShrink: 0,
							}}
						>
							<i className="ri-movie-line" style={{ fontSize: '20px' }} />
						</div>
					)}
					<div style={{ flex: 1, minWidth: 0 }}>
						<div style={{ fontSize: '10px', fontWeight: 600, color: '#64748b', textTransform: 'uppercase' }}>
							{__('Selected Media', 'raka-preorder-theme')}
						</div>
						<div style={{ fontSize: '11px', color: '#1e293b', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontFamily: 'monospace' }}>
							{value}
						</div>
					</div>
					<div style={{ display: 'flex', gap: '4px', flexShrink: 0 }}>
						<Button variant="secondary" onClick={openModal} size="small">
							<i className="ri-folder-image-line" style={{ marginRight: '3px' }} />
							{__('Change', 'raka-preorder-theme')}
						</Button>
						<Button variant="secondary" isDestructive onClick={() => onChange('')} size="small">
							<i className="ri-delete-bin-line" />
						</Button>
					</div>
				</div>
			) : (
				<div
					onClick={openModal}
					style={{
						border: '1.5px dashed #cbd5e1',
						borderRadius: '8px',
						padding: '12px 14px',
						textAlign: 'center',
						cursor: 'pointer',
						background: '#ffffff',
						transition: 'all 0.15s ease',
						display: 'flex',
						alignItems: 'center',
						justifyContent: 'center',
						gap: '8px',
					}}
					onMouseEnter={(e) => {
						e.currentTarget.style.borderColor = '#3b82f6';
						e.currentTarget.style.background = '#f0f7ff';
					}}
					onMouseLeave={(e) => {
						e.currentTarget.style.borderColor = '#cbd5e1';
						e.currentTarget.style.background = '#ffffff';
					}}
				>
					<i
						className={mediaType === 'video' ? 'ri-video-upload-line' : 'ri-image-add-line'}
						style={{ fontSize: '20px', color: '#2563eb' }}
					/>
					<span style={{ fontSize: '12px', fontWeight: 600, color: '#2563eb' }}>
						{mediaType === 'video'
							? __('Choose Video from Media Library', 'raka-preorder-theme')
							: __('Choose Image from Media Library', 'raka-preorder-theme')}
					</span>
				</div>
			)}

			{help && <p style={{ margin: '4px 0 0 0', fontSize: '11px', color: '#646970' }}>{help}</p>}
		</div>
	);
}

/**
 * Reusable Select2 component integrated into WordPress Component style.
 */
function Select2Control({
	label,
	value,
	options = [],
	onChange,
	placeholder,
	icon,
	help,
}) {
	const selectRef = useRef(null);

	useEffect(() => {
		if (!selectRef.current || !window.jQuery || !window.jQuery.fn.select2) {
			return;
		}

		const $select = window.jQuery(selectRef.current);

		$select.select2({
			placeholder: placeholder || __('-- Select --', 'raka-preorder-theme'),
			allowClear: true,
			width: '100%',
		});

		const handleSelectChange = (e) => {
			const newVal = e.target.value;
			if (onChange) {
				onChange(newVal);
			}
		};

		$select.on('change', handleSelectChange);

		return () => {
			$select.off('change', handleSelectChange);
			if ($select.data('select2')) {
				$select.select2('destroy');
			}
		};
	}, [options, placeholder]);

	// Sync external state changes into Select2
	useEffect(() => {
		if (selectRef.current && window.jQuery && window.jQuery.fn.select2) {
			const $select = window.jQuery(selectRef.current);
			const currentVal = $select.val();
			const targetVal = String(value ?? '');
			if (currentVal !== targetVal) {
				$select.val(targetVal).trigger('change.select2');
			}
		}
	}, [value]);

	return (
		<div className="raka-select2-field" style={{ marginBottom: '8px' }}>
			{label && (
				<label
					style={{
						display: 'flex',
						alignItems: 'center',
						gap: '6px',
						fontWeight: 600,
						fontSize: '12px',
						marginBottom: '4px',
						color: '#334155',
					}}
				>
					{icon && <i className={icon} style={{ fontSize: '14px', color: '#2271b1' }} />}
					<span>{label}</span>
				</label>
			)}
			<select
				ref={selectRef}
				defaultValue={value ?? ''}
				style={{ width: '100%' }}
			>
				<option value="">{placeholder || __('-- Select an option --', 'raka-preorder-theme')}</option>
				{options.map((opt) => (
					<option key={opt.value} value={opt.value}>
						{opt.label}
					</option>
				))}
			</select>
			{help && (
				<p style={{ margin: '4px 0 0 0', fontSize: '11px', color: '#646970' }}>
					{help}
				</p>
			)}
		</div>
	);
}

const POSITION_OPTIONS = [
	{ label: __('Left Pinned (Left Side)', 'raka-preorder-theme'), value: 'left' },
	{ label: __('Right Pinned (Right Side)', 'raka-preorder-theme'), value: 'right' },
	{ label: __('Center Card', 'raka-preorder-theme'), value: 'center' },
	{ label: __('Bottom Left Card', 'raka-preorder-theme'), value: 'bottom_left' },
	{ label: __('Bottom Right Card', 'raka-preorder-theme'), value: 'bottom_right' },
];

export default function ThemeSettingsApp() {
	const [loading, setLoading] = useState(true);
	const [saving, setSaving] = useState(false);
	const [notice, setNotice] = useState(null);
	const [options, setOptions] = useState({
		hero_main_title: 'OBSIDIAN',
		hero_caption: "RITUALS REFINED:\nTRACING LINE, SHADOW & SKIN\nACROSS A LIVING FORM.",
		hero_video_url: '',
		hero_overlay_url: '',
		hero_cards: [],
		contact_title: 'CONTACT & STORE',
		contact_subtitle: 'Get in touch with our team for consultations, orders, and inquiries.',
		contact_line_id: '@rakagadget',
		contact_line_url: 'https://line.me/ti/p/@rakagadget',
		contact_facebook_url: 'https://facebook.com/rakagadget',
		contact_phone: '081-234-5678',
		contact_email: 'contact@raka-gadget.com',
		contact_hours: 'Mon - Sun: 10:00 - 20:00 (Daily)',
		contact_address: '123/45 Gadget District, Bangkok 10110',
		contact_note: 'For custom inquiries, private showroom sessions, or pre-order status, message us directly via LINE Official 24/7.',
		flash_sale_enabled: true,
		flash_sale_schedule_mode: 'fixed',
		flash_sale_limit: 4,
		flash_sale_title: 'FLASH SALE',
		flash_sale_tag: 'LIMITED TIME DROPS',
		flash_sale_desc: 'Exclusive pre-order allocations at special introductory prices. Once stock is claimed, regular rates apply.',
		flash_sale_end_time: '',
		flash_sale_daily_start: '18:00',
		flash_sale_daily_end: '23:59',
		flash_sale_campaign_start_date: '',
		flash_sale_campaign_end_date: '',
		our_products_limit: 8,
		our_products_title: 'OUR PRODUCTS',
		our_products_desc: 'Explore custom keyboards, precision ergonomic gaming mice, and studio-grade audio gear built for uncompromising performance.',
		turnstile_enabled: true,
		turnstile_sitekey: '0x4AAAAAAFNWAI83P9cCw0AU',
		turnstile_secret: '0x4AAAAAAFNWAKM9TKecHcitT5juwPWd4gM',
		turnstile_theme: 'auto',
		preorder_require_member_login: false,
	});
	const [wcData, setWcData] = useState({
		categories: [],
		brands: [],
		products: [],
	});
	const [activeCardIndex, setActiveCardIndex] = useState(0);

	// Card sub-tab tracking state (per card index)
	const [cardTabModes, setCardTabModes] = useState({});

	// Fetch initial settings & WooCommerce terms from REST API
	useEffect(() => {
		apiFetch({ path: '/raka/v1/settings' })
			.then((res) => {
				if (res && res.options) {
					setOptions((prev) => ({
						...prev,
						...res.options,
						hero_cards: Array.isArray(res.options.hero_cards) ? res.options.hero_cards : [],
					}));
					setWcData({
						categories: res.categories || [],
						brands: res.brands || [],
						products: res.products || [],
					});
				}
				setLoading(false);
			})
			.catch((err) => {
				setNotice({
					type: 'error',
					message: err.message || __('Failed to load theme settings.', 'raka-preorder-theme'),
				});
				setLoading(false);
			});
	}, []);

	const handleOptionChange = (field, val) => {
		setOptions((prev) => ({
			...prev,
			[field]: val,
		}));
	};

	// Repeater handlers
	const heroCards = Array.isArray(options.hero_cards) ? options.hero_cards : [];

	const handleCardChange = (index, field, value) => {
		const updated = [...heroCards];
		updated[index] = {
			...updated[index],
			[field]: value,
		};

		// If product_id changed, auto-populate details
		if (field === 'product_id') {
			const prodId = Number(value);
			const matched = wcData.products.find((p) => p.id === prodId);
			if (matched) {
				updated[index].title = matched.title || '';
				updated[index].price = matched.price || '';
				updated[index].url = matched.permalink || '';
				updated[index].image_url = matched.image_url || '';
				updated[index].description = matched.description || '';
			}
		}

		handleOptionChange('hero_cards', updated);
	};

	const handleAddCard = () => {
		const newCard = {
			product_id: 0,
			show_custom_text: false,
			tag: 'PRE-ORDER',
			title: '',
			description: '',
			price: '',
			url: '',
			image_url: '',
			button_text: 'Pre-Order Now',
			position: heroCards.length % 2 === 0 ? 'left' : 'right',
		};
		const updated = [...heroCards, newCard];
		handleOptionChange('hero_cards', updated);
		setActiveCardIndex(updated.length - 1);
	};

	const handleRemoveCard = (index) => {
		if (window.confirm(__('Are you sure you want to remove this hero card?', 'raka-preorder-theme'))) {
			const updated = heroCards.filter((_, i) => i !== index);
			handleOptionChange('hero_cards', updated);
			if (activeCardIndex >= updated.length) {
				setActiveCardIndex(Math.max(0, updated.length - 1));
			}
		}
	};

	const handleMoveCard = (index, direction) => {
		const targetIndex = index + direction;
		if (targetIndex < 0 || targetIndex >= heroCards.length) {
			return;
		}
		const updated = [...heroCards];
		const item = updated[index];
		updated[index] = updated[targetIndex];
		updated[targetIndex] = item;
		handleOptionChange('hero_cards', updated);
		setActiveCardIndex(targetIndex);
	};

	const handleDuplicateCard = (index) => {
		const target = heroCards[index];
		if (!target) return;
		const duplicated = {
			...target,
			title: target.title ? `${target.title} (Copy)` : '',
		};
		const updated = [...heroCards];
		updated.splice(index + 1, 0, duplicated);
		handleOptionChange('hero_cards', updated);
		setActiveCardIndex(index + 1);
	};

	const handleSave = () => {
		setSaving(true);
		setNotice(null);

		apiFetch({
			path: '/raka/v1/settings',
			method: 'POST',
			data: { options },
		})
			.then((res) => {
				setSaving(false);
				if (res && res.success) {
					setNotice({
						type: 'success',
						message: res.message || __('Hero Banner settings saved successfully!', 'raka-preorder-theme'),
					});
				}
			})
			.catch((err) => {
				setSaving(false);
				setNotice({
					type: 'error',
					message: err.message || __('Failed to save settings.', 'raka-preorder-theme'),
				});
			});
	};

	if (loading) {
		return (
			<div style={{ padding: '80px 20px', textAlign: 'center' }}>
				<Spinner />
				<p style={{ marginTop: '14px', color: '#64748b', fontSize: '14px' }}>
					{__('Loading Hero Banner settings...', 'raka-preorder-theme')}
				</p>
			</div>
		);
	}

	// Prepare Product Select options
	const productOptions = wcData.products.map((p) => ({
		value: String(p.id),
		label: `${p.title} ${p.price ? '- ' + p.price : ''}`,
	}));

	const tabs = [
		{
			name: 'hero_repeater',
			title: (
				<span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}>
					<i className="ri-stack-line" style={{ fontSize: '16px' }} />
					{__('Product Cards (Repeater)', 'raka-preorder-theme')}
					<span
						style={{
							background: '#2563eb',
							color: '#fff',
							borderRadius: '10px',
							padding: '1px 8px',
							fontSize: '11px',
							fontWeight: 700,
							marginLeft: '4px',
						}}
					>
						{heroCards.length}
					</span>
				</span>
			),
		},
		{
			name: 'homepage_sections',
			title: (
				<span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}>
					<i className="ri-layout-grid-line" style={{ fontSize: '16px' }} />
					{__('Flash Sale & Our Products', 'raka-preorder-theme')}
				</span>
			),
		},
		{
			name: 'hero_general',
			title: (
				<span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}>
					<i className="ri-movie-line" style={{ fontSize: '16px' }} />
					{__('Hero Media & Title', 'raka-preorder-theme')}
				</span>
			),
		},
		{
			name: 'hero_contact',
			title: (
				<span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}>
					<i className="ri-customer-service-2-line" style={{ fontSize: '16px' }} />
					{__('Contact & Store Info', 'raka-preorder-theme')}
				</span>
			),
		},
		{
			name: 'security_settings',
			title: (
				<span style={{ display: 'inline-flex', alignItems: 'center', gap: '6px' }}>
					<i className="ri-shield-keyhole-line" style={{ fontSize: '16px', color: '#f59e0b' }} />
					{__('Pre-Order & Security', 'raka-preorder-theme')}
				</span>
			),
		},
	];

	// Detect initial active tab from page or tab URL parameter
	const urlParams = new URLSearchParams(window.location.search);
	const pageParam = urlParams.get('page') || '';
	const tabParam = urlParams.get('tab') || '';

	let defaultTabName = 'hero_repeater';
	if (tabParam === 'homepage_sections' || tabParam === 'products' || pageParam === 'raka-theme-settings-products') {
		defaultTabName = 'homepage_sections';
	} else if (tabParam === 'hero_general' || tabParam === 'media' || pageParam === 'raka-theme-settings-media') {
		defaultTabName = 'hero_general';
	} else if (tabParam === 'hero_contact' || tabParam === 'contact' || pageParam === 'raka-theme-settings-contact') {
		defaultTabName = 'hero_contact';
	} else if (tabParam === 'security_settings' || tabParam === 'security' || pageParam === 'raka-theme-settings-security') {
		defaultTabName = 'security_settings';
	}

	return (
		<div
			style={{
				width: '100%',
				maxWidth: '1560px',
				margin: '16px auto',
				padding: '0 24px 60px 24px',
				boxSizing: 'border-box',
				fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
			}}
		>
			{ /* Top Header Banner */}
			<div
				style={{
					background: '#0f172a',
					color: '#fff',
					borderRadius: '12px',
					padding: '20px 28px',
					marginBottom: '20px',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'space-between',
					boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.2)',
					border: '1px solid #1e293b',
				}}
			>
				<div style={{ display: 'flex', alignItems: 'center', gap: '14px' }}>
					<div
						style={{
							width: '42px',
							height: '42px',
							borderRadius: '10px',
							background: 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
							display: 'flex',
							alignItems: 'center',
							justifyContent: 'center',
							boxShadow: '0 4px 12px rgba(59, 130, 246, 0.3)',
						}}
					>
						<i className="ri-shopping-bag-3-fill" style={{ fontSize: '22px', color: '#fff' }} />
					</div>
					<div>
						<h1 style={{ margin: 0, fontSize: '20px', fontWeight: 700, color: '#fff', letterSpacing: '-0.02em' }}>
							{__('Hero Banner & Product Cards Settings', 'raka-preorder-theme')}
						</h1>
						<p style={{ margin: '2px 0 0 0', color: '#94a3b8', fontSize: '13px' }}>
							{__('Configure scroll-activated 4-card slides, WooCommerce select2/custom link, media picker, and video scrubbing.', 'raka-preorder-theme')}
						</p>
					</div>
				</div>

				<div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
					<Button
						variant="primary"
						onClick={handleSave}
						isBusy={saving}
						disabled={saving}
						style={{
							height: '40px',
							padding: '0 24px',
							fontSize: '13px',
							fontWeight: 600,
							display: 'inline-flex',
							alignItems: 'center',
							gap: '6px',
							borderRadius: '8px',
							background: '#3b82f6',
							borderColor: '#3b82f6',
							boxShadow: '0 2px 8px rgba(59, 130, 246, 0.35)',
						}}
					>
						<i className="ri-save-3-line" style={{ fontSize: '16px' }} />
						{saving ? __('Saving...', 'raka-preorder-theme') : __('Save All Changes', 'raka-preorder-theme')}
					</Button>
				</div>
			</div>

			{notice && (
				<Notice
					status={notice.type}
					onRemove={() => setNotice(null)}
					style={{ marginBottom: '20px' }}
				>
					{notice.message}
				</Notice>
			)}

			<Card style={{ borderRadius: '12px', border: '1px solid #e2e8f0', boxShadow: '0 1px 3px rgba(0,0,0,0.04)', overflow: 'hidden' }}>
				<TabPanel
					className="raka-theme-settings-tab-panel"
					activeClass="active-tab"
					initialTabName={defaultTabName}
					tabs={tabs}
				>
					{(tab) => {
						if (tab.name === 'homepage_sections') {
							return (
								<CardBody style={{ padding: '28px 32px' }}>
									<div style={{ marginBottom: '24px' }}>
										<h2 style={{ margin: '0 0 4px 0', fontSize: '17px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', color: '#0f172a' }}>
											<i className="ri-layout-grid-line" style={{ color: '#ef4444' }} />
											{__('Flash Sale & Our Products Display Settings', 'raka-preorder-theme')}
										</h2>
										<p style={{ color: '#64748b', fontSize: '13px', margin: 0 }}>
											{__('Control the number of items and rows displayed on the homepage for Flash Sale Deals and .', 'raka-preorder-theme')}
										</p>
									</div>

									<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '24px', alignItems: 'start' }}>
										{ /* 1. Flash Sale Settings Box */}
										<div style={{ padding: '20px 24px', background: '#f8fafc', borderRadius: '12px', border: '1px solid #e2e8f0', boxShadow: '0 1px 3px rgba(0,0,0,0.02)' }}>
											<div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px', paddingBottom: '12px', borderBottom: '1px solid #e2e8f0' }}>
												<div style={{ width: '32px', height: '32px', borderRadius: '8px', background: '#fee2e2', color: '#ef4444', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
													<i className="ri-flashlight-line" style={{ fontSize: '18px' }} />
												</div>
												<div>
													<h3 style={{ margin: 0, fontSize: '15px', fontWeight: 700, color: '#0f172a' }}>
														{__('Flash Sale Section', 'raka-preorder-theme')}
													</h3>
													<p style={{ margin: 0, fontSize: '11px', color: '#64748b' }}>
														{__('Discounted items with countdown & urgency stock bar', 'raka-preorder-theme')}
													</p>
												</div>
											</div>

											<div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
												<ToggleControl
													label={__('Enable Flash Sale Section', 'raka-preorder-theme')}
													help={options.flash_sale_enabled !== false ? __('Flash Sale is active and visible on the homepage.', 'raka-preorder-theme') : __('Flash Sale is hidden.', 'raka-preorder-theme')}
													checked={options.flash_sale_enabled !== false}
													onChange={(val) => handleOptionChange('flash_sale_enabled', val)}
												/>

												{ /* Schedule & Countdown Mode Switcher */}
												<div style={{ display: 'flex', flexDirection: 'column', gap: '10px', padding: '12px 14px', background: '#ffffff', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
													<div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '8px' }}>
														<label style={{ fontSize: '11px', fontWeight: 700, color: '#1e293b', textTransform: 'uppercase', letterSpacing: '0.05em' }}>
															{__('Schedule & Timer Mode', 'raka-preorder-theme')}
														</label>
														<TabSwitcher
															tabs={[
																{ id: 'fixed', label: __('Countdown Target', 'raka-preorder-theme'), icon: 'ri-calendar-event-line' },
																{ id: 'daily', label: __('Daily Slot & Expiry', 'raka-preorder-theme'), icon: 'ri-time-line' },
															]}
															activeTab={options.flash_sale_schedule_mode || 'fixed'}
															onSelect={(mode) => handleOptionChange('flash_sale_schedule_mode', mode)}
														/>
													</div>

													{(options.flash_sale_schedule_mode || 'fixed') === 'fixed' ? (
														<div style={{ display: 'flex', flexDirection: 'column', gap: '4px', paddingTop: '4px' }}>
															<label style={{ fontSize: '11px', fontWeight: 600, color: '#475569' }}>
																{__('Target End Date & Time (Countdown Target)', 'raka-preorder-theme')}
															</label>
															<input
																type="datetime-local"
																value={options.flash_sale_end_time || ''}
																onChange={(e) => handleOptionChange('flash_sale_end_time', e.target.value)}
																style={{
																	padding: '8px 12px',
																	border: '1px solid #cbd5e1',
																	borderRadius: '6px',
																	fontSize: '13px',
																	background: '#fff',
																	color: '#0f172a',
																	outline: 'none',
																}}
															/>
															<p style={{ margin: 0, fontSize: '11px', color: '#64748b' }}>
																{__('The live clock will count down to this specific end date and time continuously.', 'raka-preorder-theme')}
															</p>
														</div>
													) : (
														<div style={{ display: 'flex', flexDirection: 'column', gap: '10px', paddingTop: '4px' }}>
															<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
																<div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
																	<label style={{ fontSize: '11px', fontWeight: 600, color: '#475569' }}>
																		{__('Daily Start Time', 'raka-preorder-theme')}
																	</label>
																	<input
																		type="time"
																		value={options.flash_sale_daily_start || '18:00'}
																		onChange={(e) => handleOptionChange('flash_sale_daily_start', e.target.value)}
																		style={{ padding: '7px 10px', border: '1px solid #cbd5e1', borderRadius: '6px', fontSize: '13px', background: '#fff', color: '#0f172a' }}
																	/>
																</div>
																<div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
																	<label style={{ fontSize: '11px', fontWeight: 600, color: '#475569' }}>
																		{__('Daily End Time', 'raka-preorder-theme')}
																	</label>
																	<input
																		type="time"
																		value={options.flash_sale_daily_end || '23:59'}
																		onChange={(e) => handleOptionChange('flash_sale_daily_end', e.target.value)}
																		style={{ padding: '7px 10px', border: '1px solid #cbd5e1', borderRadius: '6px', fontSize: '13px', background: '#fff', color: '#0f172a' }}
																	/>
																</div>
															</div>

															<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '10px' }}>
																<div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
																	<label style={{ fontSize: '11px', fontWeight: 600, color: '#475569' }}>
																		{__('Campaign Start Date (Optional)', 'raka-preorder-theme')}
																	</label>
																	<input
																		type="date"
																		value={options.flash_sale_campaign_start_date || ''}
																		onChange={(e) => handleOptionChange('flash_sale_campaign_start_date', e.target.value)}
																		style={{ padding: '7px 10px', border: '1px solid #cbd5e1', borderRadius: '6px', fontSize: '13px', background: '#fff', color: '#0f172a' }}
																	/>
																</div>
																<div style={{ display: 'flex', flexDirection: 'column', gap: '4px' }}>
																	<label style={{ fontSize: '11px', fontWeight: 600, color: '#475569' }}>
																		{__('Campaign End Date (Expiry)', 'raka-preorder-theme')}
																	</label>
																	<input
																		type="date"
																		value={options.flash_sale_campaign_end_date || ''}
																		onChange={(e) => handleOptionChange('flash_sale_campaign_end_date', e.target.value)}
																		style={{ padding: '7px 10px', border: '1px solid #cbd5e1', borderRadius: '6px', fontSize: '13px', background: '#fff', color: '#0f172a' }}
																	/>
																</div>
															</div>
															<p style={{ margin: 0, fontSize: '11px', color: '#64748b' }}>
																{__('Runs Flash Sale during the daily time slot until the campaign end date.', 'raka-preorder-theme')}
															</p>
														</div>
													)}
												</div>

												<SelectControl
													label={__('Number of Products to Display (Rows)', 'raka-preorder-theme')}
													value={String(options.flash_sale_limit ?? 4)}
													options={[
														{ label: __('4 Products (1 Row - Recommended)', 'raka-preorder-theme'), value: '4' },
														{ label: __('8 Products (2 Rows)', 'raka-preorder-theme'), value: '8' },
														{ label: __('12 Products (3 Rows)', 'raka-preorder-theme'), value: '12' },
														{ label: __('16 Products (4 Rows)', 'raka-preorder-theme'), value: '16' },
													]}
													onChange={(val) => handleOptionChange('flash_sale_limit', parseInt(val, 10) || 4)}
													help={__('Currently configured to show on the homepage flash sale grid.', 'raka-preorder-theme')}
												/>

												<TextControl
													label={__('Flash Sale Badge Tag', 'raka-preorder-theme')}
													value={options.flash_sale_tag ?? 'LIMITED TIME DROPS'}
													onChange={(val) => handleOptionChange('flash_sale_tag', val)}
												/>

												<TextControl
													label={__('Flash Sale Section Title', 'raka-preorder-theme')}
													value={options.flash_sale_title ?? 'FLASH SALE'}
													onChange={(val) => handleOptionChange('flash_sale_title', val)}
												/>

												<TextareaControl
													label={__('Flash Sale Description Text', 'raka-preorder-theme')}
													rows={2}
													value={options.flash_sale_desc ?? 'Exclusive pre-order allocations at special introductory prices. Once stock is claimed, regular rates apply.'}
													onChange={(val) => handleOptionChange('flash_sale_desc', val)}
												/>
											</div>
										</div>

										{ /* 2. Our Products Settings Box */}
										<div style={{ padding: '20px 24px', background: '#f8fafc', borderRadius: '12px', border: '1px solid #e2e8f0', boxShadow: '0 1px 3px rgba(0,0,0,0.02)' }}>
											<div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px', paddingBottom: '12px', borderBottom: '1px solid #e2e8f0' }}>
												<div style={{ width: '32px', height: '32px', borderRadius: '8px', background: '#e0f2fe', color: '#0284c7', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
													<i className="ri-layout-grid-fill" style={{ fontSize: '18px' }} />
												</div>
												<div>
													<h3 style={{ margin: 0, fontSize: '15px', fontWeight: 700, color: '#0f172a' }}>
														{__('Our Products Section', 'raka-preorder-theme')}
													</h3>
													<p style={{ margin: 0, fontSize: '11px', color: '#64748b' }}>
														{__(' grid with category filtering tabs', 'raka-preorder-theme')}
													</p>
												</div>
											</div>

											<div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
												<SelectControl
													label={__('Number of Products to Display (Rows)', 'raka-preorder-theme')}
													value={String(options.our_products_limit ?? 8)}
													options={[
														{ label: __('4 Products (1 Row)', 'raka-preorder-theme'), value: '4' },
														{ label: __('8 Products (2 Rows - Default)', 'raka-preorder-theme'), value: '8' },
														{ label: __('12 Products (3 Rows)', 'raka-preorder-theme'), value: '12' },
														{ label: __('16 Products (4 Rows)', 'raka-preorder-theme'), value: '16' },
														{ label: __('20 Products (5 Rows)', 'raka-preorder-theme'), value: '20' },
														{ label: __('24 Products (6 Rows)', 'raka-preorder-theme'), value: '24' },
													]}
													onChange={(val) => handleOptionChange('our_products_limit', parseInt(val, 10) || 8)}
													help={__('Currently set to 2 rows (8 products) on desktop.', 'raka-preorder-theme')}
												/>

												<TextControl
													label={__('Our Products Section Title', 'raka-preorder-theme')}
													value={options.our_products_title ?? 'OUR PRODUCTS'}
													onChange={(val) => handleOptionChange('our_products_title', val)}
												/>

												<TextareaControl
													label={__('Section Description Text', 'raka-preorder-theme')}
													rows={2}
													value={options.our_products_desc ?? 'Explore custom keyboards, precision ergonomic gaming mice, and studio-grade audio gear built for uncompromising performance.'}
													onChange={(val) => handleOptionChange('our_products_desc', val)}
												/>
											</div>
										</div>
									</div>
								</CardBody>
							);
						}

						if (tab.name === 'hero_general') {
							return (
								<CardBody style={{ padding: '28px 32px' }}>
									<div style={{ marginBottom: '24px' }}>
										<h2 style={{ margin: '0 0 4px 0', fontSize: '17px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', color: '#0f172a' }}>
											<i className="ri-movie-2-line" style={{ color: '#3b82f6' }} />
											{__('Main Title & Video Media', 'raka-preorder-theme')}
										</h2>
										<p style={{ color: '#64748b', fontSize: '13px', margin: 0 }}>
											{__('These controls manage the background video scrubbing, character overlay PNG, and primary typography.', 'raka-preorder-theme')}
										</p>
									</div>

									<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px', marginBottom: '20px' }}>
										<div>
											<TextControl
												label={__('Hero Main Title Text', 'raka-preorder-theme')}
												value={options.hero_main_title ?? 'OBSIDIAN'}
												onChange={(val) => handleOptionChange('hero_main_title', val)}
												help={__('Massive centered typography displayed over the hero banner.', 'raka-preorder-theme')}
											/>
										</div>
										<div>
											<TextareaControl
												label={__('Hero Caption (Bottom Left)', 'raka-preorder-theme')}
												rows={3}
												value={options.hero_caption ?? "RITUALS REFINED:\nTRACING LINE, SHADOW & SKIN\nACROSS A LIVING FORM."}
												onChange={(val) => handleOptionChange('hero_caption', val)}
												help={__('Short poetic caption displayed at the bottom-left of the hero banner.', 'raka-preorder-theme')}
											/>
										</div>
									</div>

									{ /* Hero Video Section with Media Picker + URL fallback */}
									<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '20px' }}>
										<div style={{ padding: '16px 20px', background: '#f8fafc', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
											<MediaUploaderControl
												label={__('Background Video (MP4 Scrubbing)', 'raka-preorder-theme')}
												value={options.hero_video_url ?? ''}
												mediaType="video"
												title={__('Select Hero Background Video', 'raka-preorder-theme')}
												buttonText={__('Use This Video', 'raka-preorder-theme')}
												onChange={(val) => handleOptionChange('hero_video_url', val)}
												help={__('Direct URL or media file to a 60fps MP4 video used for the scroll scrubber.', 'raka-preorder-theme')}
											/>
											<TextControl
												label={__('Or Enter Direct Video URL', 'raka-preorder-theme')}
												value={options.hero_video_url ?? ''}
												onChange={(val) => handleOptionChange('hero_video_url', val)}
												placeholder="https://.../video.mp4"
											/>
										</div>

										{ /* Hero Overlay Character PNG */}
										<div style={{ padding: '16px 20px', background: '#f8fafc', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
											<MediaUploaderControl
												label={__('Hero Character Overlay (Transparent PNG Asset)', 'raka-preorder-theme')}
												value={options.hero_overlay_url ?? ''}
												mediaType="image"
												title={__('Select Hero Character Overlay PNG', 'raka-preorder-theme')}
												buttonText={__('Use This PNG', 'raka-preorder-theme')}
												onChange={(val) => handleOptionChange('hero_overlay_url', val)}
												help={__('Transparent PNG of the character overlay that fades during scrolling.', 'raka-preorder-theme')}
											/>
											<TextControl
												label={__('Or Enter Direct Image URL', 'raka-preorder-theme')}
												value={options.hero_overlay_url ?? ''}
												onChange={(val) => handleOptionChange('hero_overlay_url', val)}
												placeholder="https://.../character-overlay.png"
											/>
										</div>
									</div>
								</CardBody>
							);
						}

						// Contact & Store Settings Tab
						if (tab.name === 'hero_contact') {
							return (
								<CardBody style={{ padding: '28px 32px' }}>
									<div style={{ marginBottom: '24px' }}>
										<h2 style={{ margin: '0 0 4px 0', fontSize: '17px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', color: '#0f172a' }}>
											<i className="ri-customer-service-2-line" style={{ color: '#10b981' }} />
											{__('Contact & Store Information Settings', 'raka-preorder-theme')}
										</h2>
										<p style={{ color: '#64748b', fontSize: '13px', margin: 0 }}>
											{__('Configure the bottom-left contact popup content: LINE Official, Facebook, hotline phone, studio hours, and address.', 'raka-preorder-theme')}
										</p>
									</div>

									{ /* 2-Column: Form Controls on Left, Live Preview Card on Right */}
									<div style={{ display: 'grid', gridTemplateColumns: '1.2fr 0.8fr', gap: '28px', alignItems: 'start' }}>
										{ /* Form Fields */}
										<div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
											<div style={{ padding: '18px 20px', background: '#f8fafc', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
												<h3 style={{ margin: '0 0 14px 0', fontSize: '14px', fontWeight: 700, color: '#1e293b' }}>
													{__('Modal Header Texts', 'raka-preorder-theme')}
												</h3>
												<TextControl
													label={__('Modal Title / Button Label', 'raka-preorder-theme')}
													value={options.contact_title ?? 'CONTACT & STORE'}
													onChange={(val) => handleOptionChange('contact_title', val)}
													help={__('Title displayed on the bottom-left hero button and modal header.', 'raka-preorder-theme')}
												/>
												<TextControl
													label={__('Modal Subtitle', 'raka-preorder-theme')}
													value={options.contact_subtitle ?? 'Get in touch with our team for consultations, orders, and inquiries.'}
													onChange={(val) => handleOptionChange('contact_subtitle', val)}
												/>
											</div>

											<div style={{ padding: '18px 20px', background: '#f8fafc', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
												<h3 style={{ margin: '0 0 14px 0', fontSize: '14px', fontWeight: 700, color: '#1e293b' }}>
													{__('Social & Direct Channels', 'raka-preorder-theme')}
												</h3>
												<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px' }}>
													<TextControl
														label={__('LINE Official ID', 'raka-preorder-theme')}
														value={options.contact_line_id ?? '@rakagadget'}
														onChange={(val) => handleOptionChange('contact_line_id', val)}
														placeholder="@rakagadget"
													/>
													<TextControl
														label={__('LINE Add Friend URL', 'raka-preorder-theme')}
														value={options.contact_line_url ?? 'https://line.me/ti/p/@rakagadget'}
														onChange={(val) => handleOptionChange('contact_line_url', val)}
														placeholder="https://line.me/ti/p/..."
													/>
												</div>
												<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '12px', marginTop: '12px' }}>
													<TextControl
														label={__('Facebook Page URL', 'raka-preorder-theme')}
														value={options.contact_facebook_url ?? 'https://facebook.com/rakagadget'}
														onChange={(val) => handleOptionChange('contact_facebook_url', val)}
														placeholder="https://facebook.com/..."
													/>
													<TextControl
														label={__('Customer Hotline Phone', 'raka-preorder-theme')}
														value={options.contact_phone ?? '081-234-5678'}
														onChange={(val) => handleOptionChange('contact_phone', val)}
														placeholder="081-234-5678"
													/>
												</div>
												<div style={{ marginTop: '12px' }}>
													<TextControl
														label={__('Support Email Address', 'raka-preorder-theme')}
														value={options.contact_email ?? 'contact@raka-gadget.com'}
														onChange={(val) => handleOptionChange('contact_email', val)}
														placeholder="contact@domain.com"
													/>
												</div>
											</div>

											<div style={{ padding: '18px 20px', background: '#f8fafc', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
												<h3 style={{ margin: '0 0 14px 0', fontSize: '14px', fontWeight: 700, color: '#1e293b' }}>
													{__('Studio Location & Hours', 'raka-preorder-theme')}
												</h3>
												<TextControl
													label={__('Service & Opening Hours', 'raka-preorder-theme')}
													value={options.contact_hours ?? 'Mon - Sun: 10:00 - 20:00 (Daily)'}
													onChange={(val) => handleOptionChange('contact_hours', val)}
												/>
												<TextControl
													label={__('Store Physical Address', 'raka-preorder-theme')}
													value={options.contact_address ?? '123/45 Gadget District, Bangkok 10110'}
													onChange={(val) => handleOptionChange('contact_address', val)}
												/>
												<TextareaControl
													label={__('Special Instructions / Notice Note', 'raka-preorder-theme')}
													rows={2}
													value={options.contact_note ?? 'For custom inquiries, private showroom sessions, or pre-order status, message us directly via LINE Official 24/7.'}
													onChange={(val) => handleOptionChange('contact_note', val)}
												/>
											</div>
										</div>

										{ /* Live Preview Card */}
										<div style={{ position: 'sticky', top: '20px' }}>
											<div style={{ background: '#09090b', color: '#fff', borderRadius: '16px', padding: '24px', border: '1px solid rgba(255,255,255,0.15)', boxShadow: '0 20px 25px -5px rgba(0, 0, 0, 0.5)' }}>
												<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '16px', paddingBottom: '12px', borderBottom: '1px solid rgba(255,255,255,0.1)' }}>
													<div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
														<span style={{ width: '8px', height: '8px', borderRadius: '50%', background: '#10b981', display: 'inline-block' }} />
														<span style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '0.1em', textTransform: 'uppercase', color: '#94a3b8' }}>
															{__('Live Modal Preview', 'raka-preorder-theme')}
														</span>
													</div>
													<span style={{ fontSize: '11px', color: '#64748b' }}>OBSIDIAN UI</span>
												</div>

												<h3 style={{ margin: '0 0 4px 0', fontSize: '18px', fontWeight: 700, color: '#fff' }}>
													{options.contact_title || 'CONTACT & STORE'}
												</h3>
												<p style={{ margin: '0 0 16px 0', fontSize: '12px', color: 'rgba(255,255,255,0.6)', lineHeight: 1.4 }}>
													{options.contact_subtitle || 'Get in touch with our team for consultations and orders.'}
												</p>

												<div style={{ display: 'flex', flexDirection: 'column', gap: '8px', marginBottom: '16px' }}>
													{options.contact_line_id && (
														<div style={{ padding: '10px 12px', borderRadius: '10px', background: 'rgba(6, 199, 85, 0.12)', border: '1px solid rgba(6, 199, 85, 0.3)', display: 'flex', alignItems: 'center', gap: '10px' }}>
															<i className="ri-line-fill" style={{ fontSize: '20px', color: '#06C755' }} />
															<div style={{ flex: 1, minWidth: 0 }}>
																<div style={{ fontSize: '9px', fontWeight: 700, color: '#06C755', textTransform: 'uppercase' }}>LINE Official</div>
																<div style={{ fontSize: '12px', fontWeight: 600, color: '#fff' }}>{options.contact_line_id}</div>
															</div>
														</div>
													)}
													{options.contact_phone && (
														<div style={{ padding: '10px 12px', borderRadius: '10px', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.1)', display: 'flex', alignItems: 'center', gap: '10px' }}>
															<i className="ri-phone-fill" style={{ fontSize: '18px', color: '#fff' }} />
															<div style={{ flex: 1, minWidth: 0 }}>
																<div style={{ fontSize: '9px', fontWeight: 700, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase' }}>Customer Hotline</div>
																<div style={{ fontSize: '12px', fontWeight: 600, color: '#fff' }}>{options.contact_phone}</div>
															</div>
														</div>
													)}
													{options.contact_email && (
														<div style={{ padding: '10px 12px', borderRadius: '10px', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.1)', display: 'flex', alignItems: 'center', gap: '10px' }}>
															<i className="ri-mail-fill" style={{ fontSize: '18px', color: '#fff' }} />
															<div style={{ flex: 1, minWidth: 0 }}>
																<div style={{ fontSize: '9px', fontWeight: 700, color: 'rgba(255,255,255,0.5)', textTransform: 'uppercase' }}>Support Email</div>
																<div style={{ fontSize: '12px', fontWeight: 600, color: '#fff' }}>{options.contact_email}</div>
															</div>
														</div>
													)}
												</div>

												{(options.contact_hours || options.contact_address) && (
													<div style={{ padding: '12px', borderRadius: '10px', background: 'rgba(255,255,255,0.04)', border: '1px solid rgba(255,255,255,0.08)', fontSize: '11px', color: 'rgba(255,255,255,0.7)', lineHeight: 1.5 }}>
														{options.contact_hours && (
															<div style={{ display: 'flex', alignItems: 'center', gap: '6px', marginBottom: '4px' }}>
																<i className="ri-time-line" style={{ color: '#94a3b8' }} />
																<span>{options.contact_hours}</span>
															</div>
														)}
														{options.contact_address && (
															<div style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
																<i className="ri-map-pin-2-line" style={{ color: '#94a3b8' }} />
																<span>{options.contact_address}</span>
															</div>
														)}
													</div>
												)}
											</div>
										</div>
									</div>
								</CardBody>
							);
						}

						// Pre-Order & Security Tab
						if (tab.name === 'security_settings') {
							return (
								<CardBody style={{ padding: '28px 32px' }}>
									<div style={{ marginBottom: '24px' }}>
										<h2 style={{ margin: '0 0 4px 0', fontSize: '17px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', color: '#0f172a' }}>
											<i className="ri-shield-check-fill" style={{ color: '#f59e0b', fontSize: '20px' }} />
											{__('Cloudflare Turnstile & Pre-Order Security Settings', 'raka-preorder-theme')}
										</h2>
										<p style={{ color: '#64748b', fontSize: '13px', margin: 0 }}>
											{__('Manage CAPTCHA bot protection keys for Pre-order status search and lookups.', 'raka-preorder-theme')}
										</p>
									</div>

									<div style={{ display: 'grid', gridTemplateColumns: '1.2fr 0.8fr', gap: '28px', alignItems: 'start' }}>
										{ /* Form Controls */}
										<div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
											<div style={{ padding: '20px', background: '#f8fafc', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
												<h3 style={{ margin: '0 0 16px 0', fontSize: '14px', fontWeight: 700, color: '#1e293b', display: 'flex', alignItems: 'center', gap: '8px' }}>
													<i className="ri-key-2-line" style={{ color: '#f59e0b' }} />
													{__('Turnstile API Credentials', 'raka-preorder-theme')}
												</h3>

												<div style={{ marginBottom: '16px' }}>
													<ToggleControl
														label={__('Enable Cloudflare Turnstile CAPTCHA', 'raka-preorder-theme')}
														help={
															options.turnstile_enabled !== false
																? __('Turnstile is active on pre-order lookup forms.', 'raka-preorder-theme')
																: __('Turnstile is disabled (open lookup).', 'raka-preorder-theme')
														}
														checked={options.turnstile_enabled !== false}
														onChange={(val) => handleOptionChange('turnstile_enabled', val)}
													/>
												</div>

												<div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
													<TextControl
														label={__('Cloudflare Turnstile Sitekey (Public Key)', 'raka-preorder-theme')}
														value={options.turnstile_sitekey ?? '0x4AAAAAAFNWAI83P9cCw0AU'}
														onChange={(val) => handleOptionChange('turnstile_sitekey', val)}
														placeholder="0x4AAAAAA..."
														help={__('Used in the frontend widget to challenge visitors.', 'raka-preorder-theme')}
													/>

													<TextControl
														label={__('Cloudflare Turnstile Secret Key (Private Key)', 'raka-preorder-theme')}
														value={options.turnstile_secret ?? '0x4AAAAAAFNWAKM9TKecHcitT5juwPWd4gM'}
														onChange={(val) => handleOptionChange('turnstile_secret', val)}
														placeholder="0x4AAAAAA..."
														help={__('Used on the server to verify tokens securely.', 'raka-preorder-theme')}
													/>

													<SelectControl
														label={__('Turnstile Theme Appearance', 'raka-preorder-theme')}
														value={options.turnstile_theme ?? 'auto'}
														options={[
															{ label: __('Auto (Follow System Dark/Light Mode)', 'raka-preorder-theme'), value: 'auto' },
															{ label: __('Dark Theme (Obsidian Dark)', 'raka-preorder-theme'), value: 'dark' },
															{ label: __('Light Theme (Clean White)', 'raka-preorder-theme'), value: 'light' },
														]}
														onChange={(val) => handleOptionChange('turnstile_theme', val)}
													/>
												</div>
											</div>

											{ /* Member Orders Privacy & Access Control */}
											<div style={{ padding: '20px', background: '#f8fafc', borderRadius: '10px', border: '1px solid #e2e8f0' }}>
												<h3 style={{ margin: '0 0 16px 0', fontSize: '14px', fontWeight: 700, color: '#1e293b', display: 'flex', alignItems: 'center', gap: '8px' }}>
													<i className="ri-shield-user-line" style={{ color: '#6366f1' }} />
													{__('Member Orders Privacy & Access Control', 'raka-preorder-theme')}
												</h3>

												<div style={{ marginBottom: '8px' }}>
													<ToggleControl
														label={__('บังคับเข้าสู่ระบบเมื่อเป็นคำสั่งซื้อของสมาชิก (Require Login for Member Orders)', 'raka-preorder-theme')}
														help={
															options.preorder_require_member_login
																? __('เปิดใช้งาน: คำสั่งซื้อที่ผูกกับบัญชีสมาชิก (หรือเบอร์โทร/อีเมลที่ตรงกับสมาชิก) จะต้องเข้าสู่ระบบก่อนจึงจะดูสถานะได้', 'raka-preorder-theme')
																: __('ปิดใช้งาน: ใครที่มีเลขที่คำสั่งซื้อสามารถค้นหาและดูสถานะได้ทันทีโดยไม่ต้องเข้าสู่ระบบ', 'raka-preorder-theme')
														}
														checked={!!options.preorder_require_member_login}
														onChange={(val) => handleOptionChange('preorder_require_member_login', val)}
													/>
												</div>
											</div>
										</div>

										{ /* Guidance & Status Panel */}
										<div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
											<div style={{ background: '#09090b', color: '#fff', borderRadius: '16px', padding: '24px', border: '1px solid rgba(255,255,255,0.15)', boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.3)' }}>
												<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '14px', paddingBottom: '10px', borderBottom: '1px solid rgba(255,255,255,0.1)' }}>
													<div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
														<span style={{ width: '8px', height: '8px', borderRadius: '50%', background: '#10b981', display: 'inline-block' }} />
														<span style={{ fontSize: '11px', fontWeight: 700, letterSpacing: '0.1em', textTransform: 'uppercase', color: '#94a3b8' }}>
															{__('Security Status', 'raka-preorder-theme')}
														</span>
													</div>
													<span style={{ fontSize: '11px', color: '#f59e0b', fontWeight: 600 }}>CLOUDFLARE</span>
												</div>

												<h4 style={{ margin: '0 0 6px 0', fontSize: '15px', fontWeight: 700, color: '#fff' }}>
													{__('Zero-Friction Bot Protection', 'raka-preorder-theme')}
												</h4>
												<p style={{ margin: '0 0 14px 0', fontSize: '12px', color: 'rgba(255,255,255,0.7)', lineHeight: 1.5 }}>
													{__('Cloudflare Turnstile stops scraping bots from querying customer orders without annoying legitimate buyers with image puzzles.', 'raka-preorder-theme')}
												</p>

												<div style={{ padding: '12px', borderRadius: '10px', background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.1)', fontSize: '11px', lineHeight: 1.6, color: '#e2e8f0' }}>
													<strong style={{ color: '#fff', display: 'block', marginBottom: '4px' }}>💡 วิธีตั้งค่า Cloudflare Dashboard:</strong>
													1. ไปที่ Cloudflare Dashboard &gt; Turnstile<br />
													2. เพิ่มโดเมน <code>raka-gadget.local</code> และโดเมนจริง<br />
													3. นำ Sitekey และ Secret Key มาวางในฟอร์มด้านซ้าย แล้วกด Save All Changes
												</div>
											</div>
										</div>
									</div>
								</CardBody>
							);
						}

						// Hero Cards Repeater Tab
						return (
							<CardBody style={{ padding: '24px 28px' }}>
								{ /* Repeater Section Header */}
								<div
									style={{
										display: 'flex',
										alignItems: 'center',
										justifyContent: 'space-between',
										marginBottom: '20px',
										paddingBottom: '14px',
										borderBottom: '1px solid #f1f5f9',
									}}
								>
									<div>
										<h2 style={{ margin: '0 0 2px 0', fontSize: '17px', fontWeight: 700, display: 'flex', alignItems: 'center', gap: '8px', color: '#0f172a' }}>
											<i className="ri-stack-line" style={{ color: '#3b82f6' }} />
											{__('Hero Scroll Product Cards (Repeater)', 'raka-preorder-theme')}
										</h2>
										<p style={{ color: '#64748b', fontSize: '12px', margin: 0 }}>
											{__('Showing 4 cards per scroll slide. Switch easily between WooCommerce Product Select2 / Custom URL and Media / Direct URL.', 'raka-preorder-theme')}
										</p>
									</div>
									<Button
										variant="primary"
										onClick={handleAddCard}
										style={{
											height: '36px',
											padding: '0 16px',
											fontSize: '12px',
											fontWeight: 600,
											display: 'inline-flex',
											alignItems: 'center',
											gap: '6px',
											borderRadius: '6px',
											background: '#10b981',
											borderColor: '#10b981',
										}}
									>
										<i className="ri-add-line" style={{ fontSize: '15px' }} />
										{__('+ Add New Card', 'raka-preorder-theme')}
									</Button>
								</div>

								{ /* If no cards yet */}
								{heroCards.length === 0 && (
									<div
										style={{
											padding: '40px 20px',
											textAlign: 'center',
											border: '2px dashed #cbd5e1',
											borderRadius: '10px',
											background: '#f8fafc',
										}}
									>
										<i className="ri-shopping-bag-line" style={{ fontSize: '36px', color: '#94a3b8' }} />
										<h3 style={{ margin: '10px 0 4px 0', fontSize: '15px', fontWeight: 600, color: '#334155' }}>
											{__('No Hero Product Cards Added Yet', 'raka-preorder-theme')}
										</h3>
										<p style={{ color: '#64748b', fontSize: '12px', maxWidth: '380px', margin: '0 auto 14px auto' }}>
											{__('Click the button below to add your first card to appear during the front page scroll.', 'raka-preorder-theme')}
										</p>
										<Button variant="primary" onClick={handleAddCard}>
											<i className="ri-add-line" style={{ marginRight: '6px' }} />
											{__('Add First Card', 'raka-preorder-theme')}
										</Button>
									</div>
								)}

								{ /* Compact Fullwidth Accordion Cards List */}
								<div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
									{heroCards.map((card, idx) => {
										const isExpanded = activeCardIndex === idx;
										const selectedProd = wcData.products.find((p) => p.id === Number(card.product_id));
										const displayTitle = card.title || (selectedProd ? selectedProd.title : `Card #${idx + 1} (No Product Selected)`);
										const displayTag = card.tag || (card.show_custom_text ? 'ORIGIN' : 'PRE-ORDER');
										const displayPrice = card.price || (selectedProd ? selectedProd.price : '');
										const displayImage = card.image_url || (selectedProd ? selectedProd.image_url : '');

										// Tab state for Product vs Custom URL
										const linkTab = cardTabModes[`${idx}_link`] || (Number(card.product_id) > 0 ? 'product' : 'product');
										const setLinkTab = (val) => {
											setCardTabModes((prev) => ({ ...prev, [`${idx}_link`]: val }));
										};

										// Tab state for Media vs URL
										const imageTab = cardTabModes[`${idx}_image`] || 'media';
										const setImageTab = (val) => {
											setCardTabModes((prev) => ({ ...prev, [`${idx}_image`]: val }));
										};

										return (
											<div
												key={idx}
												style={{
													border: isExpanded ? '1px solid #3b82f6' : '1px solid #e2e8f0',
													borderRadius: '8px',
													background: '#ffffff',
													boxShadow: isExpanded ? '0 4px 16px rgba(59, 130, 246, 0.07)' : '0 1px 2px rgba(0,0,0,0.02)',
													overflow: 'hidden',
													transition: 'all 0.15s ease',
												}}
											>
												{ /* Compact Card Header Bar */}
												<div
													onClick={() => setActiveCardIndex(isExpanded ? -1 : idx)}
													style={{
														padding: '10px 16px',
														background: isExpanded ? '#f0f7ff' : '#f8fafc',
														display: 'flex',
														alignItems: 'center',
														justifyContent: 'space-between',
														cursor: 'pointer',
														userSelect: 'none',
														borderBottom: isExpanded ? '1px solid #dbeafe' : 'none',
													}}
												>
													<div style={{ display: 'flex', alignItems: 'center', gap: '10px', minWidth: 0, flex: 1 }}>
														<span
															style={{
																background: isExpanded ? '#2563eb' : '#475569',
																color: '#fff',
																borderRadius: '5px',
																padding: '2px 7px',
																fontSize: '11px',
																fontWeight: 700,
																flexShrink: 0,
															}}
														>
															#{idx + 1}
														</span>

														{displayImage && !card.show_custom_text && (
															<img
																src={displayImage}
																alt=""
																style={{
																	width: '26px',
																	height: '26px',
																	borderRadius: '4px',
																	objectFit: 'cover',
																	border: '1px solid #cbd5e1',
																	flexShrink: 0,
																}}
															/>
														)}

														<strong style={{ fontSize: '13px', color: '#0f172a', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis', maxWidth: '340px' }}>
															{displayTitle}
														</strong>

														<span
															style={{
																background: card.show_custom_text ? '#fef3c7' : '#ecfdf5',
																color: card.show_custom_text ? '#92400e' : '#047857',
																border: card.show_custom_text ? '1px solid #fde68a' : '1px solid #a7f3d0',
																padding: '1px 7px',
																borderRadius: '4px',
																fontSize: '10px',
																fontWeight: 600,
																flexShrink: 0,
															}}
														>
															{card.show_custom_text ? 'EDITORIAL TEXT' : displayTag}
														</span>

														{displayPrice && !card.show_custom_text && (
															<span style={{ fontSize: '11px', fontWeight: 700, color: '#059669', flexShrink: 0 }}>
																{displayPrice}
															</span>
														)}

														<span style={{ fontSize: '10px', color: '#94a3b8', flexShrink: 0 }}>
															[{card.position === 'right' ? 'Right Side' : 'Left Side'}]
														</span>
													</div>

													{ /* Quick Toolbar Actions */}
													<div
														style={{ display: 'flex', alignItems: 'center', gap: '3px', flexShrink: 0 }}
														onClick={(e) => e.stopPropagation()}
													>
														<button
															type="button"
															disabled={idx === 0}
															onClick={() => handleMoveCard(idx, -1)}
															title={__('Move Up', 'raka-preorder-theme')}
															style={{
																background: 'transparent',
																border: 'none',
																padding: '3px 5px',
																cursor: idx === 0 ? 'not-allowed' : 'pointer',
																color: idx === 0 ? '#cbd5e1' : '#64748b',
																fontSize: '15px',
																borderRadius: '4px',
															}}
														>
															<i className="ri-arrow-up-s-line" />
														</button>
														<button
															type="button"
															disabled={idx === heroCards.length - 1}
															onClick={() => handleMoveCard(idx, 1)}
															title={__('Move Down', 'raka-preorder-theme')}
															style={{
																background: 'transparent',
																border: 'none',
																padding: '3px 5px',
																cursor: idx === heroCards.length - 1 ? 'not-allowed' : 'pointer',
																color: idx === heroCards.length - 1 ? '#cbd5e1' : '#64748b',
																fontSize: '15px',
																borderRadius: '4px',
															}}
														>
															<i className="ri-arrow-down-s-line" />
														</button>
														<button
															type="button"
															onClick={() => handleDuplicateCard(idx)}
															title={__('Duplicate Card', 'raka-preorder-theme')}
															style={{
																background: 'transparent',
																border: 'none',
																padding: '3px 5px',
																cursor: 'pointer',
																color: '#64748b',
																fontSize: '15px',
																borderRadius: '4px',
															}}
														>
															<i className="ri-file-copy-line" />
														</button>
														<button
															type="button"
															onClick={() => handleRemoveCard(idx)}
															title={__('Remove Card', 'raka-preorder-theme')}
															style={{
																background: 'transparent',
																border: 'none',
																padding: '3px 5px',
																cursor: 'pointer',
																color: '#ef4444',
																fontSize: '15px',
																borderRadius: '4px',
															}}
														>
															<i className="ri-delete-bin-6-line" />
														</button>
														<button
															type="button"
															onClick={() => setActiveCardIndex(isExpanded ? -1 : idx)}
															style={{
																background: 'transparent',
																border: 'none',
																padding: '3px 5px',
																cursor: 'pointer',
																color: '#2563eb',
																fontSize: '15px',
																fontWeight: 700,
															}}
														>
															<i className={isExpanded ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'} />
														</button>
													</div>
												</div>

												{ /* Fullwidth 2-Column Responsive Dashboard Body */}
												{isExpanded && (
													<div style={{ padding: '18px 20px', background: '#fafafa' }}>
														<div
															style={{
																display: 'grid',
																gridTemplateColumns: 'minmax(0, 1fr) 400px',
																gap: '20px',
																alignItems: 'start',
															}}
														>
															{ /* ══ LEFT COLUMN: Input Fields (Compact Grid) ══ */}
															<div style={{ display: 'flex', flexDirection: 'column', gap: '14px' }}>
																{ /* Mode Toggle Header */}
																<div
																	style={{
																		background: card.show_custom_text ? '#fffbeb' : '#f0fdf4',
																		border: card.show_custom_text ? '1px solid #fde68a' : '1px solid #bbf7d0',
																		borderRadius: '6px',
																		padding: '8px 14px',
																		display: 'flex',
																		alignItems: 'center',
																		justifyContent: 'space-between',
																	}}
																>
																	<div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
																		<i
																			className={card.show_custom_text ? 'ri-article-line' : 'ri-shopping-bag-3-line'}
																			style={{
																				fontSize: '18px',
																				color: card.show_custom_text ? '#d97706' : '#16a34a',
																			}}
																		/>
																		<div>
																			<strong style={{ fontSize: '12px', color: '#0f172a' }}>
																				{card.show_custom_text
																					? __('Editorial Narrative Statement Mode', 'raka-preorder-theme')
																					: __('WooCommerce Product Card Mode (Direct Add to Cart)', 'raka-preorder-theme')}
																			</strong>
																		</div>
																	</div>
																	<ToggleControl
																		checked={!!card.show_custom_text}
																		onChange={(val) => handleCardChange(idx, 'show_custom_text', val)}
																		label={card.show_custom_text ? __('Editorial Text ON', 'raka-preorder-theme') : __('Product Mode', 'raka-preorder-theme')}
																	/>
																</div>

																{ /* Row: Product Source + Thumbnail Source in 2 side-by-side columns */}
																{!card.show_custom_text && (
																	<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
																		{ /* Box 1: Product / Destination Link */}
																		<div style={{ background: '#ffffff', padding: '12px 14px', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
																			<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '8px' }}>
																				<label style={{ fontWeight: 700, fontSize: '11px', color: '#0f172a', display: 'flex', alignItems: 'center', gap: '4px' }}>
																					<i className="ri-links-line" style={{ color: '#2563eb' }} />
																					{__('Product / Link Source', 'raka-preorder-theme')}
																				</label>
																				<TabSwitcher
																					tabs={[
																						{ id: 'product', label: 'Select2', icon: 'ri-shopping-bag-3-line' },
																						{ id: 'custom_url', label: 'Custom URL', icon: 'ri-link' },
																					]}
																					activeTab={linkTab}
																					onSelect={setLinkTab}
																					style={{ marginBottom: 0 }}
																				/>
																			</div>

																			{linkTab === 'product' ? (
																				<div>
																					<Select2Control
																						label=""
																						value={card.product_id}
																						options={productOptions}
																						onChange={(val) => handleCardChange(idx, 'product_id', val)}
																						placeholder={__('-- Search Product --', 'raka-preorder-theme')}
																					/>
																					{selectedProd && (
																						<div style={{ padding: '4px 8px', background: '#ecfdf5', borderRadius: '4px', border: '1px solid #a7f3d0', fontSize: '11px', color: '#065f46', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
																							<i className="ri-check-line" style={{ color: '#10b981', marginRight: '4px' }} />
																							<strong>{selectedProd.title}</strong> ({selectedProd.price || 'No price'})
																						</div>
																					)}
																				</div>
																			) : (
																				<TextControl
																					label=""
																					value={card.url ?? ''}
																					onChange={(val) => handleCardChange(idx, 'url', val)}
																					placeholder="https://raka-gadget.local/product/..."
																				/>
																			)}
																		</div>

																		{ /* Box 2: Image Source Switcher */}
																		<div style={{ background: '#ffffff', padding: '12px 14px', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
																			<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '8px' }}>
																				<label style={{ fontWeight: 700, fontSize: '11px', color: '#0f172a', display: 'flex', alignItems: 'center', gap: '4px' }}>
																					<i className="ri-image-line" style={{ color: '#2563eb' }} />
																					{__('Image Source', 'raka-preorder-theme')}
																				</label>
																				<TabSwitcher
																					tabs={[
																						{ id: 'media', label: 'Media Library', icon: 'ri-folder-image-line' },
																						{ id: 'url', label: 'Direct URL', icon: 'ri-global-line' },
																					]}
																					activeTab={imageTab}
																					onSelect={setImageTab}
																					style={{ marginBottom: 0 }}
																				/>
																			</div>

																			{imageTab === 'media' ? (
																				<MediaUploaderControl
																					value={card.image_url ?? (selectedProd ? selectedProd.image_url : '')}
																					mediaType="image"
																					onChange={(val) => handleCardChange(idx, 'image_url', val)}
																				/>
																			) : (
																				<div>
																					<TextControl
																						value={card.image_url ?? ''}
																						onChange={(val) => handleCardChange(idx, 'image_url', val)}
																						placeholder={selectedProd ? selectedProd.image_url : 'https://.../product.jpg'}
																					/>
																					{card.image_url && (
																						<div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginTop: '4px' }}>
																							<img src={card.image_url} alt="" style={{ width: '28px', height: '28px', borderRadius: '4px', objectFit: 'cover', border: '1px solid #cbd5e1' }} />
																							<span style={{ fontSize: '11px', color: '#64748b' }}>{__('Custom image active', 'raka-preorder-theme')}</span>
																						</div>
																					)}
																				</div>
																			)}
																		</div>
																	</div>
																)}

																{ /* Content Form Grid: Tag, Position, Title, Price, Description, CTA */}
																<div style={{ background: '#ffffff', padding: '16px 18px', borderRadius: '8px', border: '1px solid #e2e8f0', display: 'flex', flexDirection: 'column', gap: '12px' }}>
																	<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
																		<TextControl
																			label={__('Top Badge / Tag', 'raka-preorder-theme')}
																			value={card.tag ?? ''}
																			onChange={(val) => handleCardChange(idx, 'tag', val)}
																			placeholder={card.show_custom_text ? 'ORIGIN' : 'PRE-ORDER'}
																		/>
																		<SelectControl
																			label={__('Screen Position / Slot', 'raka-preorder-theme')}
																			value={card.position ?? 'left'}
																			options={POSITION_OPTIONS}
																			onChange={(val) => handleCardChange(idx, 'position', val)}
																		/>
																	</div>

																	<div style={{ display: 'grid', gridTemplateColumns: card.show_custom_text ? '1fr' : '1fr 1fr', gap: '14px' }}>
																		<TextControl
																			label={card.show_custom_text
																				? __('Statement Headline / Title', 'raka-preorder-theme')
																				: __('Card Title (Blank = Use Product Name)', 'raka-preorder-theme')}
																			value={card.title ?? ''}
																			onChange={(val) => handleCardChange(idx, 'title', val)}
																			placeholder={selectedProd ? selectedProd.title : __('e.g. Vortex 8K Gaming Keyboard', 'raka-preorder-theme')}
																		/>
																		{!card.show_custom_text && (
																			<TextControl
																				label={__('Custom Price Tag (Blank = Use Product Price)', 'raka-preorder-theme')}
																				value={card.price ?? ''}
																				onChange={(val) => handleCardChange(idx, 'price', val)}
																				placeholder={selectedProd ? selectedProd.price : '฿4,990'}
																			/>
																		)}
																	</div>

																	<TextareaControl
																		label={card.show_custom_text
																			? __('Editorial Body Statement Text', 'raka-preorder-theme')
																			: __('Key Highlights / Features Description', 'raka-preorder-theme')}
																		rows={2}
																		value={card.description ?? ''}
																		onChange={(val) => handleCardChange(idx, 'description', val)}
																		placeholder={selectedProd ? selectedProd.description : __('Short summary of specs or highlights...', 'raka-preorder-theme')}
																	/>

																	<div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '14px' }}>
																		<TextControl
																			label={__('CTA Button Text', 'raka-preorder-theme')}
																			value={card.button_text ?? (card.show_custom_text ? 'Explore' : 'Pre-Order Now')}
																			onChange={(val) => handleCardChange(idx, 'button_text', val)}
																			placeholder={card.show_custom_text ? 'Explore' : 'Pre-Order Now'}
																		/>
																		{card.show_custom_text && (
																			<TextControl
																				label={__('Destination URL', 'raka-preorder-theme')}
																				value={card.url ?? ''}
																				onChange={(val) => handleCardChange(idx, 'url', val)}
																				placeholder="https://..."
																			/>
																		)}
																	</div>
																</div>
															</div>

															{ /* ══ RIGHT COLUMN: Sticky Live Front-End Card Preview (400px) ══ */}
															<div
																style={{
																	position: 'sticky',
																	top: '20px',
																	background: '#09090b',
																	borderRadius: '12px',
																	padding: '16px',
																	border: '1px solid #27272a',
																	boxShadow: '0 8px 24px rgba(0,0,0,0.35)',
																	color: '#fff',
																}}
															>
																<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '12px', paddingBottom: '8px', borderBottom: '1px solid #18181b' }}>
																	<div style={{ fontSize: '11px', fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: '#a1a1aa', display: 'flex', alignItems: 'center', gap: '6px' }}>
																		<i className="ri-eye-line" style={{ color: '#3b82f6' }} />
																		<span>{__('Live Card Preview', 'raka-preorder-theme')}</span>
																	</div>
																	<span style={{ fontSize: '10px', background: '#27272a', color: '#e4e4e7', padding: '2px 6px', borderRadius: '4px', fontWeight: 600 }}>
																		{card.position === 'right' ? 'Right Side' : 'Left Side'}
																	</span>
																</div>

																{card.show_custom_text ? (
																	/* Custom Statement Preview */
																	<div
																		style={{
																			background: 'rgba(255, 255, 255, 0.08)',
																			backdropFilter: 'blur(16px)',
																			border: '1px solid rgba(255, 255, 255, 0.15)',
																			borderRadius: '16px',
																			padding: '20px',
																		}}
																	>
																		<div style={{ fontSize: '10px', letterSpacing: '0.2em', textTransform: 'uppercase', color: 'rgba(255,255,255,0.6)', marginBottom: '6px', fontWeight: 600 }}>
																			{displayTag}
																		</div>
																		<h3 style={{ margin: '0 0 6px 0', fontSize: '17px', fontWeight: 800, color: '#fff', lineHeight: 1.2 }}>
																			{displayTitle}
																		</h3>
																		<p style={{ margin: '0 0 12px 0', fontSize: '12px', color: 'rgba(255,255,255,0.8)', lineHeight: 1.5 }}>
																			{card.description || 'Editorial narrative statement describing your craft, story, or special hardware technology...'}
																		</p>
																		<span style={{ fontSize: '11px', textTransform: 'uppercase', letterSpacing: '0.12em', color: 'rgba(255,255,255,0.85)', fontWeight: 600 }}>
																			{card.button_text || 'Explore'} &rarr;
																		</span>
																	</div>
																) : (
																	/* Product Card Preview */
																	<div
																		style={{
																			background: 'rgba(255, 255, 255, 0.08)',
																			backdropFilter: 'blur(16px)',
																			border: '1px solid rgba(255, 255, 255, 0.15)',
																			borderRadius: '16px',
																			padding: '16px',
																		}}
																	>
																		<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '8px', marginBottom: '8px' }}>
																			<span
																				style={{
																					background: 'rgba(16, 185, 129, 0.2)',
																					color: '#34d399',
																					border: '1px solid rgba(16, 185, 129, 0.3)',
																					padding: '2px 8px',
																					borderRadius: '20px',
																					fontSize: '10px',
																					fontWeight: 700,
																					letterSpacing: '0.05em',
																					textTransform: 'uppercase',
																				}}
																			>
																				{displayTag}
																			</span>
																			{displayPrice && (
																				<span style={{ fontSize: '13px', fontWeight: 700, color: '#f8fafc' }}>
																					{displayPrice}
																				</span>
																			)}
																		</div>

																		<div style={{ display: 'flex', gap: '12px', alignItems: 'flex-start', marginBottom: '10px' }}>
																			{displayImage && (
																				<img
																					src={displayImage}
																					alt=""
																					style={{
																						width: '56px',
																						height: '56px',
																						borderRadius: '10px',
																						objectFit: 'cover',
																						border: '1px solid rgba(255,255,255,0.2)',
																						flexShrink: 0,
																					}}
																				/>
																			)}
																			<div style={{ flex: 1, minWidth: 0 }}>
																				<h4 style={{ margin: '0 0 3px 0', fontSize: '14px', fontWeight: 700, color: '#fff', lineHeight: 1.3 }}>
																					{displayTitle}
																				</h4>
																				<p style={{ margin: 0, fontSize: '11px', color: '#cbd5e1', lineHeight: 1.4, overflow: 'hidden', textOverflow: 'ellipsis', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' }}>
																					{card.description || (selectedProd ? selectedProd.description : 'Product features and specs description...')}
																				</p>
																			</div>
																		</div>

																		<div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderTop: '1px solid rgba(255,255,255,0.1)', paddingTop: '10px' }}>
																			<button
																				type="button"
																				style={{
																					background: '#fff',
																					color: '#09090b',
																					border: 'none',
																					borderRadius: '8px',
																					padding: '6px 12px',
																					fontSize: '11px',
																					fontWeight: 700,
																					cursor: 'pointer',
																					display: 'inline-flex',
																					alignItems: 'center',
																					gap: '4px',
																				}}
																			>
																				<i className="ri-shopping-bag-3-line" />
																				<span>{card.button_text || 'Pre-Order Now'}</span>
																			</button>
																			<span style={{ fontSize: '10px', color: 'rgba(255,255,255,0.6)', fontWeight: 600 }}>
																				View Details &rarr;
																			</span>
																		</div>
																	</div>
																)}
															</div>
														</div>
													</div>
												)}
											</div>
										);
									})}
								</div>

								{ /* Bottom Add Card Bar */}
								{heroCards.length > 0 && (
									<div style={{ marginTop: '16px', textAlign: 'center' }}>
										<Button
											variant="secondary"
											onClick={handleAddCard}
											style={{
												width: '100%',
												padding: '12px',
												border: '1.5px dashed #cbd5e1',
												borderRadius: '8px',
												fontSize: '13px',
												fontWeight: 600,
												color: '#475569',
												background: '#f8fafc',
											}}
										>
											<i className="ri-add-circle-line" style={{ fontSize: '16px', marginRight: '6px' }} />
											{__('+ Add Another Card', 'raka-preorder-theme')}
										</Button>
									</div>
								)}
							</CardBody>
						);
					}}
				</TabPanel>
			</Card>

			{ /* Floating / Bottom Save Bar */}
			<div style={{ marginTop: '20px', display: 'flex', justifyContent: 'flex-end', gap: '10px' }}>
				<Button
					variant="primary"
					onClick={handleSave}
					isBusy={saving}
					disabled={saving}
					style={{
						height: '42px',
						padding: '0 32px',
						fontSize: '13px',
						fontWeight: 600,
						display: 'inline-flex',
						alignItems: 'center',
						gap: '6px',
						borderRadius: '8px',
						background: '#3b82f6',
						borderColor: '#3b82f6',
						boxShadow: '0 2px 10px rgba(59, 130, 246, 0.3)',
					}}
				>
					<i className="ri-save-3-line" style={{ fontSize: '16px' }} />
					{saving ? __('Saving...', 'raka-preorder-theme') : __('Save All Settings', 'raka-preorder-theme')}
				</Button>
			</div>
		</div>
	);
}
