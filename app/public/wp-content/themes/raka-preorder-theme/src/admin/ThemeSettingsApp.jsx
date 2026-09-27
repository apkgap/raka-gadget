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
	Notice,
	Spinner,
	Flex,
	FlexItem,
} from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

/**
 * Reusable Select2 component integrated into WordPress Component style.
 */
function Select2Control( {
	label,
	value,
	options = [],
	onChange,
	placeholder,
	icon,
	help,
} ) {
	const selectRef = useRef( null );

	useEffect( () => {
		if ( ! selectRef.current || ! window.jQuery || ! window.jQuery.fn.select2 ) {
			return;
		}

		const $select = window.jQuery( selectRef.current );

		$select.select2( {
			placeholder: placeholder || __( '-- Select --', 'raka-preorder-theme' ),
			allowClear: true,
			width: '100%',
		} );

		const handleSelectChange = ( e ) => {
			const newVal = e.target.value;
			if ( onChange ) {
				onChange( newVal );
			}
		};

		$select.on( 'change', handleSelectChange );

		return () => {
			$select.off( 'change', handleSelectChange );
			if ( $select.data( 'select2' ) ) {
				$select.select2( 'destroy' );
			}
		};
	}, [ options, placeholder ] );

	// Sync external state changes into Select2
	useEffect( () => {
		if ( selectRef.current && window.jQuery && window.jQuery.fn.select2 ) {
			const $select = window.jQuery( selectRef.current );
			const currentVal = $select.val();
			const targetVal = String( value ?? '' );
			if ( currentVal !== targetVal ) {
				$select.val( targetVal ).trigger( 'change.select2' );
			}
		}
	}, [ value ] );

	return (
		<div className="raka-select2-field" style={ { marginBottom: '16px' } }>
			{ label && (
				<label
					style={ {
						display: 'flex',
						alignItems: 'center',
						gap: '7px',
						fontWeight: 600,
						fontSize: '13px',
						marginBottom: '6px',
						color: '#1e1e1e',
					} }
				>
					{ icon && <i className={ icon } style={ { fontSize: '16px', color: '#2271b1' } } /> }
					<span>{ label }</span>
				</label>
			) }
			<select
				ref={ selectRef }
				defaultValue={ value ?? '' }
				style={ { width: '100%' } }
			>
				<option value="">{ placeholder || __( '-- Select an option --', 'raka-preorder-theme' ) }</option>
				{ options.map( ( opt ) => (
					<option key={ opt.value } value={ opt.value }>
						{ opt.label }
					</option>
				) ) }
			</select>
			{ help && (
				<p style={ { margin: '5px 0 0 0', fontSize: '12px', color: '#646970' } }>
					{ help }
				</p>
			) }
		</div>
	);
}

const POSITION_OPTIONS = [
	{ label: __( 'Left Pinned Card', 'raka-preorder-theme' ), value: 'left' },
	{ label: __( 'Right Pinned Card', 'raka-preorder-theme' ), value: 'right' },
	{ label: __( 'Center Card', 'raka-preorder-theme' ), value: 'center' },
	{ label: __( 'Bottom Left Card', 'raka-preorder-theme' ), value: 'bottom_left' },
	{ label: __( 'Bottom Right Card', 'raka-preorder-theme' ), value: 'bottom_right' },
];

