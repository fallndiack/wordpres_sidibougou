;(function ( $, window, document ) {

	var socialElement       = false,
		obj                 = null,
		instance            = null,
		current             = null,
		before_clickContent = null,
		before_clickSlide   = null,
		before_clickOutside = null;

	$( document ).on(
		'envirabox_api_after_show',
		function( e, object, inst, cur ) {

			current             = cur;
			before_clickContent = current.opts.clickContent,
			before_clickSlide   = current.opts.clickSlide,
			before_clickOutside = current.opts.clickOutside;

			var obj  = object,
			instance = inst,
			slide    = current,
			$slide   = current.$slide;

			/**
			* Prevent right click on Envira Images
			*/
			$( document ).on(
				'contextmenu dragstart',
				'.envirabox-wrap, .envira-gallery-image, .envirabox-image, #envirabox-thumbs img, .envirabox-nav, .envira-gallery-item .caption, .envira-video-play-icon, .envirabox-inner, .envira-title, .envira-caption, .envira-gallery-position-overlay, .envira-download-button, .envira-printing-button, .envira-social-buttons, .envira-album-title, .envira-album-caption, .envira-album-image-count',
				function( e ) {
					e.preventDefault();
					if ( ( typeof envira_gallery_protection !== 'undefined' && envira_gallery_protection !== null ) && envira_gallery_protection.message !== undefined && envira_gallery_protection.message.replace( / /g,'' ) != '' ) {
						// have to set these to false so the click to trigger copyright message doesn't close the current lightbox
						current.opts.clickContent = false;
						current.opts.clickSlide   = false;
						current.opts.clickOutside = false;
						show_copyright_message( envira_gallery_protection.title, envira_gallery_protection.message, envira_gallery_protection.button_text, before_clickContent, before_clickSlide, before_clickOutside );
					}
					return false;
				}
			);

		}
	);

	/**
	* Prevent right click on Envira Images
	*/
	$( document ).on(
		'contextmenu dragstart',
		'.envirabox-wrap, .envira-gallery-image, .envirabox-image, #envirabox-thumbs img, .envirabox-nav, .envira-gallery-item .caption, .envira-video-play-icon, .envirabox-inner, .envira-title, .envira-caption, .envira-gallery-position-overlay, .envira-download-button, .envira-printing-button, .envira-social-buttons, .envira-album-title, .envira-album-caption, .envira-album-image-count',
		function( e ) {
			e.preventDefault();
			if ( ( typeof envira_gallery_protection !== 'undefined' && envira_gallery_protection !== null ) && envira_gallery_protection.message !== undefined && envira_gallery_protection.message.replace( / /g,'' ) != '' ) {
				show_copyright_message( envira_gallery_protection.title, envira_gallery_protection.message, envira_gallery_protection.button_text );
			}
			return false;
		}
	);

	/**
	* Prevent dragging on Envira Images
	*/
	$( 'img.envira-gallery-image' ).on(
		'dragstart',
		function() {
			if ( envira_gallery_protection !== undefined && envira_gallery_protection.message !== undefined && envira_gallery_protection.message.replace( / /g,'' ) != '' ) {
				show_copyright_message( envira_gallery_protection.title, envira_gallery_protection.message, envira_gallery_protection.button_text );
			}
			return false;
		}
	);

	/**
	 * Monitor which keys are being pressed
	 */
	var envira_protection_keys = {
		'alt': false,
		'shift': false,
		'meta': false,
	};
	$( document ).on(
		'keydown',
		function( e ) {

			// Alt Key Pressed
			if ( e.altKey ) {
				envira_protection_keys.alt = true;
			}

			// Shift Key Pressed
			if ( e.shiftKey ) {
				envira_protection_keys.shift = true;
			}

			// Meta Key Pressed (e.g. Mac Cmd)
			if ( e.metaKey ) {
				envira_protection_keys.meta = true;
			}

		}
	);
	$( document ).on(
		'keyup',
		function( e ) {

			// Alt Key Released
			if ( ! e.altKey ) {
				envira_protection_keys.alt = false;
			}

			// Shift Key Released
			if ( e.shiftKey ) {
				envira_protection_keys.shift = false;
			}

			// Meta Key Released (e.g. Mac Cmd)
			if ( ! e.metaKey ) {
				envira_protection_keys.meta = false;
			}

		}
	);

	/**
	* Prevent automatic download when Alt + left click
	*/
	$( document ).on(
		'click',
		'.envira-gallery-image, .envirabox-image, #envirabox-thumbs img, .envirabox-nav, .envira-gallery-item .caption',
		function( e ) {

			if ( envira_protection_keys.alt || envira_protection_keys.shift || envira_protection_keys.meta ) {
				// User is trying to download - stop!
				e.preventDefault();
				if ( envira_gallery_protection !== undefined && envira_gallery_protection.message !== undefined && envira_gallery_protection.message.replace( / /g,'' ) != '' ) {
					show_copyright_message( envira_gallery_protection.title, envira_gallery_protection.message, envira_gallery_protection.button_text );
				}
			}

		}
	);

	/**
	* Prevent iOS 'force touch' if we can
	*/
	window.addEventListener(
		'touchforcechange',
		function(event) {
			var force = event.changedTouches[0].force;
			var id    = event.changedTouches[0].target.id;

			if ($( '#' + id ).hasClass( 'envira-gallery-image' ) && force > 0.1) {
				event.preventDefault();
				// display custom message, if we can
				if ( envira_gallery_protection !== undefined && envira_gallery_protection.message !== undefined && envira_gallery_protection.message.replace( / /g,'' ) != '' ) {
					alert( envira_gallery_protection.message );
				} else {
					alert( 'This item cannot be copied or shared.' );
				}
			}
		}
	);

	function show_copyright_message( title, message, button_text ) {

		var title        = ( title != undefined && title.replace( / /g,'' ) != '' ) ? title : '' ,
			message_text = ( message != undefined && message.replace( / /g,'' ) != '' ) ? message : '' ,
			button_text  = ( button_text != undefined && button_text.replace( / /g,'' ) != '' ) ? button_text : 'Ok';

		$.alertMessage(
			{
				title: title,
				message_text: message_text,
				button_text: button_text,
				before_clickContent: before_clickContent,
				before_clickSlide: before_clickSlide,
				before_clickOutside: before_clickOutside,
			}
		);

	}

	$.alertMessage = function ( opts ) {

		var conditional_css = '';

		// check and see if envirabox already exists, and if so add some CSS to make the modal alert appear on top of the lightbox
		if ( $( '.envirabox-container' ).length > 0 ) {
			conditional_css += 'envira-alert-in-lightbox';
		}

		if ( $( '.envirabox-alert-content' ).length > 0 ) {
			return;
		}

		$.envirabox.open(
			{
				type: 'html',
				src: '<div class="envirabox-alert-content">' +
					'<h2 class="envira-alert-title">' + opts.title + '</h2>' +
					'<p class="envira-alert-text">' + opts.message_text + '</p>' +
					'<p class="envira-alert-message">' +
					'<a href="javascript:void(0)" data-value="1" data-envirabox-close class="envira-alert-button">' + opts.button_text + '</a>' +
					'</p>' +
					'</div>',
				opts: {
					afterClose: function( instance, slide ) {
						if ( current !== undefined && before_clickContent !== null && before_clickSlide !== null && before_clickOutside !== null ) {
							current.opts.clickContent = before_clickContent;
							current.opts.clickSlide   = before_clickSlide;
							current.opts.clickOutside = before_clickOutside;
						}
					},
					animationDuration: 265,
					animationEffect: 'material',
					modal: true,
					baseTpl: '<div id="envirbox-alert" class="envirabox-container envirabox-alert ' + conditional_css + '" tabindex="-1" role="dialog">' +
						'<div class="envirabox-bg"></div>' +
						'<div class="envirabox-inner">' +
						'<div class="envirabox-stage"></div>' +
						'</div>' +
						'</div>'
				}
			}
		);
	}

})( jQuery , window, document );
