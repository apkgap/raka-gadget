import { useState, useEffect, useRef } from '@wordpress/element';
import {
	Button,
	Card,
	CardHeader,
	CardBody,
	TabPanel,
	TextControl,
	TextareaControl,
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
		<div className="raka-select2-field" style={ { marginBottom: '20px' } }>
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

const SLOTS = [
	{
		key: 'sec_2',
		title: __( 'Slot 1: Section 2 Card (Left Large Card)', 'raka-preorder-theme' ),
		badge: 'hero-sec-2',
		icon: 'ri-layout-left-line',
		defaultTag: 'ORIGIN',
		defaultDesc: "Obsidian Studio, established by globally acclaimed visionaries of Fine Line Tattoo, Micro Realism Tattoo, and Minimalist Tattoo, Elias Varen and Mira Solenne, unites the world's and Brooklyn's finest tattoo artists in Brooklyn, NYC.",
	},
	{
		key: 'sec_3',
		title: __( 'Slot 2: Section 3 Card (Right Top Card)', 'raka-preorder-theme' ),
		badge: 'hero-sec-3',
		icon: 'ri-layout-right-line',
		defaultTag: 'Premise',
		defaultDesc: 'Here in our studio is a refuge for intention, where patience meets technique and craft dissolves all limits. With global reverence and an unwavering devotion to the singular.',
	},
	{
		key: 'sec_4_top',
		title: __( 'Slot 3: Section 4 Top Card (Right Statement)', 'raka-preorder-theme' ),
		badge: 'hero-sec-4 top',
		icon: 'ri-layout-top-line',
		defaultTag: '',
		defaultDesc: 'singular visions into striking one of a kind tattoos in partnership with the most refined artists from NYC and from across the globe.',
	},
	{
		key: 'sec_4_bot',
		title: __( 'Slot 4: Section 4 Bottom Card (Left Process Card)', 'raka-preorder-theme' ),
		badge: 'hero-sec-4 bot',
		icon: 'ri-layout-bottom-line',
		defaultTag: 'Process',
		defaultDesc: 'We treat each tattoo as a silent dialogue, a mark that emerges from your story. Your form is a page, and we aspire to inscribe narratives that capture your singularity and essence.',
	},
	{
		key: 'sec_5',
		title: __( 'Slot 5: Section 5 Ending Banner (CTA)', 'raka-preorder-theme' ),
		badge: 'hero-sec-5',
		icon: 'ri-sparkling-line',
		defaultTag: 'CTA',
		defaultDesc: 'FIND YOUR ARTIST',
	},
];

export default function ThemeSettingsApp() {
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const [ options, setOptions ] = useState( {} );
	const [ wcData, setWcData ] = useState( {
		categories: [],
		brands: [],
		products: [],
	} );

	// Fetch initial settings & WooCommerce terms from REST API
	useEffect( () => {
		apiFetch( { path: '/raka/v1/settings' } )
			.then( ( res ) => {
				if ( res && res.options ) {
					setOptions( res.options );
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

	const handleChange = ( field, val ) => {
		setOptions( ( prev ) => ( {
			...prev,
			[ field ]: val,
		} ) );
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

	// Prepare Select2 option arrays
	const categoryOptions = wcData.categories.map( ( c ) => ( {
		value: String( c.id ),
		label: `${ c.name } (${ c.count } items)`,
	} ) );

	const brandOptions = wcData.brands.map( ( b ) => ( {
		value: `${ b.taxonomy }:${ b.id }`,
		label: `${ b.name } (${ b.count } items)`,
	} ) );

	const productOptions = wcData.products.map( ( p ) => ( {
		value: String( p.id ),
		label: `${ p.title } ${ p.price ? '- ' + p.price : '' }`,
	} ) );

	const sourceOptions = [
		{ value: 'custom', label: __( 'Custom Content (Manual text & URL)', 'raka-preorder-theme' ) },
		{ value: 'product_cat', label: __( 'WooCommerce Product Category', 'raka-preorder-theme' ) },
		{ value: 'brand', label: __( 'WooCommerce Brand Term', 'raka-preorder-theme' ) },
		{ value: 'product', label: __( 'WooCommerce Single Product', 'raka-preorder-theme' ) },
	];

	const tabs = [
		{
			name: 'hero_general',
			title: (
				<span style={ { display: 'inline-flex', alignItems: 'center', gap: '6px' } }>
					<i className="ri-movie-line" style={ { fontSize: '16px' } } />
					{ __( 'Hero Media & Title', 'raka-preorder-theme' ) }
				</span>
			),
		},
		...SLOTS.map( ( slot ) => ( {
			name: slot.key,
			title: (
				<span style={ { display: 'inline-flex', alignItems: 'center', gap: '6px' } }>
					<i className={ slot.icon } style={ { fontSize: '16px' } } />
					{ slot.badge }
				</span>
			),
			slot,
		} ) ),
	];

	return (
		<div style={ { maxWidth: '1080px', margin: '20px 0', fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif' } }>
			{ /* Header Banner */ }
			<div
				style={ {
					background: '#1e1e1e',
					color: '#fff',
					borderRadius: '8px',
					padding: '24px 30px',
					marginBottom: '24px',
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'space-between',
					boxShadow: '0 4px 14px rgba(0,0,0,0.1)',
				} }
			>
				<div>
					<div style={ { display: 'flex', alignItems: 'center', gap: '10px' } }>
						<i className="ri-magic-line" style={ { fontSize: '24px', color: '#3858e9' } } />
						<h1 style={ { margin: 0, fontSize: '22px', fontWeight: 700, color: '#fff', letterSpacing: '-0.02em' } }>
							{ __( 'Hero Banner Settings', 'raka-preorder-theme' ) }
						</h1>
					</div>
					<p style={ { margin: '6px 0 0 34px', color: '#a0a0a0', fontSize: '13px' } }>
						{ __( 'Configure Hero Video Scrubbing, Title Typography, and WooCommerce Shadow-Box Cards.', 'raka-preorder-theme' ) }
					</p>
				</div>
				<div>
					<Button
						variant="primary"
						onClick={ handleSave }
						isBusy={ saving }
						disabled={ saving }
						style={ { height: '40px', padding: '0 24px', fontSize: '14px', fontWeight: 600, display: 'inline-flex', alignItems: 'center', gap: '8px' } }
					>
						<i className="ri-save-line" style={ { fontSize: '16px' } } />
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

			<Card style={ { borderRadius: '8px', border: '1px solid #e0e0e0', boxShadow: '0 1px 3px rgba(0,0,0,0.05)' } }>
				<TabPanel
					className="raka-theme-settings-tab-panel"
					activeClass="active-tab"
					tabs={ tabs }
				>
					{ ( tab ) => {
						if ( tab.name === 'hero_general' ) {
							return (
								<CardBody style={ { padding: '28px' } }>
									<h2 style={ { margin: '0 0 16px 0', fontSize: '17px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', color: '#1e1e1e' } }>
										<i className="ri-movie-2-line" style={ { color: '#2271b1' } } />
										{ __( 'Main Title & Video Media', 'raka-preorder-theme' ) }
									</h2>
									<p style={ { color: '#646970', fontSize: '13px', marginBottom: '24px' } }>
										{ __( 'These controls manage the top background video scrubbing, character overlay, and primary typography.', 'raka-preorder-theme' ) }
									</p>

									<TextControl
										label={ __( 'Hero Main Title Text', 'raka-preorder-theme' ) }
										value={ options.hero_main_title ?? 'OBSIDIAN' }
										onChange={ ( val ) => handleChange( 'hero_main_title', val ) }
										help={ __( 'The massive centered typography displayed over the hero banner.', 'raka-preorder-theme' ) }
									/>

									<div style={ { marginTop: '16px' } }>
										<TextareaControl
											label={ __( 'Hero Caption (Bottom Left)', 'raka-preorder-theme' ) }
											rows={ 3 }
											value={ options.hero_caption ?? "RITUALS REFINED:\nTRACING LINE, SHADOW & SKIN\nACROSS A LIVING FORM." }
											onChange={ ( val ) => handleChange( 'hero_caption', val ) }
											help={ __( 'Short poetic caption displayed at the bottom-left of the hero banner.', 'raka-preorder-theme' ) }
										/>
									</div>

									<div style={ { marginTop: '16px' } }>
										<TextControl
											label={ __( 'Background Video MP4 URL (Ultra-Smooth WebCodecs Scrubbing)', 'raka-preorder-theme' ) }
											value={ options.hero_video_url ?? '' }
											onChange={ ( val ) => handleChange( 'hero_video_url', val ) }
											help={ __( 'Direct URL to a 60fps MP4 video used for the scroll scrubber.', 'raka-preorder-theme' ) }
										/>
									</div>

									<div style={ { marginTop: '16px' } }>
										<TextControl
											label={ __( 'Hero Character Overlay Image URL (Figma PNG Asset)', 'raka-preorder-theme' ) }
											value={ options.hero_overlay_url ?? '' }
											onChange={ ( val ) => handleChange( 'hero_overlay_url', val ) }
											help={ __( 'Local transparent PNG of the character overlay that fades during scrolling.', 'raka-preorder-theme' ) }
										/>
									</div>
								</CardBody>
							);
						}

						const slot = tab.slot;
						const p = `card_${ slot.key }_`;
						const currentSource = options[ `${ p }source` ] || 'custom';

						return (
							<CardBody style={ { padding: '28px' } }>
								<div style={ { display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '18px', borderBottom: '1px solid #f0f0f1', paddingBottom: '14px' } }>
									<div>
										<h2 style={ { margin: 0, fontSize: '17px', fontWeight: 600, display: 'flex', alignItems: 'center', gap: '8px', color: '#1e1e1e' } }>
											<i className={ slot.icon } style={ { color: '#2271b1' } } />
											{ slot.title }
										</h2>
										<span style={ { display: 'inline-block', marginTop: '6px', background: '#f0f0f1', color: '#50575e', fontSize: '11px', padding: '2px 8px', borderRadius: '4px', fontWeight: 600 } }>
											ID: { slot.badge }
										</span>
									</div>
								</div>

								{ /* Data Source using Select2 */ }
								<Select2Control
									label={ __( 'Data Source Mode', 'raka-preorder-theme' ) }
									icon="ri-database-2-line"
									value={ currentSource }
									options={ sourceOptions }
									onChange={ ( val ) => handleChange( `${ p }source`, val ) }
									placeholder={ __( 'Select Data Source', 'raka-preorder-theme' ) }
									help={ __( 'Choose whether this card displays manual custom text or dynamically connects to WooCommerce.', 'raka-preorder-theme' ) }
								/>

								{ /* Conditional WooCommerce Select2 controls */ }
								{ currentSource === 'product_cat' && (
									<div style={ { background: '#f6f7f7', padding: '16px 20px', borderRadius: '6px', marginBottom: '20px', border: '1px solid #dcdcde' } }>
										<Select2Control
											label={ __( 'Choose WooCommerce Product Category', 'raka-preorder-theme' ) }
											icon="ri-folder-shared-line"
											value={ options[ `${ p }cat_id` ] }
											options={ categoryOptions }
											onChange={ ( val ) => handleChange( `${ p }cat_id`, val ) }
											placeholder={ __( '-- Search & Select Category --', 'raka-preorder-theme' ) }
											help={ __( 'The card will automatically fetch category name, description, and link to the archive page.', 'raka-preorder-theme' ) }
										/>
									</div>
								) }

								{ currentSource === 'brand' && (
									<div style={ { background: '#f6f7f7', padding: '16px 20px', borderRadius: '6px', marginBottom: '20px', border: '1px solid #dcdcde' } }>
										<Select2Control
											label={ __( 'Choose WooCommerce Brand Term', 'raka-preorder-theme' ) }
											icon="ri-price-tag-3-line"
											value={ options[ `${ p }brand_id` ] }
											options={ brandOptions }
											onChange={ ( val ) => handleChange( `${ p }brand_id`, val ) }
											placeholder={ __( '-- Search & Select Brand --', 'raka-preorder-theme' ) }
											help={ __( 'The card will fetch brand name, description, and term URL automatically.', 'raka-preorder-theme' ) }
										/>
									</div>
								) }

								{ currentSource === 'product' && (
									<div style={ { background: '#f6f7f7', padding: '16px 20px', borderRadius: '6px', marginBottom: '20px', border: '1px solid #dcdcde' } }>
										<Select2Control
											label={ __( 'Choose WooCommerce Product', 'raka-preorder-theme' ) }
											icon="ri-shopping-bag-3-line"
											value={ options[ `${ p }product_id` ] }
											options={ productOptions }
											onChange={ ( val ) => handleChange( `${ p }product_id`, val ) }
											placeholder={ __( '-- Search & Select Product --', 'raka-preorder-theme' ) }
											help={ __( 'The card will show the product title, price tag badge, short description, and permalink.', 'raka-preorder-theme' ) }
										/>
									</div>
								) }

								{ /* Overrides / Custom Content */ }
								<div style={ { marginTop: '20px', borderTop: '1px solid #f0f0f1', paddingTop: '20px' } }>
									<h3 style={ { fontSize: '14px', fontWeight: 600, color: '#1e1e1e', marginBottom: '14px', display: 'flex', alignItems: 'center', gap: '6px' } }>
										<i className="ri-edit-line" style={ { color: '#50575e' } } />
										{ currentSource === 'custom'
											? __( 'Card Content Fields', 'raka-preorder-theme' )
											: __( 'Optional Field Overrides (Leave empty to use WooCommerce data)', 'raka-preorder-theme' ) }
									</h3>

									<Flex gap={ 4 } style={ { marginBottom: '16px' } }>
										<FlexItem isBlock>
											<TextControl
												label={ __( 'Top Badge / Tag', 'raka-preorder-theme' ) }
												value={ options[ `${ p }tag` ] ?? ( currentSource === 'custom' ? slot.defaultTag : '' ) }
												onChange={ ( val ) => handleChange( `${ p }tag`, val ) }
												placeholder={ slot.defaultTag }
											/>
										</FlexItem>
										<FlexItem isBlock>
											<TextControl
												label={ __( 'Card Title', 'raka-preorder-theme' ) }
												value={ options[ `${ p }title` ] ?? '' }
												onChange={ ( val ) => handleChange( `${ p }title`, val ) }
												placeholder={ __( 'e.g. Fine Line Art', 'raka-preorder-theme' ) }
											/>
										</FlexItem>
									</Flex>

									<TextareaControl
										label={ __( 'Card Description / Body Copy', 'raka-preorder-theme' ) }
										rows={ 4 }
										value={ options[ `${ p }description` ] ?? ( currentSource === 'custom' ? slot.defaultDesc : '' ) }
										onChange={ ( val ) => handleChange( `${ p }description`, val ) }
										placeholder={ slot.defaultDesc }
									/>

									<div style={ { marginTop: '16px' } }>
										<TextControl
											label={ __( 'Destination Link URL', 'raka-preorder-theme' ) }
											value={ options[ `${ p }url` ] ?? '' }
											onChange={ ( val ) => handleChange( `${ p }url`, val ) }
											placeholder="https://..."
										/>
									</div>
								</div>
							</CardBody>
						);
					} }
				</TabPanel>
			</Card>

			{ /* Footer Save Bar */ }
			<div style={ { marginTop: '20px', display: 'flex', justifyContent: 'flex-end' } }>
				<Button
					variant="primary"
					onClick={ handleSave }
					isBusy={ saving }
					disabled={ saving }
					style={ { height: '42px', padding: '0 28px', fontSize: '14px', fontWeight: 600, display: 'inline-flex', alignItems: 'center', gap: '8px' } }
				>
					<i className="ri-save-line" style={ { fontSize: '16px' } } />
					{ saving ? __( 'Saving...', 'raka-preorder-theme' ) : __( 'Save Settings', 'raka-preorder-theme' ) }
				</Button>
			</div>
		</div>
	);
}
