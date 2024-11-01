define( [
    'jquery'
], function(
    $
) {
    'use strict';

    return function() {

        // mousecapture, see http://benanne.net/misc/jquery-plugins/mousecapture/demo.html
        // captures the mouse after mousedown until mouseup

        $.fn.mousecapture = function(params) {
            var $doc = $( document );

            this.each( function() {
                var $this = $( this );
                var sharedData = {};

                $this.mousedown( function( e ) {
                    // mousemove
                    var moveHandler;

                    if( params.move ) {
                        moveHandler = function( e ) {
                            params.move.call( $this, e, sharedData );
                        };

                        $doc.mousemove( moveHandler );
                    }

                    // mouseup
                    var upHandler;

                    var unbind = function() {
                        if( params.move ) {
                            $doc.unbind( 'mousemove', moveHandler );
                        }

                        $doc.unbind( 'mouseup', upHandler );
                    };

                    if( params.up ) {
                        upHandler = function( e ) {
                            unbind();
                            return params.up.call( $this, e, sharedData );
                        };
                    } else {
                        upHandler = unbind;
                    }

                    $doc.mouseup( upHandler );

                    // mousedown
                    return params.down.call( $this, e, sharedData );
                } );
            } );

            return this;
        };

    }

} );