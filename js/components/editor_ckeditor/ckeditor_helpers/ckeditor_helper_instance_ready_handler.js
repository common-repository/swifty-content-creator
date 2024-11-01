define( [
    'jquery',
    'swiftylib/evt'
], function(
    $, evt
) {
    'use strict';

    return function( ev ) {
        var self = this;
        var editor = ev.editor;
        var commands = editor.commands;

        self.$toolbar = $( '#cke_' + self.instance.name ).children( '.cke_inner' );

        self.$toolbar.find( '.cke_toolbar' )
            .eq( 1 )
            .css( 'width', '100%' )
            .find( 'a:last' )
            .css( 'float', 'right' );

        if( self.sticky_editor ) {
            commands.hideEditor && commands.hideEditor.enable();
        } else {
            commands.hideEditor && commands.hideEditor.disable();

            self.toggleToolbarVisibility( false );
        }

        // some changes for the generated html
        editor.dataProcessor.writer.lineBreakChars = '\n';

        var noIndentsNoBreaks = {
            indent: false,
            breakBeforeOpen: false,
            breakAfterOpen: false,
            breakBeforeClose: false,
            breakAfterClose: false
        };

        $.each( CKEDITOR.dtd, function ( index ) {
            editor.dataProcessor.writer.setRules( index, noIndentsNoBreaks );
        } );

        // go to the last position in the editor (and make sure the focus is working as expected)
        var range = editor.createRange();
        range.moveToElementEditEnd( range.root );
        editor.getSelection().selectRanges( [ range ] );

        editor.on( 'blur', self.onBlurHandler, self );

        var html = editor.getData();

        evt(
            'swifty_editor_before_init',
            {
                'content': html
            }
        );

        // Hide empty div's in contact forms which are used to do a clear: both; CKeditor is adding br tags to them
        // which result in 4 extra empty lines in the form
        var $forms = $('.swc_asset_swifty_contact_form > .swc_asset_cntnt' );
        $.each( $forms, function( i, formcontent ) {
            $( formcontent ) .find( 'div:not([id]):has(br) br' ).hide();
        } );

        // we need to know when the paste was used for cutting
        editor.editable().on( 'cut', function( evt ) {
            self.lastCopyPaste = 'cut';
        } );

        // we need to know when the past was used for copying
        editor.editable().on( 'copy', function( evt ) {
            self.lastCopyPaste = 'copy';
        } );

        if ( editor.contextMenu ) {
            editor.contextMenu.addListener( function( /*element, selection, path*/ ) {
                editor.contextMenu.items = $.grep( editor.contextMenu.items, function ( item /*, i*/ ) {
                    return ( item.name !== 'editdiv' && item.name !== 'removediv' );
                } );
            } );
        }

        // show tooltip when empty html or when there is a text asset without any text and when there is no asset on the 
        // page
        var $assets = editor.editable().find( '.swc_asset' );
        if( ( $assets.$.length === 0 ) &&
            ( ( $.trim( html ) === '' ) ||
              ( ( $.trim( $( editor.container.$ ).text() ) === '' ) &&  ( /swifty_text/.test(html)  ) ) )
            ) {
            evt( 'show_page_setup' );
        }

        evt( 'show_main_menu' );
    };

} );