import { useState, useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import apiFetch from '@wordpress/api-fetch';
import {
    RiUser3Line,
    RiFacebookCircleLine,
    RiShieldCheckLine,
    RiShieldAlertLine,
    RiImageAddLine,
    RiLinkM,
    RiDeleteBinLine,
    RiAddLine,
    RiCheckLine,
    RiGlobalLine
} from '@remixicon/react';

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
        return (
            <div style={{ padding: '24px', textAlign: 'center', color: '#64748b', fontSize: '13px' }}>
                กำลังโหลดข้อมูลลูกค้า...
            </div>
        );
    }

    const currentAvatarUrl = meta.wsr_fb_avatar_url || '';
    const avatarUrls = getFbAvatarUrls();

    return (
        <div className="wsr-customer-panel" style={{ fontFamily: 'var(--wsr-font)', color: '#0f172a' }}>
            
            {/* Card 1: Identity & Channels */}
            <div className="wsr-meta-card">
                <div className="wsr-meta-card__header">
                    <h4 className="wsr-meta-card__title">
                        <RiUser3Line size={16} color="#4169F7" /> บัญชีและช่องทางการขาย
                    </h4>
                    <span className="wsr-meta-card__badge">{meta.wsr_sales_channel || 'Facebook'}</span>
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '16px' }}>
                    <div className="wsr-field">
                        <label style={{ fontSize: '12px', fontWeight: 600, color: '#475569', marginBottom: '4px' }}>ชื่อบัญชีโซเชียล (Social Name)</label>
                        <input
                            type="text"
                            value={ meta.wsr_social_name || '' }
                            onChange={ ( e ) => updateMeta( 'wsr_social_name', e.target.value ) }
                            placeholder="เช่น Suphakorn Innoi"
                            className="wsr-pos-input"
                            style={{ fontSize: '14px', padding: '9px 12px' }}
                        />
                        <span style={{ fontSize: '11px', color: '#64748b', marginTop: '2px' }}>ชื่อหรือบัญชีผู้ใช้บนแพลตฟอร์มโซเชียล</span>
                    </div>

                    <div className="wsr-field">
                        <label style={{ fontSize: '12px', fontWeight: 600, color: '#475569', marginBottom: '4px' }}>ช่องทางการติดต่อ/ขาย</label>
                        <select
                            value={ meta.wsr_sales_channel || 'facebook' }
                            onChange={ ( e ) => updateMeta( 'wsr_sales_channel', e.target.value ) }
                            className="wsr-pos-input"
                            style={{ fontSize: '14px', padding: '9px 12px' }}
                        >
                            <option value="facebook">Facebook</option>
                            <option value="shopee">Shopee</option>
                            <option value="tiktok">TikTok</option>
                            <option value="line">LINE</option>
                            <option value="lazada">Lazada</option>
                            <option value="other">อื่นๆ (Other)</option>
                        </select>
                        <span style={{ fontSize: '11px', color: '#64748b', marginTop: '2px' }}>แพลตฟอร์มหลักที่ใช้ปิดการขายกับลูกค้ารายนี้</span>
                    </div>
                </div>

                <div className="wsr-field" style={{ marginTop: '14px' }}>
                    <label style={{ fontSize: '12px', fontWeight: 600, color: '#475569', marginBottom: '4px' }}>ลิงก์หน้าโปรไฟล์ (URL)</label>
                    <div style={{ display: 'flex', gap: '8px' }}>
                        <input
                            type="text"
                            value={ meta.wsr_social_url || '' }
                            onChange={ ( e ) => updateMeta( 'wsr_social_url', e.target.value ) }
                            placeholder="https://www.facebook.com/..."
                            className="wsr-pos-input"
                            style={{ fontSize: '14px', padding: '9px 12px' }}
                        />
                        { meta.wsr_social_url && (
                            <a
                                href={ meta.wsr_social_url }
                                target="_blank"
                                rel="noreferrer"
                                className="button"
                                style={{ display: 'inline-flex', alignItems: 'center', gap: '4px', whiteSpace: 'nowrap', borderRadius: '8px', fontSize: '12px' }}
                            >
                                <RiGlobalLine size={14} /> เปิดดู
                            </a>
                        ) }
                    </div>
                </div>
            </div>

            {/* Card 2: Security & Profile Flag */}
            <div className="wsr-meta-card">
                <div className="wsr-meta-card__header">
                    <h4 className="wsr-meta-card__title">
                        <RiShieldCheckLine size={16} color="#4169F7" /> การตรวจสอบความปลอดภัย & Flag
                    </h4>
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '16px' }}>
                    <div className="wsr-field">
                        <label style={{ fontSize: '12px', fontWeight: 600, color: '#475569', marginBottom: '4px' }}>สถานะลูกค้า (Facebook Flag)</label>
                        <select
                            value={ meta.wsr_fb_flag || '' }
                            onChange={ ( e ) => updateMeta( 'wsr_fb_flag', e.target.value ) }
                            className="wsr-pos-input"
                            style={{
                                fontSize: '14px',
                                padding: '9px 12px',
                                borderColor: meta.wsr_fb_flag === 'scammer' ? '#fca5a5' : ( meta.wsr_fb_flag === 'suspicious' ? '#fde68a' : '#cbd5e1' ),
                                background: meta.wsr_fb_flag === 'scammer' ? '#fef2f2' : ( meta.wsr_fb_flag === 'suspicious' ? '#fffbeb' : '#ffffff' )
                            }}
                        >
                            <option value="">ปกติ (ลูกค้าทั่วไป)</option>
                            <option value="suspicious">น่าสงสัย / อาจสวม ID</option>
                            <option value="scammer">ยืนยันว่าเป็นมิจฉาชีพ</option>
                            <option value="verified">ยืนยันว่าเป็นลูกค้าจริง</option>
                        </select>
                    </div>

                    <div className="wsr-field">
                        <label style={{ fontSize: '12px', fontWeight: 600, color: '#475569', marginBottom: '4px' }}>หมายเหตุประจำตัว (Flag Note)</label>
                        <select
                            value={ meta.wsr_fb_flag_note || '' }
                            onChange={ ( e ) => updateMeta( 'wsr_fb_flag_note', e.target.value ) }
                            className="wsr-pos-input"
                            style={{ fontSize: '14px', padding: '9px 12px' }}
                        >
                            <option value="">เลือกหมายเหตุที่เคยบันทึกไว้...</option>
                            { [ ...flagNotePresets, meta.wsr_fb_flag_note || '' ]
                                .filter( ( v, i, arr ) => typeof v === 'string' && v.trim() !== '' && arr.indexOf( v ) === i )
                                .map( ( v ) => (
                                    <option key={ v } value={ v }>{ v }</option>
                                ) ) }
                        </select>
                    </div>
                </div>

                <div style={{ marginTop: '12px', background: '#f8fafc', padding: '12px', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
                    <div style={{ fontSize: '11px', fontWeight: 600, color: '#475569', marginBottom: '6px' }}>เพิ่มหมายเหตุใหม่สำหรับบันทึกใช้ซ้ำ:</div>
                    <div style={{ display: 'flex', gap: '8px' }}>
                        <input
                            type="text"
                            value={ newFlagNotePreset }
                            onChange={ ( e ) => setNewFlagNotePreset( e.target.value ) }
                            placeholder="เช่น เคยต่อราคาหนัก, ใช้ชื่อ/รูปซ้ำ, เปลี่ยนบัญชีบ่อย"
                            className="wsr-pos-input"
                            style={{ fontSize: '13px', padding: '6px 10px', height: '34px' }}
                        />
                        <button
                            type="button"
                            onClick={ addFlagNotePreset }
                            className="button button-secondary"
                            style={{ fontSize: '12px', whiteSpace: 'nowrap', borderRadius: '6px' }}
                        >
                            <RiAddLine size={14} /> เพิ่มตัวเลือก
                        </button>
                        { meta.wsr_fb_flag_note && (
                            <button
                                type="button"
                                onClick={ removeFlagNotePreset }
                                className="button"
                                style={{ fontSize: '12px', whiteSpace: 'nowrap', borderRadius: '6px', color: '#b91c1c', borderColor: '#fca5a5' }}
                            >
                                <RiDeleteBinLine size={14} /> ลบออกจากรายการ
                            </button>
                        ) }
                    </div>
                </div>
            </div>

            {/* Card 3: Avatar & Photo Identification */}
            <div className="wsr-meta-card">
                <div className="wsr-meta-card__header">
                    <h4 className="wsr-meta-card__title">
                        <RiFacebookCircleLine size={16} color="#4169F7" /> รูปโปรไฟล์ & Avatar ที่ใช้ตอนขาย
                    </h4>
                    <button
                        type="button"
                        onClick={ addFbAvatarUrlRow }
                        className="button button-secondary"
                        style={{ display: 'inline-flex', alignItems: 'center', gap: '4px', fontSize: '12px', borderRadius: '6px' }}
                    >
                        <RiAddLine size={14} /> เพิ่ม URL รูปภาพ
                    </button>
                </div>

                {/* Avatar Preview Section */}
                <div style={{ display: 'flex', gap: '16px', alignItems: 'center', marginBottom: '16px', background: '#f8fafc', padding: '12px', borderRadius: '8px', border: '1px solid #e2e8f0' }}>
                    <div style={{
                        width: '56px',
                        height: '56px',
                        borderRadius: '50%',
                        overflow: 'hidden',
                        background: '#ffffff',
                        border: '2px solid #cbd5e1',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        flexShrink: 0
                    }}>
                        { currentAvatarUrl ? (
                            <img src={ currentAvatarUrl } alt="Avatar" style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                        ) : (
                            <RiUser3Line size={24} color="#94a3b8" />
                        )}
                    </div>
                    <div>
                        <div style={{ fontSize: '12px', fontWeight: 600, color: '#0f172a' }}>Avatar หลักที่กำลังใช้งาน</div>
                        <div style={{ fontSize: '11px', color: '#64748b', wordBreak: 'break-all' }}>
                            { currentAvatarUrl ? currentAvatarUrl : 'ยังไม่มีการเลือกรูป Avatar หลัก' }
                        </div>
                    </div>
                </div>

                {/* Avatar URLs List */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: '8px', marginBottom: '14px' }}>
                    { avatarUrls.length === 0 ? (
                        <div style={{ fontSize: '12px', color: '#94a3b8', fontStyle: 'italic', padding: '4px 0' }}>
                            ยังไม่มีรายการ Avatar URL ให้กด "เพิ่ม URL รูปภาพ" หรือเลือกจากคลังสื่อด้านล่าง
                        </div>
                    ) : (
                        avatarUrls.map( ( url, idx ) => {
                            const isCurrent = String( currentAvatarUrl ) === String( url );
                            return (
                                <div key={ idx } style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                                    <input
                                        type="text"
                                        value={ url }
                                        onChange={ ( e ) => updateFbAvatarUrlRow( idx, e.target.value ) }
                                        placeholder="วางลิงก์รูปโปรไฟล์ Facebook..."
                                        className="wsr-pos-input"
                                        style={{ fontSize: '13px', padding: '7px 10px' }}
                                    />
                                    <button
                                        type="button"
                                        onClick={ () => setCurrentFbAvatarUrl( url ) }
                                        disabled={ ! url || isCurrent }
                                        className={ `button ${ isCurrent ? 'button-primary' : 'button-secondary' }` }
                                        style={{ fontSize: '11px', whiteSpace: 'nowrap', borderRadius: '6px' }}
                                    >
                                        { isCurrent ? <><RiCheckLine size={12} /> ใช้อยู่</> : 'เลือกใช้' }
                                    </button>
                                    <button
                                        type="button"
                                        onClick={ () => removeFbAvatarUrlRow( idx ) }
                                        className="button"
                                        style={{ color: '#b91c1c', borderColor: '#fca5a5', borderRadius: '6px', padding: '0 8px' }}
                                        title="ลบแถวนี้"
                                    >
                                        <RiDeleteBinLine size={13} />
                                    </button>
                                </div>
                            );
                        } )
                    ) }
                </div>

                {/* Media Upload & Flags */}
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '12px', paddingTop: '10px', borderTop: '1px solid #f1f5f9' }}>
                    <MediaUploadCheck>
                        <MediaUpload
                            onSelect={ ( media ) => {
                                if ( media?.url ) {
                                    setFbAvatarUrls( [ ...avatarUrls, media.url ] );
                                    setCurrentFbAvatarUrl( media.url );
                                }
                                updateMeta( 'wsr_fb_avatar_id', media?.id || '' );
                            } }
                            allowedTypes={ [ 'image' ] }
                            value={ meta.wsr_fb_avatar_id || '' }
                            render={ ( { open } ) => (
                                <div style={{ display: 'flex', gap: '8px', alignItems: 'center' }}>
                                    <button type="button" onClick={ open } className="button button-secondary" style={{ fontSize: '12px', borderRadius: '6px' }}>
                                        <RiImageAddLine size={14} /> เลือกรูปจาก Media Library
                                    </button>
                                    { meta.wsr_fb_avatar_id && (
                                        <button
                                            type="button"
                                            onClick={ () => updateMeta( 'wsr_fb_avatar_id', '' ) }
                                            className="button"
                                            style={{ fontSize: '11px', color: '#b91c1c' }}
                                        >
                                            ล้างรูป Media
                                        </button>
                                    ) }
                                </div>
                            ) }
                        />
                    </MediaUploadCheck>

                    <label style={{ display: 'inline-flex', alignItems: 'center', gap: '6px', cursor: 'pointer', fontSize: '12px', color: '#475569' }}>
                        <input
                            type="checkbox"
                            checked={ ( meta.wsr_fb_avatar_spam || '' ) === '1' }
                            onChange={ ( e ) => updateMeta( 'wsr_fb_avatar_spam', e.target.checked ? '1' : '' ) }
                            style={{ borderRadius: '4px' }}
                        />
                        <span>ทำเครื่องหมายว่า Avatar นี้เป็น Spam</span>
                    </label>
                </div>

                <input type="hidden" name="wsr_fb_avatar_url" value={ currentAvatarUrl } />
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
