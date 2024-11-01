define( [
    'jquery'
], function(
    $
) {
    'use strict';

    return function( self, ckDragHandlerImage, $dragHandlerImage, allowDrag ) {
        // var clicking = false;
        // var move = 0;
        //
        // // Remove all the event listeners on this element.
        //
        // ckDragHandlerImage.removeAllListeners();
        //
        // $dragHandlerImage.on( 'mousedown', function( /*ev*/ ) {
        //     clicking = true;
        // } );
        //
        // $dragHandlerImage.on( 'mouseup', function( /*ev*/ ) {
        //     move = 0;
        // } );
        //
        // $( document ).on( 'mouseup', function( /*ev*/ ) {
        //     clicking = false;
        // } );
        //
        // $dragHandlerImage.on( 'mousemove', mouseMoveHandler );
        //
        // function mouseMoveHandler() {
        //     if( clicking === false ) {
        //         return;
        //     }
        //
        //     move++;
        //
        //     if( move > 5 && allowDrag === 1 ) {
        //
        //         // before drag drop we make columns editable
        //         $( '.swc_grid_column' ).attr( 'contenteditable', 'true' );
        //
        //         onBlockWidgetDrag();
        //         $dragHandlerImage.off( 'mousemove' );
        //         move = 0;
        //     }
        // }
        //
        // // We need to make sure there is something in the column after dragging an asset away.
        // // Check if asset will be dragged outside the current column, if so make sure there is something left in it
        // self.checkEmptyColumn = function( target ) {
        //     var self = this;
        //     var $sourceColumns = $( self.element.$ ).closest( '.swc_grid_column' );
        //     if( $sourceColumns.length === 1 ) {
        //         // Do we need to check if sourceColumn will be empty after drop?
        //         var $targetColumns = $( target.$ ).closest( '.swc_grid_column' );
        //         if( ( $targetColumns.length === 0 ) || ( $sourceColumns[0] !== $targetColumns[0] ) ) {
        //             if( $sourceColumns[0].childElementCount === 1 ) {
        //                 var $ptag = $( '<p><br><p>' );
        //                 $sourceColumns.append( $ptag );
        //             }
        //         }
        //     }
        // };
        //
        // // Copied onBlockWidgetDrag from CKEditor / widget / plugin.js.
        // // We need to override the mousedown event on the draghandler.
        // // Changed: 'this' -> 'self'
        // // Added: var sender = ( evt && evt.sender ) ? evt.sender : ckDragHandlerImage;
        // // Changed: evt.sender -> sender
        // // Added: relations filter by using relations2
        // function onBlockWidgetDrag( evt ) {
        //     // Swifty code: We use onBlockWidgetDrag in mouseMoveHandler above without the evt parameter.
        //     var sender = ( evt && evt.sender ) ? evt.sender : ckDragHandlerImage;
        //
        //     var finder = self.repository.finder,
        //         locator = self.repository.locator,
        //         liner = self.repository.liner,
        //         editor = self.editor,
        //         editable = editor.editable(),
        //         listeners = [],
        //         sorted = [];
        //
        //     // Mark dragged widget for repository#finder.
        //     self.repository._.draggedWidget = self;
        //
        //     // Harvest all possible relations and display some closest.
        //     var relations2 = finder.greedySearch(),
        //         relations = {};
        //
        //     // Ignore divs grid system
        //     $.each( relations2, function( index, item ) {
        //         var add = true;
        //
        //         // no drag into text asset
        //         var $text_asset = $( item.element.$ ).parents( '.swc_text' );
        //         if( $text_asset.length > 0 ) {
        //             add = false;
        //         } else {
        //             // only drag to columns when in grid, column should be a direct parent
        //             var $column = $( item.element.$ ).closest( '.swc_grid_row' );
        //             if( $column.length > 0 ) {
        //                 if( ! $( item.element.$.parentElement ).hasClass( 'swc_grid_column' ) ) {
        //                     add = false;
        //                 }
        //             }
        //         }
        //
        //         if( add ) {
        //             relations[ index ] = item;
        //         }
        //     } );
        //
        //     var buffer = CKEDITOR.tools.eventsBuffer( 50, function() {
        //             locations = locator.locate( relations );
        //
        //             // There's only a single line displayed for D&D.
        //             sorted = locator.sort( y, 1 );
        //
        //             if( sorted.length ) {
        //                 liner.prepare( relations, locations );
        //                 liner.placeLine( sorted[0] );
        //                 liner.cleanup();
        //             }
        //         } ),
        //
        //         locations, y;
        //
        //     // Let's have the "dragging cursor" over entire editable.
        //     editable.addClass( 'cke_widget_dragging' );
        //
        //     // Cache mouse position so it is re-used in events buffer.
        //     listeners.push( editable.on( 'mousemove', function( evt ) {
        //         y = evt.data.$.clientY;
        //         buffer.input();
        //     } ) );
        //
        //     // Fire drag start as it happens during the native D&D.
        //     //editor.fire( 'dragstart', { target: evt.sender } );
        //     editor.fire( 'dragstart', { target: sender } );
        //
        //     function onMouseUp() {
        //         var l;
        //
        //         buffer.reset();
        //
        //         // Stop observing events.
        //         while( ( l = listeners.pop() ) ) {
        //             l.removeListener();
        //         }
        //
        //         //onBlockWidgetDrop.call( self, sorted, evt.sender );
        //         onBlockWidgetDrop.call( self, sorted, sender );
        //     }
        //
        //     // Mouseup means "drop". This is when the widget is being detached
        //     // from DOM and placed at range determined by the line (location).
        //     listeners.push( editor.document.once( 'mouseup', onMouseUp, self ) );
        //
        //     // Prevent calling 'onBlockWidgetDrop' twice in the inline editor.
        //     // `removeListener` does not work if it is called at the same time event is fired.
        //     if ( !editable.isInline() ) {
        //         // Mouseup may occur when user hovers the line, which belongs to
        //         // the outer document. This is, of course, a valid listener too.
        //         listeners.push( CKEDITOR.document.once( 'mouseup', onMouseUp, self ) );
        //     }
        // }
        //
        // // Copied onBlockWidgetDrop from CKEditor / widget / plugin.js.
        // // We need to override the mousedown event on the draghandler.
        // // Changed: 'this' into 'self'
        // // Added: $dragHandlerImage.on( 'mousemove', mouseMoveHandler );
        // // Added: checkEmptyColumn call
        // function onBlockWidgetDrop( sorted, dragTarget ) {
        //     var finder = self.repository.finder,
        //         liner = self.repository.liner,
        //         editor = self.editor,
        //         editable = self.editor.editable();
        //
        //     if( !CKEDITOR.tools.isEmpty( liner.visible ) ) {
        //         // Retrieve range for the closest location.
        //         var dropRange = finder.getRange( sorted[0] );
        //
        //         // Focus widget (it could lost focus after mousedown+mouseup)
        //         // and save this state as the one where we want to be taken back when undoing.
        //         self.focus();
        //
        //         // Swifty code: test for empty column
        //         self.checkEmptyColumn( dropRange.startContainer );
        //
        //         // Drag range will be set in the drop listener.
        //         editor.fire( 'drop', {
        //             dropRange: dropRange,
        //             target: dropRange.startContainer
        //         } );
        //     }
        //
        //     // Clean-up custom cursor for editable.
        //     editable.removeClass( 'cke_widget_dragging' );
        //
        //     // Clean-up all remaining lines.
        //     liner.hideVisible();
        //
        //     // Clean-up drag & drop.
        //     editor.fire( 'dragend', { target: dragTarget } );
        //
        //     // Swifty code: attach the mousemove event handler again.
        //     $dragHandlerImage.on( 'mousemove', mouseMoveHandler );
        // }
    }

} );