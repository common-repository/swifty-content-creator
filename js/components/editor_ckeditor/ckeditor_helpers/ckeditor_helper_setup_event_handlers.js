define( [
    'jquery',
    'swiftylib/evt',
    'swiftylib/compute',
    './ckeditor_helper_link_type'
], function(
    $, evt, Compute, editorLinkType
) {
    'use strict';

    return function() {
        var self = this;
        var instance = self.instance;

        self.lastCopyPaste = null;

        // This fix will prevent multiple pastes when CTRL+V is hold down a bit longer.
        self.pasteOccured = new Compute( null );
        self.pasteOccured.bind( 'change', function( ev, newVal, oldVal ) {
            if( oldVal && newVal && oldVal.name === 'paste' && newVal.name === 'paste' ) {
                newVal.stop();
                // Reset after 1 second. For cases where the keyup might not be triggerd correctly.
                // Some people were not able to do multiple ctrl-v at all.
                setTimeout( function() {
                    self.pasteOccured( null );
                }, 1000 );
            }
        } );

        // When paste Dialog is shown we will allow the next paste.
        instance.on( 'pasteDialog', function( evt ) {
            self.pasteOccured( null );
        } );

        instance.on( 'pluginsLoaded', self.onPluginsLoadedHandler, self );
        instance.on( 'contentDom', self.onContentDomHandler, self );
        instance.on( 'instanceReady', self.onInstanceReadyHandler, self );

        instance.on( 'setData', function( ev ) {
            if( $.trim( ev.data.dataValue ) === '' ) {
                ev.data.dataValue = '<p></p>';
            }
        } );

        instance.on( 'insertElement', function( ev ) {
            var elName = ev.data.getName();
            var rows, row, nrOfRows, cols, col, nrOfCols, colWidth;

            if( elName.toLowerCase() === 'table' ) {
                rows = ev.data.find( 'tbody' ).getItem( 0 ).getChildren();   // Is a nodeList of table rows
                nrOfRows = rows.count();
                nrOfCols = rows.getItem( 0 ).getChildren().count();
                colWidth = Math.floor( ( 100 / nrOfCols ) * 10000 ) / 10000;   // 4 decimals

                for( var i = 0; i < nrOfRows; i++ ) {
                    row = rows.getItem( i );
                    cols = row.getChildren();   // Is a nodeList of table columns

                    for( var j = 0; j < nrOfCols; j++ ) {
                        col = cols.getItem( j );
                        col.setAttribute( 'width', colWidth + '%' );
                    }
                }

            }
        }, null, null, 1 );   // Priority < 10 gets called before insertion.

        instance.on( 'selectionChange', function( /*ev*/ ) {
            self.unlockSelection();
        } );

        /**
         * Check for widget in paste, prevent pasting into widgets
         */
        instance.on( 'paste', function( evt ) {
            var data = evt.data,
                // Prevent XSS attacks.
                tempDoc = document.implementation.createHTMLDocument( '' ),
                temp = new CKEDITOR.dom.element( tempDoc.body ),
                spans;

            // Without this isReadOnly will not work properly.
            temp.data( 'cke-editable', 1 );

            temp.appendHtml( data.dataValue );

            // only when there is a widget in the paste data
            var widgetInData = temp.find( 'div.cke_widget_wrapper' );
            if( widgetInData.count() > 0 ) {
                // remove drag span uit
                spans = temp.find( 'span.cke_widget_drag_handler_container' );
                $( spans.$ ).remove();

                // this is also called with drag drop, but then the insert position is handled differently
                if( ( ( data.method !== 'drop' ) && self.isCurrentInsertPositionInWidget() ) ) {
                    evt.stop(); // we don't let editor to paste data
                }

                // when we have just cutted the date then we know there is no issue with duplicates, otherwise create
                // new ids
                if( self.lastCopyPaste !== 'cut' ) {

                    self.replaceIds( temp );
                }
                data.dataValue = temp.getHtml();
            }

            self.pasteOccured( evt );
        } );

        instance.on( 'afterPaste', function() {
            // after drag drop we make columns readonly again
            $( '.swc_grid_column' ).attr( 'contenteditable', 'false' );
        } );

        CKEDITOR.on( 'dialogDefinition', function( ev ) {
            if( ev.editor.name === instance.name ) {
                var definition = ev.data.definition;
                var infoTab = definition.getContents( 'info' );

                if( ev.data.name === 'table' ) {
                    infoTab.get( 'txtWidth' )['default'] = '100%';   // Set default width to 100%
                }

                if( ev.data.name === 'link' ) {
                    var linkTypeSelect = infoTab.get( 'linkType' );   // The link type select box.

                    // Add the UI that is shown when the user selects our new link type option from the select box.
                    editorLinkType.insertNewLinkType( linkTypeSelect, infoTab );

                    // Show or hide our controls, when the user picks a new link type in the select box.
                    editorLinkType.linkTypeSelectOnChange( linkTypeSelect, ev.editor );

                    // When the link type select box is initialized with a value.
                    editorLinkType.linkTypeSelectSetup( linkTypeSelect );

                    // When the link type select box is supposed to save its value.
                    editorLinkType.linkTypeSelectCommit( linkTypeSelect );
                }
            }
        } );

        // Prevent CTRL+A select all outside of text assets. This solution should work on all browsers.
        $( window ).bind( 'keydown', function( event ) {
            if( event.ctrlKey || event.metaKey ) {
                switch( String.fromCharCode( event.which ).toLowerCase() ) {
                    case 'a':
                        event.preventDefault();
                        break;
                }
            }
        } );
    };

} );