import { render } from '@wordpress/element';
import CustomerMetaFields from './customer-meta';
import ProductMetaFields from './product-meta';
import SalesRecordMetaFields from './sales-meta';
import DashboardApp from './dashboard';
import GraphAnalysis from './graph-analysis';
import POSSystem from './pos-system';
import './style.css';

// Initialize React apps on DOM load depending on which container exists
document.addEventListener( 'DOMContentLoaded', () => {

    // 1. Customer Meta Box
    const customerRoot = document.getElementById( 'wsr-customer-root' );
    if ( customerRoot ) {
        const postId = customerRoot.getAttribute('data-post-id');
        import( '@wordpress/element' ).then( ( { createRoot } ) => {
            const root = createRoot( customerRoot );
            root.render( <CustomerMetaFields postId={postId} /> );
        } );
    }

    // 2. Product Meta Box
    const productRoot = document.getElementById( 'wsr-product-root' );
    if ( productRoot ) {
        const postId = productRoot.getAttribute('data-post-id');
        import( '@wordpress/element' ).then( ( { createRoot } ) => {
            const root = createRoot( productRoot );
            root.render( <ProductMetaFields postId={postId} /> );
        } );
    }

    // 3. Sales Record Meta Box
    const salesRoot = document.getElementById( 'wsr-sales-record-root' );
    if ( salesRoot ) {
        const postId = salesRoot.getAttribute('data-post-id');
        import( '@wordpress/element' ).then( ( { createRoot } ) => {
            const root = createRoot( salesRoot );
            root.render( <SalesRecordMetaFields postId={postId} /> );
        } );
    }

    // 4. Dashboard App
    const dashboardRoot = document.getElementById( 'wsr-dashboard-root' );
    if ( dashboardRoot ) {
        import( '@wordpress/element' ).then( ( { createRoot } ) => {
            const root = createRoot( dashboardRoot );
            root.render( <DashboardApp /> );
        } );
    }

    // 5. Graph Analysis Page
    const graphAnalysisRoot = document.getElementById( 'wsr-graph-analysis-root' );
    if ( graphAnalysisRoot ) {
        import( '@wordpress/element' ).then( ( { createRoot } ) => {
            const root = createRoot( graphAnalysisRoot );
            root.render( <GraphAnalysis /> );
        } );
    }

    // 6. POS System Page
    const posRoot = document.getElementById( 'wsr-pos-root' );
    if ( posRoot ) {
        import( '@wordpress/element' ).then( ( { createRoot } ) => {
            const root = createRoot( posRoot );
            root.render( <POSSystem /> );
        } );
    }
} );
