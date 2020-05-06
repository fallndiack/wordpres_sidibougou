;(function ( $, window, document, envira_gallery ) {

	function envira_is_mobile_zoom() {
		var isMobile = false; // initiate as false
		// device detection
		if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test( navigator.userAgent )
			|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test( navigator.userAgent.substr( 0,4 ) )) {
			isMobile = true;
		}
		return isMobile;
	}

	function envira_kill_zoom( obj ) {
		if ( $( '.zoomContainer' ).length > 0 ) {
			/* kill the elevateZoom instance */
			$( '.zoomContainer' ).remove();
			/* img.removeData('elevateZoom'); */
			$( '.envirabox-image' ).removeData( 'zoomImage' );
			$( this ).removeClass( 'btnZoomOn' ).addClass( 'btnZoomOff' ).parent().removeClass( 'zoom-on' );
			$( 'body' ).removeClass( 'envira-zoom-no-cursor' );
		}
	}

	function envira_restore_zoom( obj ) {
		if ( $( '.zoomContainer' ).length == 0 ) {
			$( this ).removeClass( 'btnZoomOff' ).addClass( 'btnZoomOn' ).parent().addClass( 'zoom-on' );
			envira_setup_zoom_vars( obj );
			$( '.zoomContainer' ).show();
			$( '.zoomContainer' ).css( 'opacity','0.5' );
			envirabox_zoom_init( obj );
			$( '.envirabox-container' ).addClass( 'envirabox-show-nav' );
			if ( obj.get_config( 'zoom_lens_cursor' ) === 0 ) {
				$( 'body' ).addClass( 'envira-zoom-no-cursor' );
			}
		}
	}

	function envira_get_zoom_window_size( size ) {
		var zoom_window_size = 0;
		switch ( size ) {
			case 'small': // or bottom right
				zoom_window_size = 100;
				break;
			case 'large': // or upper left
				zoom_window_size = 300;
				break;
			case 'x-large':
				zoom_window_size = 350;
				break;
			default: // default is medium
				zoom_window_size = 200;
				break;
		}
		return zoom_window_size;
	}

	function envira_get_zoom_position( position, wanted_value, zoom_window_size ) {
		var zoom_data = [];

		switch ( position ) {
			case 'lower-right': // or bottom right
				zoom_data['zoom_window_position'] = 4;
				zoom_data['zoom_window_offset_x'] = -(zoom_window_size);
				zoom_data['zoom_window_offset_y'] = -(zoom_window_size);
			  break;
			case 'upper-left': // or upper left
				zoom_data['zoom_window_position'] = 11;
				zoom_data['zoom_window_offset_x'] = zoom_window_size;
				zoom_data['zoom_window_offset_y'] = 0;
			  break;
			case 'lower-left':
				zoom_data['zoom_window_position'] = 9;
				zoom_data['zoom_window_offset_x'] = zoom_window_size;
				zoom_data['zoom_window_offset_y'] = 0;
			  break;
			default: // default is above or upper right
				zoom_data['zoom_window_position'] = 1;
				zoom_data['zoom_window_offset_x'] = -(zoom_window_size);
				zoom_data['zoom_window_offset_y'] = 0;
			  break;
		}
		return zoom_data[wanted_value];
	}

	$( document ).on(
		'envirabox_api_before_show',
		function( e, obj, instance, current ){

			$.mynamespace = {
				enviraObject : obj
			};

			if ( obj.get_config( 'zoom' ) === 1 && envira_is_mobile_zoom() === false ) {

				// if the array doesn't exist, create it
				if ( window.envira_zoom_settings === undefined ) {
					window.envira_zoom_settings = {};
				}

				// create global for settings and is_open
				window.envira_zoom_settings[obj.id] = {
					data: obj.data,
					zoom_settings: {
						zoom_window_height      : envira_get_zoom_window_size( obj.get_config( 'zoom_window_size' ) ),
						zoom_window_width       : envira_get_zoom_window_size( obj.get_config( 'zoom_window_size' ) ),
						zoom_window_offset_x    : envira_get_zoom_position( obj.get_config( 'zoom_position' ), 'zoom_window_offset_x', envira_get_zoom_window_size( obj.get_config( 'zoom_window_size' ) ) ),
						zoom_window_offset_y    : envira_get_zoom_position( obj.get_config( 'zoom_position' ), 'zoom_window_offset_y', envira_get_zoom_window_size( obj.get_config( 'zoom_window_size' ) ) ),
						zoom_window_position    : envira_get_zoom_position( obj.get_config( 'zoom_position' ), 'zoom_window_position', envira_get_zoom_window_size( obj.get_config( 'zoom_window_size' ) ) ),
						zoom_lens_size          : 200,
						mobile_zoom             : obj.get_config( 'mobile_zoom' ) ? true : false,
						zoom_click              : obj.get_config( 'zoom_hover' ) ? true : false,
					}
				};

				// add css class
				$( '.envirabox-wrap' ).addClass( 'envira-zoom' );

				// get wrap values
				_width_wrap  = $( ".envirabox-wrap" ).width();
				_height_wrap = $( ".envirabox-wrap" ).height();

				_width_inner  = $( ".envirabox-inner" ).width();
				_height_inner = $( ".envirabox-inner" ).height();

				_width_image  = $( ".envirabox-image" ).width();
				_height_image = $( ".envirabox-image" ).height();

				envira_setup_zoom_vars( obj );
				// envirabox_zoom_init( obj );
				if ( window.envira_zoom_settings[obj.id].zoom_settings.zoom_click === false ) {

					jQuery( 'body' ).on(
						'click',
						'#btnZoom:not(.btnZoomOff)',
						function() {
							/* kill the elevateZoom instance */
							$( '.zoomContainer' ).remove();
							jQuery( '.envirabox-image' ).removeData( 'elevateZoom' ).removeData( 'zoomImage' );
							jQuery( this ).removeClass( 'btnZoomOn' ).addClass( 'btnZoomOff' ).parent().removeClass( 'zoom-on' );
							$( 'body' ).removeClass( 'envira-zoom-no-cursor' );
						}
					);

					jQuery( 'body' ).on(
						'click',
						'#btnZoom:not(.btnZoomOn)',
						function(e) {
							e.preventDefault();
							jQuery( this ).removeClass( 'btnZoomOff' ).addClass( 'btnZoomOn' ).parent().addClass( 'zoom-on' );
							envira_setup_zoom_vars( obj );
							$( '.zoomContainer' ).show();
							envirabox_zoom_init( obj );
							if ( obj.get_config( 'zoom_lens_cursor' ) === 0 ) {
								$( 'body' ).addClass( 'envira-zoom-no-cursor' );
							}
						}
					);

				}

			}

		}
	);

	$( document ).on(
		'envirabox_api_after_show',
		function( e, obj, instance, current ){

			$.mynamespace = {
				enviraObject : obj
			};

			if ( obj.get_config( 'zoom' ) === 1 && envira_is_mobile_zoom() === false ) {

				/* legacy check */

				var classes = ['envirabox-theme-base', 'envirabox-theme-subtle', 'envirabox-theme-captioned', 'envirabox-theme-polaroid', 'envirabox-theme-showcase', 'envirabox-theme-sleek'];

				if ( $( '.envirabox-container' ).hasClass( classes[0] ) || $( '.envirabox-container' ).hasClass( classes[1] ) || $( '.envirabox-container' ).hasClass( classes[2] ) || $( '.envirabox-container' ).hasClass( classes[3] ) || $( '.envirabox-container' ).hasClass( classes[4] ) || $( '.envirabox-container' ).hasClass( classes[5] ) ) {

					 // $('.envirabox-slide--current .envirabox-navigation-inside').css('z-index', 9999).css('pointer-events', 'none');
					$( 'img.envirabox-image' ).mousemove(
						function(e) {

							if ( $( '#btnZoom' ).hasClass( 'btnZoomOff' ) ) {
								return; } /* if zoom is turned off in the toolbar, don't bother */

							var parentOffset  = $( this ).parent().offset(),
							relX              = e.pageX - parentOffset.left,
							relY              = e.pageY - parentOffset.top,
							previousArrowTop  = parseInt( $( '.envirabox-slide--current a.envirabox-arrow--left' ).css( 'top' ) ),
							previousArrowLeft = 0 , /* parseInt ( $('.envirabox-prev span').css('left') ); */
							nextArrowTop      = parseInt( $( '.envirabox-slide--current a.envirabox-arrow--right' ).css( 'top' ) ),
							nextArrowRight    = parseInt( $( '.envirabox-slide--current a.envirabox-arrow--right' ).css( 'right' ) ),
							outerWidth        = $( '.envirabox-outer' ).width(),
							currentMousePos   = { x: -1, y: -1 };

							if ( relY >= ( nextArrowTop - 80 ) && ( relY <= nextArrowTop + 80 ) ) {
								if ( relX >= ( previousArrowLeft ) && ( relX <= previousArrowLeft + 80 ) ) {
									$( '.envirabox-slide--current a.envirabox-arrow--left' ).css( 'visibility','visible' );
									envira_kill_zoom( obj );
								} else if ( relX >= ( outerWidth - nextArrowRight - 80 ) && ( relX <= outerWidth ) ) {

									$( '.envirabox-slide--current a.envirabox-arrow--right' ).css( 'visibility','visible' );
									envira_kill_zoom( obj );
								} else {
									$( '.envirabox-arrow--right .envirabox-next' ).css( 'visibility','hidden' );
									$( '.envirabox-slide--current a.envirabox-arrow--left' ).css( 'visibility','hidden' );
									envira_restore_zoom( obj );
								}

							} else {
								 $( '.envirabox-slide--current a.envirabox-arrow--right' ).css( 'visibility','hidden' );
								 $( '.envirabox-slide--current a.envirabox-arrow--left' ).css( 'visibility','hidden' );
								 envira_restore_zoom( obj );
							}

						}
					);

				} else { /* end legacy check - now should be light/dark themes */

					if ( $( '#btnZoom' ).hasClass( 'btnZoomOff' ) ) {
						return;
					} /* if zoom is turned off in the toolbar, don't bother */

					  // $('.envirabox-nav').css('z-index', 1).css('pointer-events', 'none');
					  var currentMousePos = { x: -1, y: -1 };

					$( '.envirabox-outer' ).mousemove(
						function(e) {

							var arrow_width   = $( '.envirabox-navigation a' ).width(),
							arrow_height      = $( '.envirabox-navigation a' ).width(),
							parentOffset      = $( this ).parent().offset(), /* or $(this).offset(); if you really just want the current element's offset */
							outerWidth        = $( '.envirabox-outer' ).width(),
							relX              = e.pageX - parentOffset.left,
							relY              = e.pageY - parentOffset.top,
							previousArrowTop  = parseInt( $( '.envirabox-navigation a.envirabox-prev' ).css( 'top' ) ),
							previousArrowLeft = 0 , /* parseInt ( $('.envirabox-prev span').css('left') ); */
							nextArrowTop      = parseInt( $( '.envirabox-navigation a.envirabox-next' ).css( 'top' ) ),
							nextArrowRight    = parseInt( outerWidth - $( '.envirabox-navigation a.envirabox-next' ).width() );

							$( '.envirabox-navigation' ).css( 'width', arrow_width );

							if ( relY >= ( nextArrowTop - arrow_height ) && ( relY <= nextArrowTop + arrow_height ) ) {

								if ( relX >= ( previousArrowLeft ) && ( relX <= previousArrowLeft + arrow_width ) ) {

									envira_kill_zoom( obj );

								} else if ( relX >= ( outerWidth - arrow_width ) && ( relX <= outerWidth ) ) {

									envira_kill_zoom( obj );

								} else {

									envira_restore_zoom( obj );

								}

							} else {

								envira_restore_zoom( obj );

							}

						}
					);

				}

				  /* resize wrap element */

				  var width = $( ".envirabox-wrap" ).width(),
				  height    = $( ".envirabox-wrap" ).height();

				  $( '.zoomContainer' ).remove();
				  $( '.envirabox-image' ).removeData( 'elevateZoom' ).removeData( 'zoomImage' );

				  /* init variables*/
				  envira_setup_zoom_vars( obj );

				if ( obj.get_config( 'mobile_zoom' ) == 'true' ) {

					if ( obj.get_config( 'zoom_hover' ) == 'click' ) {
						/* the zoom button exists, so we should check and see if this is 'on' before init the gallery*/

						if ( jQuery( '#btnZoom' ).hasClass( 'btnZoomOn' ) ) {
							/* if button is on, init the gallery (most likely user clicked zoom on previous photo showing)*/
							envirabox_zoom_init( obj );
						}

					} else {
						/* if the button does not exist, then it must be a zoom on hover, so init the gallery*/
						envirabox_zoom_init( obj );
					}

				}

			}

		}
	);

	$( document ).on(
		'envirabox_api_after_close',
		function( e, obj, instance, current ){

			if ( obj.get_config( 'zoom' ) === 1 && envira_is_mobile_zoom() === false ) {
				// This will effectively turn off the ElevateZoom (there is no "destroy" with this JS lib)
				$( '.zoomContainer' ).remove();
				$( '.envirabox-image' ).removeData( 'elevateZoom' ).removeData( 'zoomImage' ).elevateZoom();
			}

			$( 'body' ).removeClass( 'envira-zoom-no-cursor' );

		}
	);

	function envira_setup_zoom_vars( obj ) {

		if ( obj === undefined ) {
			if ( $.mynamespace.enviraObject !== undefined ) {
				obj = $.mynamespace.enviraObject;
			} else {
				return;
			}
			return;
		}

		var browser_width   = jQuery( window ).width(),
			offset_percent  = 1,
			max_width       = 9999,
			x_offset_offset = 2,
			y_offset_offset = -2,
			gallery_id      = obj.id;

		switch (true) {
			case ( browser_width < 400 ):
				offset_percent  = 0.50;
				max_width       = 100;
				zoom_lens_size  = 5;
				x_offset_offset = 2;
				y_offset_offset = -2;
				// if ( envira_zoom_settings.mobile_zoom_js !== '') {
					mobile_zoom = false;
				// }
				break;
			case ( browser_width > 399 && browser_width < 768):
				offset_percent  = 0.70;
				max_width       = 200;
				zoom_lens_size  = 100;
				x_offset_offset = 2;
				y_offset_offset = -2;
				// if ( envira_zoom_settings.mobile_zoom_js !== '') {
					mobile_zoom = false;
				// }
				break;
			case ( browser_width > 767 && browser_width < 1024):
				offset_percent  = 0.90;
				max_width       = 300;
				x_offset_offset = 2;
				y_offset_offset = -2;
				mobile_zoom     = 'true';
				break;
			case ( browser_width > 1023 && browser_width < 1200):
				offset_percent  = 0.90;
				max_width       = 300;
				x_offset_offset = 2;
				y_offset_offset = -2;
				mobile_zoom     = 'true';
				break;
			default:
				offset_percent  = 1;
				x_offset_offset = 2;
				y_offset_offset = -2;
				mobile_zoom     = 'true';
				break;
		}

		/* x_offset_offset is a "hack" to resolve a one-pixel shift seen at a narrow range of browser sizes in Chrome */

		window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_height   = parseInt( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_height ) * offset_percent;
		window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_width    = parseInt( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_width ) * offset_percent;
		window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_x = parseInt( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_x ) * offset_percent;
		window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_y = parseInt( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_y ) * offset_percent;

		/* Ensure Max Is Not Exceeded */

		if ( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_height > max_width ) {
			window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_height = max_width; }
		if ( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_width > max_width ) {
			window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_width = max_width; }
		if ( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_x > max_width ) {
			window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_x = max_width; }
		if ( window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_y > max_width ) {
			window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_y = max_width; }

	}

	function envirabox_zoom_init( obj ) {

		if ( obj === undefined ) {
			if ( $.mynamespace.enviraObject !== undefined ) {
				obj = $.mynamespace.enviraObject;
			} else {
				return;
			}
			return;
		}

		// var scrollZoom   = false;
		// if ( obj.get_config( 'scrollZoom' ) === 1 ) {
		// scrollZoom = true;
		// }
		var easing = false;
		if ( obj.get_config( 'easing' ) === 1 ) {
			easing = true;
		}

		/* zoom type */

		switch ( obj.get_config( 'zoom_type' ) ) {
			case 'basic':
				var zoom_type = 'window';
			  break;
			case 'mousewheel':
				var zoom_type = 'window';
			  break;
			default:
				var zoom_type = obj.get_config( 'zoom_type' );
			  break;
		}

		/* Hover or Click? */

		if ( obj.get_config( 'zoom_hover' ) === undefined || obj.get_config( 'zoom_hover' ) === 0 ) {
			var zoom_hover = 'hover';
		} else {
			var zoom_hover = 'click';
		}

		/* Mousewheel? */

		// if ( obj.get_config( 'zoom_mousewheel' ) !== undefined && obj.get_config( 'zoom_mousewheel' ) === 1 ) {
		// var scrollZoom = true;
		// } else {
		// var scrollZoom = false;
		// }
		var scrollZoom = false;

		/* Zoom Size */

		if ( obj.get_config( 'zoom_size' ) !== undefined ) {
			var zoomSize = obj.get_config( 'zoom_size' );
		} else {
			var zoomSize = 200;
		}

		/* Lens */

		switch ( obj.get_config( 'zoom_lens_shape' ) ) {
			case 'square':
				var zoom_lens_shape = 'square';
				break;
			default: // default is medium
				var zoom_lens_shape = 'round';
				break;
		}

		/* lens fade and easing */

		var lensFadeIn     = 0;
		var lensFadeOut    = 0;
		var easingDuration = 2000;

		if ( obj.get_config( 'zoom_effect' ) !== undefined && obj.get_config( 'zoom_effect' ) == 'easing' ) {
			var easing = 1;
		} else {
			var easing = 0;
		}
		if ( obj.get_config( 'zoom_effect' ) !== undefined && obj.get_config( 'zoom_effect' ) == 'fade-in' ) {
			lensFadeIn = 1000;
			lensFadeIn = 10;
		}
		if ( obj.get_config( 'zoom_effect' ) !== undefined && obj.get_config( 'zoom_effect' ) == 'fade-out' ) {
			lensFadeIn = 10;
			lensFadeIn = 1000;
		}

		var args = {
			responsive          : false,
			zoomType            : zoom_type,
			lensSize            : zoomSize,
			containLensZoom     : true,
			scrollZoom          : scrollZoom,
			tint                : obj.get_config( 'zoom_tint_color' ),
			tintColour          : '#' + obj.get_config( 'zoom_tint_color' ),
			tintOpacity         : obj.get_config( 'zoom_tint_color_opacity' ) * .01,
			zoomWindowPosition  : window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_position,
			zoomWindowHeight    : window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_height,
			zoomWindowWidth     : window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_width,
			borderSize          : 1,
			easing              : easing,
			easingDuration      : easingDuration,
			lensFadeOut         : lensFadeOut,
			lensFadeIn          : lensFadeIn,
			zoomWindowOffetx    : window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_x,
			zoomWindowOffety    : window.envira_zoom_settings[obj.id].zoom_settings.zoom_window_offset_y,
			lensShape           : zoom_lens_shape,
		};

		$( 'div.envirabox-container .envirabox-slide--current .envirabox-image' ).elevateZoom( args );

	} /* envirabox_zoom_init */

	$( window ).resize(
		function() {

			if ( $( '.zoomContainer' ).length ) {
				/* kill it */
				$( '.zoomContainer' ).remove();
				$( '.envirabox-image' ).removeData( 'elevateZoom' ).removeData( 'zoomImage' );
				envira_setup_zoom_vars();
				envirabox_zoom_init();
			}

		}
	);

	$( document ).on(
		'onFullscreenChange',
		function( e, isFullscreen ){

			/* kill the elevateZoom instance */
			var obj = $.mynamespace.enviraObject;
			$( '.zoomContainer' ).remove();
			$( '.envirabox-image' ).removeData( 'elevateZoom' ).removeData( 'zoomImage' );
			$( '#btnZoom' ).removeClass( 'btnZoomOn' ).addClass( 'btnZoomOff' ).parent().removeClass( 'zoom-on' );

		}
	);

})( jQuery , window, document, envira_gallery );
