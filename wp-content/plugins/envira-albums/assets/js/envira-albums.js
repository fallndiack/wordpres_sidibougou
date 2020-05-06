import Envira_Albums from './albums-init.js';

var envira_albums = window.envira_albums || {};

;(function ( $, window, document, Envira_Albums, envira_gallery, envira_albums ) {

	$(
		function() {

			window.envira_albums = envira_albums;

			$( document ).on(
				'envira_load',
				function(e){

					e.stopPropagation();
					envira_albums = {};

					$( '.envira-album-public' ).each(
						function() {

							var $this        = $( this ),
							$envira_albums   = $this.data( 'album-config' ),
							$envira_images   = $this.data( 'album-galleries' ),
							$envira_lightbox = $this.data( 'lightbox-theme' );

							if ( envira_albums[ $envira_albums[ 'album_id' ] ] === undefined ) {
								envira_albums[ $envira_albums[ 'album_id' ] ] = new Envira_Albums( $envira_albums, $envira_images, $envira_lightbox );
							}

						}
					);

				}
			);

			$( document ).trigger( 'envira_load' );

			/* setup lazy load event */
			$( document ).on(
				"envira_image_lazy_load_complete",
				function( event ) {

					if ( event !== undefined && event.image_id !== undefined && event.image_id !== null ) {

						let envira_container = $( '#envira-gallery-item-' + event.gallery_id ).find( 'img#' + event.image_id );

						if ( $( '#envira-gallery-item-' + event.gallery_id ).find( 'div.envira-album-public' ).hasClass( 'envira-gallery-0-columns' ) ) {

							/* this is an automatic gallery */
							$( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( 'div.envira-gallery-position-overlay' ).delay( 100 ).show();

						} else {

							/* this is a legacy gallery */
							$( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( 'div.envira-gallery-position-overlay' ).delay( 100 ).show();

							/* re-do the padding bottom */
							/* $padding_bottom = ( $output_height / $output_width ) * 100; */

							var envira_lazy_width = $( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( '.envira-lazy' ).width();
							var ratio1            = ( event.naturalHeight / event.naturalWidth );
							var ratio2            = ( event.naturalHeight / envira_lazy_width );

							if ( ratio2 < ratio1 ) {
								var ratio = ratio2;
							} else {
								var ratio = ratio1;
							}

							var padding_bottom = ratio * 100;
							if ( envira_container.closest( 'div.envira-album-public' ).parent().hasClass( 'envira-gallery-theme-sleek' ) ) {
								// add additional padding for this theme
								padding_bottom = padding_bottom + 2;
							}

							var div_envira_lazy = $( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( 'div.envira-lazy' );
							var caption_height  = div_envira_lazy.closest( 'div.envira-gallery-item-inner' ).find( '.envira-album-title' ).height();
							if ( $( envira_container ).closest( 'div.envira-gallery-item' ).hasClass( 'enviratope-item' ) ) {
								div_envira_lazy.css( 'padding-bottom', padding_bottom + '%' ).attr( 'data-envira-changed', 'true' );
								var div_overlay = $( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( '.envira-gallery-position-overlay.envira-gallery-bottom-right' );
								div_overlay.css( 'bottom', caption_height );
								div_overlay = $( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( '.envira-gallery-position-overlay.envira-gallery-bottom-left' );
								div_overlay.css( 'bottom', caption_height );
							} else {
								div_envira_lazy.css( 'height', 'auto' ).css( 'padding-bottom', '10px' ).attr( 'data-envira-changed', 'true' );
								var div_overlay = $( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( '.envira-gallery-position-overlay.envira-gallery-bottom-right' );
								div_overlay.css( 'bottom', caption_height + 10 );
								div_overlay = $( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( '.envira-gallery-position-overlay.envira-gallery-bottom-left' );
								div_overlay.css( 'bottom', caption_height + 10 );
							}

							// div_envira_lazy.addClass('changed');
							$( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( 'span.envira-title' ).delay( 1000 ).css( 'visibility', 'visible' );
							$( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( 'span.envira-caption' ).delay( 1000 ).css( 'visibility', 'visible' );

							if ( window["envira_container_" + event.gallery_id] !== undefined ) {

								if ( $( '#envira-gallery-' + event.gallery_id ).hasClass( 'enviratope' ) ) {

									window["envira_container_" + event.gallery_id].on(
										'layoutComplete',
										function( event, laidOutItems ) {

											$( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( 'span.envira-title' ).delay( 1000 ).css( 'visibility', 'visible' );
											$( envira_container ).closest( 'div.envira-gallery-item-inner' ).find( 'span.envira-caption' ).delay( 1000 ).css( 'visibility', 'visible' );

										}
									);

								} else {

								}

							}

						}

					}
				}
			);

		}
	);

})( jQuery , window, document, Envira_Albums, envira_gallery, envira_albums );
