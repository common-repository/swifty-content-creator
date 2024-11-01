define( [
    'jquery',
    'can',
    'swiftylib/evt'
], function(
    $, can, evt
) {
    'use strict';

    return function() {
        var self = this;
        var instance = self.instance;

        // Insert new asset html (received from server) into the editor (replacing existing widget content)
        can.bind.call( window, 'evt_swc_new_asset_content_received', function( ev, opts ) {
            $.each( instance.widgets.instances, function( ii, widget ) {
                if( $( widget.element.$ ).is( $( opts.container ) ) ) {
                    // Replace the whole content (inner) wrapper by the new content
                    widget.element.setHtml( opts.html );

                    // If a newer version is still being fetched from the server, re-add the refresh spinner.
                    if( parseInt( opts.refreshnr, 10 ) < parseInt( $( opts.container ).attr( 'data-swc_refreshnr' ), 10 ) ) {
                        self.addRefreshSpinner( opts );
                    }

                    // Set the new style tag to the outer wrapper (the out wrapper is not replaced upon new content)
                    $( widget.element.$ ).attr( 'style', opts.style );

                    // Set the new css classes to the outer wrapper (without removing the existing classes, which might by added by CKEditor) (the out wrapper is not replaced upon new content)
                    var classes = opts.classes.split( ' ' );
                    $.each( classes, function( ii, cls ) {
                        $( widget.element.$ ).addClass( cls );
                    } );

                    //editor.fire( 'change' );
                    instance.fire( 'saveSnapshot' ); // Create undo point and trigger change (autosave)

                    self.openEditAfterInsert( widget.element.$, widget.inline );
                }
            } );
        } );

        // Show spinner while refreshing asset from server
        can.bind.call( window, 'evt_swc_asset_reload_from_server_queued', function( ev, opts ) {
            self.addRefreshSpinner( opts );
            $( opts.container ).attr( 'data-swc_refreshnr', opts.refreshnr );
        } );
    }

} );