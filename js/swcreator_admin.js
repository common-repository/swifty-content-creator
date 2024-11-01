( function( $ ) {

    function __( s ) {
        if( swifty_data && swifty_data.i18n && swifty_data.i18n[ s ] ) {
            return swifty_data.i18n[ s ];
        } else {
            return s;
        }
    }

    function getEditUrl() {
        return scc_data.view_url + ( scc_data.view_url.indexOf( '?' ) >= 0 ? '&' : '?' ) + 'swcreator_edit=main';
    }

    $( function() {
        var data = scc_data.swcreator_data;
        var active = false;

        if( data && typeof data === 'object' ) {
            if( ( ! data.hasOwnProperty( 'active' ) && data.hasOwnProperty( 'rows' ) ) || data.active ) {
                active = true;
            }
        }

        var $el = $( '<a class="swc_edit_with_swifty wp-switch-editor"><span class="wp-media-buttons-icon"></span>&nbsp; ' + __( 'Edit with Swifty' ) + '</a>' );

        $( '.wp-editor-tabs' ).prepend( $el );

        $el.click( function( ev ) {
            ev.preventDefault();

            if( $( '#auto_draft' ).length > 0 ) {
                var scc_warning = __( 'Please publish or save draft before editing with Swifty Content Creator.' ) + '\n\n';
                alert( scc_warning );
            } else {
                // Disable the browser warning that is shown when you exit the page.
                window.onbeforeunload = null;

                window.location = getEditUrl();
            }
        } );

        if( scc_data.wpautop_option === 'off' ) {
            var dummy_removep = function( html ) {
                return html;
            };
            window.wp.editor.removep = dummy_removep;
            window.wp.editor.wpautop = dummy_removep;
        }

        if( scc_data.url_determine_image_sizes ) {
            if( typeof window.swiftyStartDetermineImageSizes === 'function' ) {
                window.swiftyStartDetermineImageSizes( scc_data.page_id, scc_data.url_determine_image_sizes );
            }
        } else {
            if( typeof window.swiftyCheckImageSizesToBeDetermined === 'function' ) {
                window.swiftyCheckImageSizesToBeDetermined( scc_data.page_id );
            }
        }
    } );

} )( jQuery );
