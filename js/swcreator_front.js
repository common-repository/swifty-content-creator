( function( $ ) {
    $( function() {
//        console.log( "jjj", $( ".ss_item_mainmenu" ) );
        $( '.swc_item_mainmenu ul:first' ).attr( 'data-breakpoint', '639' ).addClass( 'flexnav' );  //.flexNav( {

        $( '.flexnav' ).flexNav( {
            'animationSpeed': 100,                         // default for drop down animation speed
            'transitionOpacity': true,                     // default for opacity animation
            'buttonSelector': '.swc_menu_collapse_button', // default menu button class name
            'hoverIntent': true,                           // Change to true for use with hoverIntent plugin
            'hoverIntentTimeout': 10,                      // hoverIntent default timeout
            'calcItemWidths': false,                       // dynamically calcs top level nav item widths
            'hover': true                                  // would you like hover support?
        } );
    } );

} )( jQuery );
