jQuery( document ).ready(
	function( $ ) {

			/******* LIGHTBOX *********/
			$( document ).on(
				'envirabox_api_after_show',
				function( e, obj, instance, current ){

					if ( obj.get_config( 'fullscreen' ) === false ) {
						 return;
					}

					var envira_lb_image    = $( '.envirabox-slide--current img.envirabox-image' ).attr( 'src' ),
					envira_gallery_item_id = false;

				}
			);

	}
);
