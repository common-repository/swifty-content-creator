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

        // Replace widget with html code
        // opts.html html code of new widget
        // opts.$element node of widget wrapper
        // after replacement swifty_checkImages will be run.
        can.bind.call( window, 'evt_swc_replace_asset', function( ev, opts, dfd ) {

            if(opts && opts.html && opts.$element ) {

                // select widget that will be replaced
                self.instance.getSelection().selectElement( new CKEDITOR.dom.node( opts.$element[0] ) );

                evt( 'add_asset_to_content', {
                    'html': opts.html,
                    'widget_class': opts.widget_class,
                    'insert_class': null,
                    'inline': false,
                    'takeSnapshot': true,
                    'only_insert_at_root': false,
                    'force_html_insert': false
                } ).then( function() {

                    // remove original widget
                    opts.$element.remove();

                    evt( 'add_exec', { 'fn': 'swifty_check_inserts' } );
                    setTimeout( function() {
                        evt( 'add_exec', { 'fn': 'swifty_check_inserts' } );
                    }, 1000 );
                    dfd.resolve();
                } );
            }
        } );

        can.bind.call( window, 'evt_swc_add_asset_to_content', function( ev, opts, dfd ) {
            var $newEl;

            self.destroySingle( 'tooltip' );

            self.swifty_insert_content = true;

            try {
                if( opts.takeSnapshot ) {
                    self.lastUndoImage = null;
                    self.undoImageStack = null;

                    self.instance.fire( 'saveSnapshot' );

                    if( self.instance && self.instance.undoManager ) {
                        // This should work, but returns null.
                        //self.lastUndoImage = self.instance.undoManager.getNextImage( false );
                        // Instead grab the 'image' from the snapshots stack directly.
                        if( self.instance.undoManager.snapshots.length ) {
                            self.lastUndoImage = self.instance.undoManager.snapshots[self.instance.undoManager.snapshots.length - 1]
                        }
                        // We do a few tricks here with the snapshot stack because we could not find a better way.
                        self.undoImageStack = $.extend( true, [], self.instance.undoManager.snapshots );
                    }

                    self.instance.fire( 'lockSnapshot', { dontUpdate: true } );
                }

                if( opts.$column ) {
                    // Remove empty p tags from column, there should be only asset divs here, p tag with text will be replaced
                    // with text asset on next render
                    $( opts.$column ).children( 'p' ).each( function() {
                        var $this = $( this );
                        if( $this.html().replace( /\s|&nbsp;|<br>/g, '' ).length === 0 ) {
                            $this.remove();
                        }
                    } );

                    self.appendHtmlToColumn( opts.$column, opts.html );
                } else {
                    if( ! opts.inline ) {
                        if( opts.is_preset ) {
                            var presetNode = self.appendHtmlToSelector( opts.html );

                            $newEl = $( presetNode.$ );

                            setTimeout( function() {
                                $newEl.find( '.swc_grid_row' ).addClass( 'swc_glowing' );

                                setTimeout( function() {
                                    $newEl.find( '.swc_grid_row' ).removeClass( 'swc_glowing' );
                                }, 3000 );
                            }, 1 );
                        } else {
                            var selectedEl = self.setAssetInsertPosition( opts.only_insert_at_root, opts.force_html_insert );
                            if( selectedEl ) {
                                // this does almost the same as insertHtml, but allows us to insert just before a node
                                // without the range problems
                                var widgetnode = CKEDITOR.dom.element.createFromHtml( opts.html );
                                widgetnode.insertBefore( selectedEl );
                                self.instance.widgets.initOn( widgetnode, opts.widget_class );
                            } else {
                                self.instance.insertHtml( opts.html );
                            }
                        }
                    } else {
                        self.instance.insertHtml( opts.html );
                    }
                }

                if( opts.insert_class ) {
                    var $el = $( 'body' ).find( '.' + opts.insert_class );

                    setTimeout( function() {
                        if( $el.hasClass( 'swc_asset' ) ) {
                            $el.addClass( 'swc_glowing' );
                            setTimeout( function() {
                                $el.removeClass( 'swc_glowing' );
                            }, 4000 );
                        }
                    }, 1 );

                    if( ! opts.inline ) {
                        var $td = $el.closest( 'td' );
                        var contentEl = self.getContentElement();

                        // Asset is inserted inside a table cell within the content element
                        if( $td && $td.length && $( contentEl.$ ).find( $td ).length ) {
                            var $widgetEl = $el.parent();
                            // Get child elements in table cell ( exclude the widget element )
                            var $tdChildren = $td.children().not( $widgetEl );

                            // No child elements exist AND no text inside the table cell
                            if( $tdChildren && ! $tdChildren.length && $td.text() === '' ) {
                                $widgetEl.after( '&#8203;' );   // Adds a zero-width space after the widget element
                            }
                        }
                    }
                    if( ! $el.hasClass( 'swc_text' ) ) {
                        evt(
                            'swifty_editor_asset_inserted',
                            {
                                'el': $el
                            }
                        );

                    }

                    $el.removeClass( '.' + opts.insert_class ).addClass( 'swc_asset_state_ins_updating' );
                    if( $el.hasClass( 'swc_text' ) ) {
                        // This timout is needed because the slide down effect of the previous panel (.swc_panel_animate_out)
                        // takes 400 ms (done in css - transition) to complete. After that the Text edit panel will slide up.
                        setTimeout( function() {
                            self.openEditAfterInsert( $el, opts.inline, true );
                        }, 500 );
                    }
                    self.setCursor( { '$asset': $el } );

                    $newEl = $el;
                }

                if( opts.takeSnapshot ) {
                    self.instance.fire( 'unlockSnapshot' );
                    self.instance.fire( 'saveSnapshot' );
                }

                setTimeout( function() {
                    dfd.resolve( $newEl );
                }, 100 );
            } finally {
                self.swifty_insert_content = false;
            }
        } );

        can.bind.call( window, 'evt_swc_add_row_to_content', function( ev, opts, dfd ) {

            self.destroySingle( 'tooltip' );

            var $row = null;

            self.swifty_insert_content = true;
            try {
                self.appendHtmlToSelector( opts.html );

                $row = $( 'body' ).find( '.' + opts.insert_class );

                setTimeout( function() {
                    $row.find( '.swc_grid_colwrapper' ).addClass( 'swc_glowing' );
                    setTimeout( function() {
                        $row.find( '.swc_grid_colwrapper' ).removeClass( 'swc_glowing' );
                    }, 3000 );
                }, 1 );

                $row.removeClass( '.' + opts.insert_class );
            } finally {
                self.swifty_insert_content = false;
            }
            dfd.resolve( $row );
        } );

        // Insert a new column in a row
        can.bind.call( window, 'evt_swc_add_columns_to_row', function( ev, opts, dfd ) {
            // Find the right row
            $.each( self.instance.widgets.instances, function( ii, instance ) {
                if( $( instance.element.$ ).is( opts.$row ) ) {
                    // Insert the column and make sure CKeditor initializes it as a widget
                    instance.element.appendHtml( opts.html );
                    $( instance.element.$ ).children( 'p' ).remove();
                    var count = instance.element.getChildCount();
                    for( var i = 0; i < count; i++ ) {
                        var column = instance.element.getChild( i ).findOne( '.swc_grid_column' );
                        if( column ) {
                            self.instance.widgets.initOn( column, 'swc_grid_column' );
                        }
                    }
                    dfd.resolve();
                }
            } );
        } );

        // Make sure all widgets like rows and columns are correct in CKeditor.
        // Is for instance used when a column is removed from the dom via jQuery.
        can.bind.call( window, 'evt_swc_swifty_editor_check_widgets', function( ev, opts, dfd ) {
            self.instance.widgets.checkWidgets();
            dfd.resolve();
        } );

        // is the current insertion location in a grid or table?
        can.bind.call( window, 'evt_swc_is_current_insert_position_in_table_or_grid', function( ev, opts, dfd ) {
            dfd.resolve( self.isCurrentInsertPositionInTableOrGrid() );
        } );

        // Helper for probe
        can.bind.call( window, 'evt_swc_probe_simulate', function( ev, opts ) {
            $( ( '.cke_editable:first' ) ).focus().simulate( opts.cmd, opts.data );

            self.instance.fire( 'change' );
        } );
        can.bind.call( window, 'evt_swc_probe_ck_bold', function( /*ev, opts*/ ) {
            self.instance.execCommand( 'bold' );

            self.instance.fire( 'change' );
        } );
        //can.bind.call( window, 'evt_swc_probe_ck_remove_prev_char', function( /*ev, opts*/ ) {
        //    var range = self.instance.createRange();
        //    //range.moveToElementEditEnd( self.getContentElement().find( 'p' ).getItem( 0 ) );
        //    range.moveToElementEditStart( self.getContentElement().find( 'h2' ).getItem( 0 ) );
        //    //range.moveToElementEditablePosition( self.getContentElement().find( 'h2' ).getItem( 0 ), false );
        //    self.instance.getSelection().selectRanges( [ range ] );
        //    //range.select();
        //
        //    var startNode = self.getContentElement().find( 'p' ).getItem( 0 );
        //
        //    //var txt = startNode.getText();
        //    var txt = startNode.getHtml();
        //    //txt = txt.substr( 0, txt.length - 2 );
        //    txt = txt.replace( 'Q', '' );
        //    startNode.setHtml( txt );
        //
        //    //range.select();
        //    //range.selectNodeContents( startNode );
        //    //range.deleteContents();
        //    //self.instance.insertHtml( txt );
        //
        //    self.instance.fire( 'change' );
        //} );
        can.bind.call( window, 'evt_swc_probe_ck_select_first_el', function( ev, opts ) {
            // var focusManager = new CKEDITOR.focusManager( self.instance );
            // focusManager.focus();
            self.instance.focus();

            // Find the first text node that can be edited and is not empty.

            self.instance.execCommand( 'selectAll' );
            var range = self.instance.getSelection().getRanges()[ 0 ];
            var walker = new CKEDITOR.dom.walker( range );
            var node;
            var nodeWithContent = null;
            while ( ( node = walker.next() ) ) {
                if( ! node.isReadOnly() ) {
                    var tag = $( node.$ ).prop( 'tagName' );
                    if( tag ) {
                        tag = tag.toLowerCase();
                        if( tag === 'p' || tag === 'h1' || tag === 'h2' || tag === 'h3' || tag === 'h4' || tag === 'h5' || tag === 'h6' ) {
                            if( ! nodeWithContent && $( node.$ ).text().length > 0 ) {
                                nodeWithContent = node;
                            }
                        }
                    }
                }
            }

            range = self.instance.createRange();
            range.moveToElementEditStart( nodeWithContent );
            range.selectNodeContents( nodeWithContent );
            self.instance.getSelection().selectRanges( [ range ] );

            self.instance.fire( 'change' );
         } );
    };

} );