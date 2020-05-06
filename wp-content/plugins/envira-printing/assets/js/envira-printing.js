;(function ( $, window, document ) {

	var socialElement = false,
		obj           = null,
		instance      = null,
		current       = null;

	function enviraPrintImage ( image ) {
		// Build URL
		url = envira_printing.url + '?envira_printing_image=' + image;

		// Display new window with printing dialog
		// Calculate the position of the window we'll open
		var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
		var dualScreenTop  = window.screenTop != undefined ? window.screenTop : screen.top;
		var width          = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
		var height         = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
		var left           = ((width / 2) - (32 / 2)) + dualScreenLeft;
		var top            = ((height / 2) - (32 / 2)) + dualScreenTop;

		// Open the window
		var envira_printing_window = window.open( url, 'Print', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=640, height=480, top=' + top + ', left=' + left );

	}

	// DOM ready
	$(
		function(){

			$( document ).on(
				'envirabox_api_before_show',
				function(){
					obj      = null,
					instance = null,
					current  = null;
				}
			);

			$( document ).on(
				'envirabox_api_after_show',
				function( e, object, inst, cur ){

					obj      = object,
					instance = inst,
					current  = cur;

					$( document ).on(
						'click',
						'.envirabox-container .envira-printing-button a',
						function( e ) {

							enviraPrintImage( cur.src );

							/* var	gallery_id 		= $( 'img.envirabox-image' ).data( 'envira-gallery-id' ); */

						}
					);

				}
			);

		}
	);

	$( document ).on(
		'click',
		'.envira-printing-button a',
		function( e ) {

			e.preventDefault();

			// Get image based on whether we're in the lightbox or not
			if ( $( '.envirabox-stage' ).css( 'display' ) == 'block' ) {
				// Lightbox (old code was here, no longer here)
			} else {
				// Gallery
				if ( $( this ).closest( '.envira-gallery-item-inner' ).find( 'a' ).hasClass( 'envira-gallery-video' ) ) {
					var image = $( this ).closest( '.envira-gallery-item-inner' ).find( 'a.envira-gallery-video img.envira-gallery-image' ).attr( 'src' );
				} else {
					var image = $( this ).attr( 'href' );
				}
				enviraPrintImage( image );
				/* var gallery_id = $( this ).closest( '.envira-gallery-public' ).attr( 'id' ).split( '-' )[2]; */
			}

		}
	);

	// Gallery: Show Printing Button on Image Hover
	$( document ).on(
		{
			mouseenter: function(test) {
				$( this ).find( 'div.envira-printing-button' ).show().css( 'display', 'inline-block' );
			},
			mouseleave: function(test) {
				$( this ).find( 'div.envira-printing-button' ).hide()
			}
		},
		'div.envira-gallery-item-inner'
	);

})( jQuery , window, document );

/**
 * Returns the value of the given cookie name
 *
 * @since 1.0.0
 *
 * @param 	string 	cname 	Cookie Name
 * @return 	string 			Cookie Value
 */
function envira_printing_get_cookie( cname ) {
	var name = cname + "=";
	var ca   = document.cookie.split( ';' );
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt( 0 ) == ' ') {
			c = c.substring( 1 );
		}
		if (c.indexOf( name ) == 0) {
			return c.substring( name.length,c.length );
		}
	}
	return "";
}
