jQuery( document ).ready(
	function( $ ) {

		/**
		* Show/hide the Password Protection Meta Box
		*
		* @since 1.0.1
		*
		* @param string visibility Post's Visibility setting
		*/
		/* var password_protection_meta_box = function( visibility ) {
		if ( visibility == 'password' ) {
			$( '#envira-password-protection' ).fadeIn();
		} else {
			$( '#envira-password-protection' ).fadeOut();
		}
		} */

		// Show/hide Password Protection options when Post Visibility is toggled
		/* if ( $( '#envira-password-protection' ).length > 0 ) {
		password_protection_meta_box ( $( 'input[name=visibility]:checked' ).val() );
		$( 'input[name=visibility]' ).on( 'change', function() {
			password_protection_meta_box( $( this ).val() );
		} );
		} */

	}
);