export default function ThemeSettingsApp() {
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const [ options, setOptions ] = useState( {
		hero_main_title: 'OBSIDIAN',
		hero_caption: "RITUALS REFINED:\nTRACING LINE, SHADOW & SKIN\nACROSS A LIVING FORM.",
		hero_video_url: '',
		hero_overlay_url: '',
		hero_cards: [],
	} );
	const [ wcData, setWcData ] = useState( {
		categories: [],
		brands: [],
		products: [],
	} );
	const [ activeCardIndex, setActiveCardIndex ] = useState( 0 );

	// Fetch initial settings & WooCommerce terms from REST API
	useEffect( () => {
		apiFetch( { path: '/raka/v1/settings' } )
			.then( ( res ) => {
				if ( res && res.options ) {
					setOptions( ( prev ) => ( {
						...prev,
						...res.options,
						hero_cards: Array.isArray( res.options.hero_cards ) ? res.options.hero_cards : [],
					} ) );
					setWcData( {
						categories: res.categories || [],
						brands: res.brands || [],
						products: res.products || [],
					} );
				}
				setLoading( false );
			} )
			.catch( ( err ) => {
				setNotice( {
					type: 'error',
					message: err.message || __( 'Failed to load theme settings.', 'raka-preorder-theme' ),
				} );
				setLoading( false );
			} );
	}, [] );

	const handleOptionChange = ( field, val ) => {
		setOptions( ( prev ) => ( {
			...prev,
			[ field ]: val,
		} ) );
	};

	// Repeater handlers
	const heroCards = Array.isArray( options.hero_cards ) ? options.hero_cards : [];

	const handleCardChange = ( index, field, value ) => {
		const updated = [ ...heroCards ];
		updated[ index ] = {
			...updated[ index ],
			[ field ]: value,
		};

		// If product_id changed, auto-populate details if they are currently blank
		if ( field === 'product_id' ) {
			const prodId = Number( value );
			const matched = wcData.products.find( ( p ) => p.id === prodId );
			if ( matched ) {
				if ( ! updated[ index ].title ) {
					updated[ index ].title = matched.title;
				}
				if ( ! updated[ index ].price && matched.price ) {
					updated[ index ].price = matched.price;
				}
				if ( ! updated[ index ].url && matched.permalink ) {
					updated[ index ].url = matched.permalink;
				}
				if ( ! updated[ index ].image_url && matched.image_url ) {
					updated[ index ].image_url = matched.image_url;
				}
				if ( ! updated[ index ].description && matched.description ) {
					updated[ index ].description = matched.description;
				}
			}
		}

		handleOptionChange( 'hero_cards', updated );
	};

	const handleAddCard = () => {
		const newCard = {
			product_id: 0,
			tag: 'PRE-ORDER',
			title: '',
			description: '',
			price: '',
			url: '',
			image_url: '',
			button_text: 'Pre-Order Now',
			position: heroCards.length % 2 === 0 ? 'left' : 'right',
		};
		const updated = [ ...heroCards, newCard ];
		handleOptionChange( 'hero_cards', updated );
		setActiveCardIndex( updated.length - 1 );
	};

	const handleRemoveCard = ( index ) => {
		if ( window.confirm( __( 'Are you sure you want to remove this hero card?', 'raka-preorder-theme' ) ) ) {
			const updated = heroCards.filter( ( _, i ) => i !== index );
			handleOptionChange( 'hero_cards', updated );
			if ( activeCardIndex >= updated.length ) {
				setActiveCardIndex( Math.max( 0, updated.length - 1 ) );
			}
		}
	};

	const handleMoveCard = ( index, direction ) => {
		const targetIndex = index + direction;
		if ( targetIndex < 0 || targetIndex >= heroCards.length ) {
			return;
		}
		const updated = [ ...heroCards ];
		const temp = updated[ index ];
		updated[ index ] = updated[ targetIndex ];
		updated[ targetIndex ] = temp;
		handleOptionChange( 'hero_cards', updated );
		setActiveCardIndex( targetIndex );
	};

	const handleDuplicateCard = ( index ) => {
		const target = heroCards[ index ];
		if ( ! target ) return;
		const duplicated = {
			...target,
			title: target.title ? `${ target.title } (Copy)` : '',
		};
		const updated = [ ...heroCards ];
		updated.splice( index + 1, 0, duplicated );
		handleOptionChange( 'hero_cards', updated );
		setActiveCardIndex( index + 1 );
	};

	const handleSave = () => {
		setSaving( true );
		setNotice( null );

		apiFetch( {
			path: '/raka/v1/settings',
			method: 'POST',
			data: { options },
		} )
			.then( ( res ) => {
				setSaving( false );
				if ( res && res.success ) {
					setNotice( {
						type: 'success',
						message: res.message || __( 'Hero Banner settings saved successfully!', 'raka-preorder-theme' ),
					} );
				}
			} )
			.catch( ( err ) => {
				setSaving( false );
				setNotice( {
					type: 'error',
					message: err.message || __( 'Failed to save settings.', 'raka-preorder-theme' ),
				} );
			} );
	};

	if ( loading ) {
		return (
			<div style={ { padding: '60px 20px', textAlign: 'center' } }>
				<Spinner />
				<p style={ { marginTop: '12px', color: '#666', fontSize: '14px' } }>
					{ __( 'Loading Hero Banner settings...', 'raka-preorder-theme' ) }
				</p>
			</div>
		);
	}

	// Prepare Product Select options
	const productOptions = wcData.products.map( ( p ) => ( {
		value: String( p.id ),
		label: `${ p.title } ${ p.price ? '- ' + p.price : '' }`,
	} ) );

	const tabs = [
		{
			name: 'hero_repeater',
			title: (
				<span style={ { display: 'inline-flex', alignItems: 'center', gap: '6px' } }>
					<i className="ri-stack-line" style={ { fontSize: '16px' } } />
					{ __( 'Product Cards (Repeater)', 'raka-preorder-theme' ) }
					<span
						style={ {
							background: '#2271b1',
							color: '#fff',
							borderRadius: '10px',
							padding: '1px 8px',
							fontSize: '11px',
							fontWeight: 700,
							marginLeft: '4px',
						} }
					>
						{ heroCards.length }
					</span>
				</span>
			),
		},
		{
			name: 'hero_general',
			title: (
				<span style={ { display: 'inline-flex', alignItems: 'center', gap: '6px' } }>
					<i className="ri-movie-line" style={ { fontSize: '16px' } } />
					{ __( 'Hero Media & Title', 'raka-preorder-theme' ) }
				</span>
			),
		},
	];

	return (
		<div style={ { maxWidth: '1140px', margin: '20px 0', fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif' } }>
			{ /* Top Header Banner */ }
			<div
				style={ {
					background: '#111827',
					color: '#fff',
					borderRadius: '12px',
					padding: '24px 32px',
					marginBottom: '24px',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'space-between',
					boxShadow: '0 10px 25px -5px rgba(0, 0, 0, 0.15)',
					border: '1px solid #1f2937',
				} }
			>
				<div>
					<div style={ { display: 'flex', alignItems: 'center', gap: '10px' } }>
						<div
							style={ {
								width: '36px',
								height: '36px',
								borderRadius: '8px',
								background: 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'center',
							} }
						>
							<i className="ri-shopping-bag-3-fill" style={ { fontSize: '20px', color: '#fff' } } />
						</div>
						<h1 style={ { margin: 0, fontSize: '22px', fontWeight: 700, color: '#fff', letterSpacing: '-0.02em' } }>
							{ __( 'Hero Banner & Product Cards Settings', 'raka-preorder-theme' ) }
						</h1>
					</div>
					<p style={ { margin: '6px 0 0 46px', color: '#9ca3af', fontSize: '13px' } }>
						{ __( 'Configure dynamic scroll-activated product cards (unlimited repeater), background 60fps video scrubbing, and hero typography.', 'raka-preorder-theme' ) }
					</p>
				</div>
				<div style={ { display: 'flex', gap: '10px' } }>
					<Button
						variant="primary"
						onClick={ handleSave }
						isBusy={ saving }
						disabled={ saving }
						style={ {
							height: '42px',
							padding: '0 24px',
							fontSize: '14px',
							fontWeight: 600,
							display: 'inline-flex',
							alignItems: 'center',
							gap: '8px',
							borderRadius: '8px',
							background: '#3b82f6',
							borderColor: '#3b82f6',
						} }
					>
						<i className="ri-save-3-line" style={ { fontSize: '16px' } } />
						{ saving ? __( 'Saving...', 'raka-preorder-theme' ) : __( 'Save Settings', 'raka-preorder-theme' ) }
					</Button>
				</div>
			</div>

			{ notice && (
				<Notice
					status={ notice.type }
					onRemove={ () => setNotice( null ) }
					style={ { marginBottom: '24px' } }
				>
					{ notice.message }
				</Notice>
			) }

			<Card style={ { borderRadius: '12px', border: '1px solid #e5e7eb', boxShadow: '0 1px 3px rgba(0,0,0,0.05)', overflow: 'hidden' } }>
				<TabPanel
					className="raka-theme-settings-tab-panel"
					activeClass="active-tab"
					tabs={ tabs }
				>
					{ ( tab ) => {
						if ( tab.name === 'hero_general' ) {
							return (
								<CardBody style={ { padding: '28px 32px' } }>
									<div style={ { marginBottom: '24px' } }>
										<h2 style={ { margin: '0 0 6px 0', fontSize: '17px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', color: '#111827' } }>
											<i className="ri-movie-2-line" style={ { color: '#3b82f6' } } />
											{ __( 'Main Title & Video Media', 'raka-preorder-theme' ) }
										</h2>
										<p style={ { color: '#6b7280', fontSize: '13px', margin: 0 } }>
											{ __( 'These controls manage the background video scrubbing, character overlay PNG, and primary typography.', 'raka-preorder-theme' ) }
										</p>
									</div>

									<TextControl
										label={ __( 'Hero Main Title Text', 'raka-preorder-theme' ) }
										value={ options.hero_main_title ?? 'OBSIDIAN' }
										onChange={ ( val ) => handleOptionChange( 'hero_main_title', val ) }
										help={ __( 'The massive centered typography displayed over the hero banner.', 'raka-preorder-theme' ) }
									/>

									<div style={ { marginTop: '18px' } }>
										<TextareaControl
											label={ __( 'Hero Caption (Bottom Left)', 'raka-preorder-theme' ) }
											rows={ 3 }
											value={ options.hero_caption ?? "RITUALS REFINED:\nTRACING LINE, SHADOW & SKIN\nACROSS A LIVING FORM." }
											onChange={ ( val ) => handleOptionChange( 'hero_caption', val ) }
											help={ __( 'Short poetic caption displayed at the bottom-left of the hero banner.', 'raka-preorder-theme' ) }
										/>
									</div>

									<div style={ { marginTop: '18px' } }>
										<TextControl
											label={ __( 'Background Video MP4 URL (Ultra-Smooth WebCodecs Scrubbing)', 'raka-preorder-theme' ) }
											value={ options.hero_video_url ?? '' }
											onChange={ ( val ) => handleOptionChange( 'hero_video_url', val ) }
											help={ __( 'Direct URL to a 60fps MP4 video used for the scroll scrubber.', 'raka-preorder-theme' ) }
										/>
									</div>

									<div style={ { marginTop: '18px' } }>
										<TextControl
											label={ __( 'Hero Character Overlay Image URL (Figma PNG Asset)', 'raka-preorder-theme' ) }
											value={ options.hero_overlay_url ?? '' }
											onChange={ ( val ) => handleOptionChange( 'hero_overlay_url', val ) }
											help={ __( 'Local transparent PNG of the character overlay that fades during scrolling.', 'raka-preorder-theme' ) }
										/>
									</div>
								</CardBody>
							);
						}

						// Hero Cards Repeater Tab
						return (
							<CardBody style={ { padding: '28px 32px' } }>
								{ /* Repeater Section Header */ }
								<div
									style={ {
										display: 'flex',
										alignItems: 'center',
										justifyContent: 'space-between',
										marginBottom: '24px',
										paddingBottom: '18px',
										borderBottom: '1px solid #f3f4f6',
									} }
								>
									<div>
										<h2 style={ { margin: '0 0 4px 0', fontSize: '18px', fontWeight: 700, display: 'flex', alignItems: 'center', gap: '8px', color: '#111827' } }>
											<i className="ri-stack-line" style={ { color: '#3b82f6' } } />
											{ __( 'Hero Scroll Product Cards (Repeater)', 'raka-preorder-theme' ) }
										</h2>
										<p style={ { color: '#6b7280', fontSize: '13px', margin: 0 } }>
											{ __( 'Add as many product cards as you like. They will automatically animate in sequence as the visitor scrolls down.', 'raka-preorder-theme' ) }
										</p>
									</div>
									<Button
										variant="primary"
										onClick={ handleAddCard }
										style={ {
											height: '38px',
											padding: '0 18px',
											fontSize: '13px',
											fontWeight: 600,
											display: 'inline-flex',
											alignItems: 'center',
											gap: '6px',
											borderRadius: '6px',
											background: '#10b981',
											borderColor: '#10b981',
										} }
									>
										<i className="ri-add-line" style={ { fontSize: '16px' } } />
										{ __( 'Add Product Card', 'raka-preorder-theme' ) }
									</Button>
								</div>

								{ /* If no cards yet */ }
								{ heroCards.length === 0 && (
									<div
										style={ {
											padding: '48px 24px',
											textAlign: 'center',
											border: '2px dashed #d1d5db',
											borderRadius: '10px',
											background: '#f9fafb',
										} }
									>
										<i className="ri-shopping-bag-line" style={ { fontSize: '40px', color: '#9ca3af' } } />
										<h3 style={ { margin: '12px 0 6px 0', fontSize: '16px', fontWeight: 600, color: '#374151' } }>
											{ __( 'No Hero Product Cards Added Yet', 'raka-preorder-theme' ) }
										</h3>
										<p style={ { color: '#6b7280', fontSize: '13px', maxWidth: '420px', margin: '0 auto 16px auto' } }>
											{ __( 'Click the button below to add your first product card to appear during the front page scroll.', 'raka-preorder-theme' ) }
										</p>
										<Button variant="primary" onClick={ handleAddCard }>
											<i className="ri-add-line" style={ { marginRight: '6px' } } />
											{ __( 'Add First Product Card', 'raka-preorder-theme' ) }
										</Button>
									</div>
								) }

								{ /* Card Items List */ }
								<div style={ { display: 'flex', flexDirection: 'column', gap: '20px' } }>
									{ heroCards.map( ( card, idx ) => {
										const isExpanded = activeCardIndex === idx;
										const selectedProd = wcData.products.find( ( p ) => p.id === Number( card.product_id ) );
										const displayTitle = card.title || ( selectedProd ? selectedProd.title : `Card #${ idx + 1 } (No Product Selected)` );
										const displayTag = card.tag || 'PRE-ORDER';
										const displayPrice = card.price || ( selectedProd ? selectedProd.price : '' );
										const displayImage = card.image_url || ( selectedProd ? selectedProd.image_url : '' );

										return (
											<div
												key={ idx }
												style={ {
													border: isExpanded ? '1px solid #3b82f6' : '1px solid #e5e7eb',
													borderRadius: '10px',
													background: '#fff',
													boxShadow: isExpanded ? '0 4px 14px rgba(59, 130, 246, 0.08)' : '0 1px 3px rgba(0,0,0,0.03)',
													overflow: 'hidden',
													transition: 'all 0.2s ease',
												} }
											>
												{ /* Card Item Header Bar */ }
												<div
													onClick={ () => setActiveCardIndex( isExpanded ? -1 : idx ) }
													style={ {
														padding: '14px 20px',
														background: isExpanded ? '#f0f7ff' : '#f9fafb',
														display: 'flex',
														alignItems: 'center',
														justifyContent: 'space-between',
														cursor: 'pointer',
														userSelect: 'none',
														borderBottom: isExpanded ? '1px solid #dbeafe' : 'none',
													} }
												>
													<div style={ { display: 'flex', alignItems: 'center', gap: '12px' } }>
														<span
															style={ {
																background: isExpanded ? '#3b82f6' : '#6b7280',
																color: '#fff',
																borderRadius: '6px',
																padding: '2px 8px',
																fontSize: '11px',
																fontWeight: 700,
															} }
														>
															#{ idx + 1 }
														</span>
														{ displayImage && (
															<img
																src={ displayImage }
																alt=""
																style={ {
																	width: '28px',
																	height: '28px',
																	borderRadius: '4px',
																	objectFit: 'cover',
																	border: '1px solid #e5e7eb',
																} }
															/>
														) }
														<strong style={ { fontSize: '14px', color: '#111827' } }>
															{ displayTitle }
														</strong>
														<span
															style={ {
																background: '#e0f2fe',
																color: '#0369a1',
																padding: '2px 8px',
																borderRadius: '4px',
																fontSize: '11px',
																fontWeight: 600,
															} }
														>
															{ displayTag }
														</span>
														{ displayPrice && (
															<span style={ { fontSize: '12px', fontWeight: 600, color: '#059669' } }>
																{ displayPrice }
															</span>
														) }
														<span style={ { fontSize: '11px', color: '#9ca3af' } }>
															[{ card.position || 'left' }]
														</span>
													</div>

													{ /* Actions bar */ }
													<div
														style={ { display: 'flex', alignItems: 'center', gap: '4px' } }
														onClick={ ( e ) => e.stopPropagation() }
													>
														<button
															type="button"
															disabled={ idx === 0 }
															onClick={ () => handleMoveCard( idx, -1 ) }
															title={ __( 'Move Up', 'raka-preorder-theme' ) }
															style={ {
																background: 'transparent',
																border: 'none',
																padding: '4px 6px',
																cursor: idx === 0 ? 'not-allowed' : 'pointer',
																color: idx === 0 ? '#d1d5db' : '#4b5563',
																fontSize: '16px',
																borderRadius: '4px',
															} }
														>
															<i className="ri-arrow-up-s-line" />
														</button>
														<button
															type="button"
															disabled={ idx === heroCards.length - 1 }
															onClick={ () => handleMoveCard( idx, 1 ) }
															title={ __( 'Move Down', 'raka-preorder-theme' ) }
															style={ {
																background: 'transparent',
																border: 'none',
																padding: '4px 6px',
																cursor: idx === heroCards.length - 1 ? 'not-allowed' : 'pointer',
																color: idx === heroCards.length - 1 ? '#d1d5db' : '#4b5563',
																fontSize: '16px',
																borderRadius: '4px',
															} }
														>
															<i className="ri-arrow-down-s-line" />
														</button>
														<button
															type="button"
															onClick={ () => handleDuplicateCard( idx ) }
															title={ __( 'Duplicate Card', 'raka-preorder-theme' ) }
															style={ {
																background: 'transparent',
																border: 'none',
																padding: '4px 6px',
																cursor: 'pointer',
																color: '#4b5563',
																fontSize: '16px',
																borderRadius: '4px',
															} }
														>
															<i className="ri-file-copy-line" />
														</button>
														<button
															type="button"
															onClick={ () => handleRemoveCard( idx ) }
															title={ __( 'Remove Card', 'raka-preorder-theme' ) }
															style={ {
																background: 'transparent',
																border: 'none',
																padding: '4px 6px',
																cursor: 'pointer',
																color: '#ef4444',
																fontSize: '16px',
																borderRadius: '4px',
															} }
														>
															<i className="ri-delete-bin-6-line" />
														</button>
														<button
															type="button"
															onClick={ () => setActiveCardIndex( isExpanded ? -1 : idx ) }
															style={ {
																background: 'transparent',
																border: 'none',
																padding: '4px 6px',
																cursor: 'pointer',
																color: '#6b7280',
																fontSize: '16px',
															} }
														>
															<i className={ isExpanded ? 'ri-arrow-up-line' : 'ri-arrow-down-line' } />
														</button>
													</div>
												</div>

												{ /* Card Item Body / Controls */ }
												{ isExpanded && (
													<div style={ { padding: '24px' } }>
														{ /* Product Selection */ }
														<div style={ { background: '#f8fafc', padding: '16px 20px', borderRadius: '8px', border: '1px solid #e2e8f0', marginBottom: '20px' } }>
															<Select2Control
																label={ __( 'Select WooCommerce Product', 'raka-preorder-theme' ) }
																icon="ri-shopping-bag-3-line"
																value={ card.product_id }
																options={ productOptions }
																onChange={ ( val ) => handleCardChange( idx, 'product_id', val ) }
																placeholder={ __( '-- Search & Pick Product (or leave empty for custom) --', 'raka-preorder-theme' ) }
																help={ __( 'Selecting a product automatically populates Title, Price, Description, Thumbnail, and Link.', 'raka-preorder-theme' ) }
															/>
														</div>

														{ /* Row 1: Tag + Position */ }
														<Flex gap={ 4 } style={ { marginBottom: '18px' } }>
															<FlexItem isBlock>
																<TextControl
																	label={ __( 'Top Badge / Tag', 'raka-preorder-theme' ) }
																	value={ card.tag ?? '' }
																	onChange={ ( val ) => handleCardChange( idx, 'tag', val ) }
																	placeholder={ __( 'e.g. PRE-ORDER, NEW ARRIVAL, HOT DEAL', 'raka-preorder-theme' ) }
																/>
															</FlexItem>
															<FlexItem isBlock>
																<SelectControl
																	label={ __( 'Screen Position / Alignment', 'raka-preorder-theme' ) }
																	value={ card.position ?? 'left' }
																	options={ POSITION_OPTIONS }
																	onChange={ ( val ) => handleCardChange( idx, 'position', val ) }
																/>
															</FlexItem>
														</Flex>

														{ /* Row 2: Custom Title + Price */ }
														<Flex gap={ 4 } style={ { marginBottom: '18px' } }>
															<FlexItem isBlock>
																<TextControl
																	label={ __( 'Custom Title (Leave blank to use Product Name)', 'raka-preorder-theme' ) }
																	value={ card.title ?? '' }
																	onChange={ ( val ) => handleCardChange( idx, 'title', val ) }
																	placeholder={ selectedProd ? selectedProd.title : __( 'e.g. Vortex 8K Keyboard', 'raka-preorder-theme' ) }
																/>
															</FlexItem>
															<FlexItem isBlock>
																<TextControl
																	label={ __( 'Custom Price Tag (Leave blank to use Product Price)', 'raka-preorder-theme' ) }
																	value={ card.price ?? '' }
																	onChange={ ( val ) => handleCardChange( idx, 'price', val ) }
																	placeholder={ selectedProd ? selectedProd.price : '฿4,990' }
																/>
															</FlexItem>
														</Flex>

														{ /* Row 3: Description */ }
														<div style={ { marginBottom: '18px' } }>
															<TextareaControl
																label={ __( 'Description / Key Features (Leave blank to use Product Excerpt)', 'raka-preorder-theme' ) }
																rows={ 3 }
																value={ card.description ?? '' }
																onChange={ ( val ) => handleCardChange( idx, 'description', val ) }
																placeholder={ selectedProd ? selectedProd.description : __( 'Short summary of specs or highlights...', 'raka-preorder-theme' ) }
															/>
														</div>

														{ /* Row 4: CTA Button Text + Destination URL */ }
														<Flex gap={ 4 } style={ { marginBottom: '18px' } }>
															<FlexItem isBlock>
																<TextControl
																	label={ __( 'CTA Button Text', 'raka-preorder-theme' ) }
																	value={ card.button_text ?? 'Pre-Order Now' }
																	onChange={ ( val ) => handleCardChange( idx, 'button_text', val ) }
																	placeholder="Pre-Order Now"
																/>
															</FlexItem>
															<FlexItem isBlock>
																<TextControl
																	label={ __( 'Destination URL (Leave blank to use Product Page)', 'raka-preorder-theme' ) }
																	value={ card.url ?? '' }
																	onChange={ ( val ) => handleCardChange( idx, 'url', val ) }
																	placeholder={ selectedProd ? selectedProd.permalink : 'https://...' }
																/>
															</FlexItem>
														</Flex>

														{ /* Row 5: Custom Image URL */ }
														<div>
															<TextControl
																label={ __( 'Custom Image URL (Optional override)', 'raka-preorder-theme' ) }
																value={ card.image_url ?? '' }
																onChange={ ( val ) => handleCardChange( idx, 'image_url', val ) }
																placeholder={ selectedProd ? selectedProd.image_url : 'https://.../product.jpg' }
																help={ __( 'Leave blank to automatically use the product featured image.', 'raka-preorder-theme' ) }
															/>
														</div>

														{ /* Card Live Preview Box */ }
														<div
															style={ {
																marginTop: '20px',
																padding: '16px 20px',
																background: '#0f172a',
																borderRadius: '12px',
																border: '1px solid #334155',
																color: '#fff',
															} }
														>
															<div style={ { fontSize: '11px', textTransform: 'uppercase', tracking: '0.1em', color: '#94a3b8', marginBottom: '12px', fontWeight: 600 } }>
																<i className="ri-eye-line" style={ { marginRight: '5px' } } />
																{ __( 'Live Front-End Card Preview', 'raka-preorder-theme' ) } ({ card.position || 'left' })
															</div>
															<div
																style={ {
																	background: 'rgba(255, 255, 255, 0.08)',
																	backdropFilter: 'blur(12px)',
																	border: '1px solid rgba(255, 255, 255, 0.15)',
																	borderRadius: '16px',
																	padding: '18px 20px',
																	display: 'flex',
																	gap: '16px',
																	alignItems: 'center',
																} }
															>
																{ displayImage && (
																	<img
																		src={ displayImage }
																		alt=""
																		style={ {
																			width: '64px',
																			height: '64px',
																			borderRadius: '10px',
																			objectFit: 'cover',
																			border: '1px solid rgba(255,255,255,0.2)',
																			flexShrink: 0,
																		} }
																	/>
																) }
																<div style={ { flex: 1, minWidth: 0 } }>
																	<div style={ { display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '4px' } }>
																		<span
																			style={ {
																				background: 'rgba(16, 185, 129, 0.2)',
																				color: '#34d399',
																				border: '1px solid rgba(16, 185, 129, 0.3)',
																				padding: '2px 8px',
																				borderRadius: '20px',
																				fontSize: '10px',
																				fontWeight: 700,
																				letterSpacing: '0.05em',
																				textTransform: 'uppercase',
																			} }
																		>
																			{ displayTag }
																		</span>
																		{ displayPrice && (
																			<span style={ { fontSize: '12px', fontWeight: 700, color: '#f8fafc' } }>
																				{ displayPrice }
																			</span>
																		) }
																	</div>
																	<h4 style={ { margin: '0 0 4px 0', fontSize: '15px', fontWeight: 700, color: '#fff' } }>
																		{ displayTitle }
																	</h4>
																	<p style={ { margin: 0, fontSize: '12px', color: '#cbd5e1', lineHeight: 1.5, overflow: 'hidden', textOverflow: 'ellipsis', display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical' } }>
																		{ card.description || ( selectedProd ? selectedProd.description : 'Product features and specs description will appear here...' ) }
																	</p>
																</div>
																<button
																	type="button"
																	style={ {
																		background: '#fff',
																		color: '#0f172a',
																		border: 'none',
																		borderRadius: '8px',
																		padding: '8px 14px',
																		fontSize: '12px',
																		fontWeight: 700,
																		cursor: 'pointer',
																		flexShrink: 0,
																	} }
																>
																	{ card.button_text || 'Pre-Order Now' } &rarr;
																</button>
															</div>
														</div>
													</div>
												) }
											</div>
										);
									} ) }
								</div>

								{ /* Bottom Add Card Bar */ }
								{ heroCards.length > 0 && (
									<div style={ { marginTop: '20px', textAlign: 'center' } }>
										<Button
											variant="secondary"
											onClick={ handleAddCard }
											style={ {
												width: '100%',
												padding: '14px',
												border: '2px dashed #cbd5e1',
												borderRadius: '8px',
												fontSize: '14px',
												fontWeight: 600,
												color: '#475569',
												background: '#f8fafc',
											} }
										>
											<i className="ri-add-circle-line" style={ { fontSize: '18px', marginRight: '6px' } } />
											{ __( '+ Add Another Product Card', 'raka-preorder-theme' ) }
										</Button>
									</div>
								) }
							</CardBody>
						);
					} }
				</TabPanel>
			</Card>

			{ /* Footer Save Bar */ }
			<div style={ { marginTop: '24px', display: 'flex', justifyContent: 'flex-end' } }>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ saving }
					disabled={ saving }
					style={ {
						height: '44px',
						padding: '0 32px',
						fontSize: '14px',
						fontWeight: 600,
						display: 'inline-flex',
						alignItems: 'center',
						gap: '8px',
						borderRadius: '8px',
						background: '#3b82f6',
						borderColor: '#3b82f6',
					} }
				>
					<i className="ri-save-3-line" style={ { fontSize: '18px' } } />
					{ saving ? __( 'Saving Settings...', 'raka-preorder-theme' ) : __( 'Save All Settings', 'raka-preorder-theme' ) }
				</Button>
			</div>
		</div>
	);
}
