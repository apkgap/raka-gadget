import { useState, useEffect } from '@wordpress/element';
import { TextControl, SelectControl, Button } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';

const CustomerMetaFields = ( { postId } ) => {
    const [ meta, setMeta ] = useState( {} );
    const [ isLoading, setIsLoading ] = useState( true );
    const [ flagNotePresets, setFlagNotePresets ] = useState( [] );
    const [ newFlagNotePreset, setNewFlagNotePreset ] = useState( '' );

    const FLAG_NOTE_PRESETS_STORAGE_KEY = 'wsr_fb_flag_note_presets_v1';

    // Fetch existing meta
    useEffect( () => {
        apiFetch( { path: `/wp/v2/customers/${postId}` } ).then( ( post ) => {
            setMeta( post.meta || {} );
            setIsLoading( false );
        } );
    }, [ postId ] );

    useEffect( () => {
        if ( isLoading ) {
            return;
        }

        const single = typeof meta.wsr_fb_avatar_url === 'string' ? meta.wsr_fb_avatar_url.trim() : '';
        const arr = Array.isArray( meta.wsr_fb_avatar_urls ) ? meta.wsr_fb_avatar_urls : [];

        if ( arr.length === 0 && single ) {
            updateMeta( 'wsr_fb_avatar_urls', [ single ] );
        }
    }, [ isLoading, meta.wsr_fb_avatar_url, meta.wsr_fb_avatar_urls ] );

    useEffect( () => {
        try {
            const raw = window?.localStorage?.getItem( FLAG_NOTE_PRESETS_STORAGE_KEY );
            if ( raw ) {
                const parsed = JSON.parse( raw );
                if ( Array.isArray( parsed ) ) {
                    setFlagNotePresets( parsed.filter( ( v ) => typeof v === 'string' && v.trim() !== '' ) );
                }
            }
        } catch ( e ) {
        }
    }, [] );

    const updateMeta = ( key, value ) => {
        const newMeta = { ...meta, [ key ]: value };
        setMeta( newMeta );
    };

    const persistFlagNotePresets = ( nextPresets ) => {
        setFlagNotePresets( nextPresets );
        try {
            window?.localStorage?.setItem( FLAG_NOTE_PRESETS_STORAGE_KEY, JSON.stringify( nextPresets ) );
        } catch ( e ) {
        }
    };

    const addFlagNotePreset = () => {
        const value = ( newFlagNotePreset || '' ).trim();
        if ( ! value ) {
            return;
        }

        const next = Array.from( new Set( [ ...flagNotePresets, value ] ) );
        persistFlagNotePresets( next );
        setNewFlagNotePreset( '' );
        updateMeta( 'wsr_fb_flag_note', value );
    };

    const removeFlagNotePreset = () => {
        const value = ( meta.wsr_fb_flag_note || '' ).trim();
        if ( ! value ) {
            return;
        }

        const next = flagNotePresets.filter( ( v ) => v !== value );
        persistFlagNotePresets( next );
    };

    const getFbAvatarUrls = () => {
        if ( Array.isArray( meta.wsr_fb_avatar_urls ) ) {
            return meta.wsr_fb_avatar_urls;
        }
        return [];
    };

    const setFbAvatarUrls = ( nextUrls ) => {
        updateMeta( 'wsr_fb_avatar_urls', nextUrls );

        const current = typeof meta.wsr_fb_avatar_url === 'string' ? meta.wsr_fb_avatar_url : '';
        if ( current && nextUrls.includes( current ) ) {
            return;
        }

        updateMeta( 'wsr_fb_avatar_url', nextUrls[ 0 ] || '' );
    };

    const addFbAvatarUrlRow = () => {
        const next = [ ...getFbAvatarUrls(), '' ];
        setFbAvatarUrls( next );
    };

    const updateFbAvatarUrlRow = ( idx, value ) => {
        const next = getFbAvatarUrls().map( ( v, i ) => ( i === idx ? value : v ) );
        setFbAvatarUrls( next );
        if ( ( meta.wsr_fb_avatar_url || '' ) === ( getFbAvatarUrls()[ idx ] || '' ) ) {
            updateMeta( 'wsr_fb_avatar_url', value );
        }
    };

    const setCurrentFbAvatarUrl = ( value ) => {
        updateMeta( 'wsr_fb_avatar_url', value );
    };

    const removeFbAvatarUrlRow = ( idx ) => {
        const current = String( meta.wsr_fb_avatar_url || '' );
        const urls = getFbAvatarUrls();
        const removed = String( urls[ idx ] || '' );
        const next = urls.filter( ( _, i ) => i !== idx );
        setFbAvatarUrls( next );
        if ( current && removed && current === removed ) {
            updateMeta( 'wsr_fb_avatar_url', next[ 0 ] || '' );
        }
    };

    if ( isLoading ) {
        return <p>กำลังโหลดข้อมูลลูกค้า...</p>;
    }

    return (
        <div className="wsr-customer-panel">
            <div className="wsr-meta-field">
                <TextControl
                    label="ชื่อโซเชียล"
                    value={ meta.wsr_social_name || '' }
                    onChange={ ( value ) => updateMeta( 'wsr_social_name', value ) }
                    help="ชื่อหรือบัญชีผู้ใช้บนแพลตฟอร์มโซเชียล"
                />
            </div>

            <div className="wsr-meta-field">
                <div style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' } }>
                    <div style={ { fontWeight: 600 } }>Facebook Avatar (URL)</div>
                    <Button variant="secondary" onClick={ addFbAvatarUrlRow }>
                        Add
                    </Button>
                </div>

                {getFbAvatarUrls().length === 0 ? (
                    <div style={ { fontSize: '12px', color: '#64748b' } }>
                        ยังไม่มี Avatar URL
                    </div>
                ) : null}

                {getFbAvatarUrls().map( ( url, idx ) => (
                    <div key={ idx } style={ { display: 'grid', gridTemplateColumns: '1fr auto auto', gap: '8px', alignItems: 'end', marginBottom: '8px' } }>
                        <TextControl
                            label={ idx === 0 ? 'รายการ Avatar URL' : '' }
                            value={ url }
                            onChange={ ( value ) => updateFbAvatarUrlRow( idx, value ) }
                            help={ idx === 0 ? 'วางลิงก์รูปโปรไฟล์/รูป avatar ที่เห็นตอนคุยซื้อขาย' : '' }
                        />
                        <Button
                            variant="secondary"
                            onClick={ () => setCurrentFbAvatarUrl( url ) }
                            disabled={ !url || String( meta.wsr_fb_avatar_url || '' ) === String( url || '' ) }
                        >
                            Use
                        </Button>
                        <Button variant="tertiary" isDestructive onClick={ () => removeFbAvatarUrlRow( idx ) }>
                            Remove
                        </Button>
                    </div>
                ) )}

                <input type="hidden" name="wsr_fb_avatar_url" value={ meta.wsr_fb_avatar_url || '' } />
            </div>

            <div className="wsr-meta-field">
                <label style={ { display: 'flex', gap: '8px', alignItems: 'center' } }>
                    <input
                        type="checkbox"
                        checked={ ( meta.wsr_fb_avatar_spam || '' ) === '1' }
                        onChange={ ( e ) => updateMeta( 'wsr_fb_avatar_spam', e.target.checked ? '1' : '' ) }
                    />
                    <span>Avatar นี้เป็น Spam</span>
                </label>
            </div>

            <div className="wsr-meta-field">
                <MediaUploadCheck>
                    <MediaUpload
                        onSelect={ ( media ) => updateMeta( 'wsr_fb_avatar_id', media?.id || '' ) }
                        allowedTypes={ [ 'image' ] }
                        value={ meta.wsr_fb_avatar_id || '' }
                        render={ ( { open } ) => (
                            <div style={ { display: 'flex', gap: '8px', alignItems: 'center' } }>
                                <Button variant="secondary" onClick={ open }>
                                    เลือกรูป Avatar จาก Media
                                </Button>
                                { meta.wsr_fb_avatar_id ? (
                                    <Button
                                        variant="tertiary"
                                        isDestructive
                                        onClick={ () => updateMeta( 'wsr_fb_avatar_id', '' ) }
                                    >
                                        ล้าง
                                    </Button>
                                ) : null }
                            </div>
                        ) }
                    />
                </MediaUploadCheck>
            </div>

            <div className="wsr-meta-field">
                <SelectControl
                    label="Facebook Flag"
                    value={ meta.wsr_fb_flag || '' }
                    options={ [
                        { label: 'ปกติ', value: '' },
                        { label: 'น่าสงสัย / อาจสวม ID', value: 'suspicious' },
                        { label: 'ยืนยันว่าเป็นมิจฉาชีพ', value: 'scammer' },
                        { label: 'ยืนยันว่าเป็นลูกค้าจริง', value: 'verified' },
                    ] }
                    onChange={ ( value ) => updateMeta( 'wsr_fb_flag', value ) }
                />
            </div>

            <div className="wsr-meta-field">
                <SelectControl
                    label="หมายเหตุ (Facebook Flag Note)"
                    value={ meta.wsr_fb_flag_note || '' }
                    options={ [
                        { label: 'เลือกหมายเหตุ...', value: '' },
                        ...[ ...flagNotePresets, meta.wsr_fb_flag_note || '' ]
                            .filter( ( v, i, arr ) => typeof v === 'string' && v.trim() !== '' && arr.indexOf( v ) === i )
                            .map( ( v ) => ( { label: v, value: v } ) ),
                    ] }
                    onChange={ ( value ) => updateMeta( 'wsr_fb_flag_note', value ) }
                    help="เลือกได้จากรายการ หรือพิมพ์เพิ่มด้านล่างเพื่อใช้ซ้ำ"
                />
                <div style={ { display: 'flex', gap: '8px', alignItems: 'flex-end', marginTop: '8px' } }>
                    <div style={ { flex: 1 } }>
                        <TextControl
                            label="เพิ่มหมายเหตุใหม่"
                            value={ newFlagNotePreset }
                            onChange={ ( value ) => setNewFlagNotePreset( value ) }
                            help="เช่น เคยต่อราคาหนัก, ใช้ชื่อ/รูปซ้ำ, เปลี่ยนบัญชีบ่อย"
                        />
                    </div>
                    <Button variant="secondary" onClick={ addFlagNotePreset }>
                        Add
                    </Button>
                    <Button variant="tertiary" isDestructive onClick={ removeFlagNotePreset }>
                        Remove
                    </Button>
                </div>
            </div>
            <div className="wsr-meta-field">
                <TextControl
                    label="ลิงก์หน้าโปรไฟล์ (URL)"
                    value={ meta.wsr_social_url || '' }
                    onChange={ ( value ) => updateMeta( 'wsr_social_url', value ) }
                    help="ลิงก์ไปยังหน้าโปรไฟล์ของลูกค้า"
                />
            </div>
            <div className="wsr-meta-field">
                <SelectControl
                    label="ช่องทางการติดต่อ/ขาย"
                    value={ meta.wsr_sales_channel || 'facebook' }
                    options={ [
                        { label: 'Facebook', value: 'facebook' },
                        { label: 'Shopee', value: 'shopee' },
                        { label: 'TikTok', value: 'tiktok' },
                        { label: 'LINE', value: 'line' },
                        { label: 'Lazada', value: 'lazada' },
                        { label: 'อื่นๆ (Other)', value: 'other' },
                    ] }
                    onChange={ ( value ) => updateMeta( 'wsr_sales_channel', value ) }
                />
            </div>

            {/* Hidden Inputs for WordPress Classic Native Save */}
            <input type="hidden" name="wsr_social_name" value={ meta.wsr_social_name || '' } />
            <input type="hidden" name="wsr_social_url" value={ meta.wsr_social_url || '' } />
            <input type="hidden" name="wsr_sales_channel" value={ meta.wsr_sales_channel || 'facebook' } />
            { Array.isArray( meta.wsr_fb_avatar_urls ) && meta.wsr_fb_avatar_urls.length > 0 ? (
                meta.wsr_fb_avatar_urls.map( ( url, idx ) => (
                    <input key={ idx } type="hidden" name="wsr_fb_avatar_urls[]" value={ url } />
                ) )
            ) : (
                <input type="hidden" name="wsr_fb_avatar_urls_empty" value="1" />
            ) }
            <input type="hidden" name="wsr_fb_avatar_id" value={ meta.wsr_fb_avatar_id || '' } />
            <input type="hidden" name="wsr_fb_avatar_spam" value={ meta.wsr_fb_avatar_spam || '' } />
            <input type="hidden" name="wsr_fb_flag" value={ meta.wsr_fb_flag || '' } />
            <input type="hidden" name="wsr_fb_flag_note" value={ meta.wsr_fb_flag_note || '' } />
        </div>
    );
};

export default CustomerMetaFields;
