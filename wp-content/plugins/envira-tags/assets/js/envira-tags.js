;(function ( $, window, document ) {

	var obj      = null,
		instance = null,
		current  = null;

		$( document ).on(
			'envira_gallery_api_enviratope',
			function( e, obj ) {

				var tags_display = obj.data.tags_display;

				$( '#envira-tags-filter-list-' + obj.id ).on(
					'click',
					'a.envira-tags-filter-link',
					function(e){
						e.preventDefault();

						// Prepare variables.
						var $this = $( this ),
						selector  = $this.attr( 'data-envira-filter' ),
						filter    = $( '#envira-tags-filter-list-' + obj.id );

						// If the item is already active, do nothing.
						if ( $this.hasClass( 'envira-tags-filter-active' ) ) {
							return;
						}

						// Do filtering.
						/* envira_container_<?php echo $data['id']; ?>.enviratope( { */
						$( '#envira-gallery-' + obj.id ).enviratope(
							{
								/* <?php do_action( 'envira_gallery_api_enviratope_config', $data ); ?> */
								filter: selector,
								itemSelector: '.envira-gallery-item',
								masonry: {
									columnWidth: '.envira-gallery-item'
								}
							}
						);

						// Reset classes properly.
						filter.find( '.envira-tags-filter-active' ).removeClass( 'envira-tags-filter-active' );
						$this.addClass( 'envira-tags-filter-active' );

						// Iterate through each gallery image, removing the rel attribute if it doesn't
						// match the chosen tag
						selector = selector.slice( 1 );
						$( '#envira-gallery-' + obj.id + ' > div.envira-gallery-item' ).each(
							function() {
								// Check if this item has the selector we want
								if ($( this ).hasClass( selector )) {
									$( 'a', $( this ) ).attr( 'rel', 'enviragallery' + obj.id );
								} else {
									$( 'a', $( this ) ).attr( 'rel', '' );
								}
							}
						);

						// If the link URL has an anchor in it, scroll to that element now
						// Because we use e.preventDefault(), this doesn't happen automatically
						if ($( this ).attr( 'href' ).indexOf( '#' ) != -1) {
							var hash = $( this ).attr( 'href' ).split( "#" )[1];
							$( 'html,body' ).animate(
								{
									scrollTop: $( '#' + hash ).offset().top
								},
								500
							);
						}
					}
				);

				if ( obj.data.album_id !== undefined && tags_display !== undefined && tags_display.length > 0 ) {
					$( '#envira-tags-filter-list-' + obj.id + ' a[data-envira-filter=".envira-category-' + tags_display + '"]' ).trigger( 'click' );
				} else {
					$( '#envira-tags-filter-list-' + obj.id + ' a[data-envira-filter=".envira-tag-' + tags_display + '"]' ).trigger( 'click' );
				}

			}
		);

})( jQuery , window, document );
