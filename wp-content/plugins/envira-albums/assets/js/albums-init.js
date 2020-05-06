var enviraLazy = window.enviraLazy;

class Envira_Album{

	constructor( config, galleries, envirabox_config ){

		var self = this;

		// Setup our Vars
		self.data             = config;
		self.galleries        = galleries;
		self.id               = this.get_config( 'album_id' );
		self.envirabox_config = envirabox_config;

		// Log if ENVIRA_DEBUG enabled
		self.log( self.data );
		self.log( self.galleries );
		self.log( self.envirabox_config );
		self.log( self.id );

		// self init
		self.init();
	}
	init() {

		var self = this;

		// Justified Gallery Setup
		if (self.get_config( 'columns' ) == 0) {

			self.justified();

			if (self.get_config( 'lazy_loading' )) {

				$( document ).on(
					'envira_pagination_ajax_load_completed',
					function() {

						$( '#envira-gallery-' + self.id ).on(
							'jg.complete',
							function(e) {

								e.preventDefault();

								self.load_images();

							}
						);

					}
				);

				self.load_images();

			}

			if (self.get_config( 'justified_gallery_theme' )) {

				// self.overlay_themes();
			}

			$( document ).trigger( 'envira_gallery_api_justified', self.data );

		}

		// Lazy loading setup
		if ( self.get_config( 'lazy_loading' ) ) {

			self.load_images();

			$( window ).scroll(
				function(e) {

					self.load_images();

				}
			);

		}

		// Enviratope Setup
		if ( parseInt( self.get_config( 'columns' ) ) > 0 && self.get_config( 'isotope' ) ) {

			self.enviratopes();
			// Lazy loading setup
			if (self.get_config( 'lazy_loading' )) {

				$( '#envira-gallery-' + self.id ).one(
					'layoutComplete',
					function(e, laidOutItems) {

						self.load_images();

					}
				);

			}
		} else if (  parseInt( self.get_config( 'columns' ) ) > 0) {

			self.load_images();

		}

		// Lightbox setup
		if (self.get_config( 'lightbox_enabled' ) || self.get_config( 'lightbox' ) ) {

			self.lightbox();

		}

		$( document ).trigger( 'envira_gallery_api_init', self );

	}

	/**
	 * LazyLoading
	 *
	 * @since 1.7.1
	 */
	load_images() {

		var self = this;

		self.log( 'running: ' + '#envira-gallery-' + self.id );

		enviraLazy.run( '#envira-gallery-' + self.id );

		if ( $( '#envira-gallery-' + self.id ).hasClass( 'enviratope' ) ) {

			$( '#envira-gallery-' + self.id ).enviraImagesLoaded()
				.done(
					function() {
						$( '#envira-gallery-' + self.id ).enviratope( 'layout' );
					}
				)
				.progress(
					function() {
						$( '#envira-gallery-' + self.id ).enviratope( 'layout' );
					}
				);

		}
	}

	/**
	 * Outputs the gallery init script in the footer.
	 *
	 * @since 1.7.1
	 */
	justified() {

		var self = this;

		$( '#envira-gallery-' + this.id ).enviraJustifiedGallery(
			{
				rowHeight: self.is_mobile() ? this.get_config('mobile_justified_row_height') : this.get_config('justified_row_height'),
				maxRowHeight: -1,
				waitThumbnailsLoad: true,
				selector: '> div > div',
				lastRow: this.get_config( 'justified_last_row' ),
				border: 0,
				margins: this.get_config( 'justified_margins' ),

			}
		);

		$( document ).trigger( 'envira_gallery_api_start_justified', self.data );

		$( '#envira-gallery-' + this.id ).css( 'opacity', '1' );

	}
	justified_norewind() {

		$( '#envira-gallery-' + self.id ).enviraJustifiedGallery( 'norewind' );

	}
	/**
	 * Outputs the gallery init script in the footer.
	 *
	 * @since 1.7.1
	 */
	enviratopes() {

			var self = this;

			var envira_isotopes_config = {

				itemSelector: '.envira-gallery-item',
				masonry: {
					columnWidth: '.envira-gallery-item'
				}

		};
			$( document ).trigger( 'envira_gallery_api_enviratope_config', envira_isotopes_config );

			// Initialize Isotope
			$( '#envira-gallery-' + self.id ).enviratope( envira_isotopes_config );
			// Re-layout Isotope when each image loads
			$( '#envira-gallery-' + self.id ).enviraImagesLoaded()
				.done(
					function() {
						$( '#envira-gallery-' + self.id ).enviratope( 'layout' );
					}
				)
				.progress(
					function() {
						$( '#envira-gallery-' + self.id ).enviratope( 'layout' );
					}
				);

			$( document ).trigger( 'envira_gallery_api_enviratope', self );

	}
	lightbox(){

		var self                  = this,
			thumbs                = self.get_config( 'thumbnails' ) ? { autoStart : true, hideOnClose : true, position : self.get_lightbox_config( 'thumbs_position' ) } : false,
			slideshow             = self.get_config( 'slideshow' ) ? { autoStart : self.get_config( 'autoplay' ),speed : self.get_config( 'ss_speed' ) } : false,
			fullscreen            = self.get_config( 'fullscreen' ) && self.get_config( 'open_fullscreen' ) ? { autoStart : true } : true,
			animationEffect       = self.get_config( 'lightbox_open_close_effect' ) == 'zomm-in-out' ? 'zoom-in-out' : self.get_config( 'lightbox_open_close_effect' ),
			transitionEffect      = self.get_config( 'effect' ) == 'zomm-in-out' ? 'zoom' : self.get_config( 'effect' ),
			lightbox_images       = [];
			self.lightbox_options = {
				selector:           '[data-envirabox="' + self.id + '"]',
				loop:               self.get_config( 'loop' ), // Enable infinite gallery navigation
				margin:             self.get_lightbox_config( 'margins' ), // Space around image, ignored if zoomed-in or viewport width is smaller than 800px
				gutter:             self.get_lightbox_config( 'gutter' ), // Horizontal space between slides
				keyboard:           self.get_config( 'keyboard' ), // Enable keyboard navigation
				arrows:             self.get_lightbox_config( 'arrows' ), // Should display navigation arrows at the screen edges
				arrow_position:     self.get_lightbox_config( 'arrow_position' ),
				infobar:            self.get_lightbox_config( 'infobar' ), // Should display infobar (counter and arrows at the top)
				toolbar:            self.get_lightbox_config( 'toolbar' ), // Should display toolbar (buttons at the top)
				idleTime:           self.get_lightbox_config( 'idle_time' ) ? self.get_lightbox_config( 'idle_time' ) : false, // by default there shouldn't be any, otherwise value is in seconds
				smallBtn:           self.get_lightbox_config( 'show_smallbtn' ),
				protect:            false, // Disable right-click and use simple image protection for images
				image:              { preload: false },
				animationEffect:    animationEffect,
				animationDuration:  300, // Duration in ms for open/close animation
				btnTpl : {
					smallBtn   :        self.get_lightbox_config( 'small_btn_template' ),
				},
				zoomOpacity:        'auto',
				transitionEffect:   transitionEffect, // Transition effect between slides
				transitionDuration: 200, // Duration in ms for transition animation
				baseTpl:            self.get_lightbox_config( 'base_template' ), // Base template for layout
				spinnerTpl:         '<div class="envirabox-loading"></div>', // Loading indicator template
				errorTpl:           self.get_lightbox_config( 'error_template' ), // Error message template
				fullScreen:         self.get_config( 'fullscreen' ) ? fullscreen : false,
				touch:              { vertical: true, momentum: true }, // Set `touch: false` to disable dragging/swiping
				hash:               false,
				insideCap:          self.get_lightbox_config( 'inner_caption' ),
				capPosition:        self.get_lightbox_config( 'caption_position' ),
				capTitleShow: 		self.get_config('lightbox_title_caption') && self.get_config('lightbox_title_caption') != 'none' && self.get_config('lightbox_title_caption') != '0' ? self.get_config('lightbox_title_caption') : false,
				media : {
					youtube : {
						params : {
							autoplay : 0
						}
					}
				},
				wheel:              self.get_config( 'mousewheel' ) ? self.get_config( 'mousewheel' ) : true,
				slideShow:          slideshow,
				thumbs:             thumbs,
				mobile : {
					clickContent : function( current, event ) {
						return current.type === 'image' ? 'toggleControls' : false;
					},
					clickSlide : function( current, event ) {
						return current.type === 'image' ? 'toggleControls' : 'close';
					},
					dblclickContent : false,
					dblclickSlide :false,
				},
				// Clicked on the content
				clickContent:   self.get_lightbox_config( 'click_content' ) ? self.get_lightbox_config( 'click_content' ) : 'toggleControls', // clicked on the image itself
				clickSlide:     self.get_lightbox_config( 'click_slide' ) ? self.get_lightbox_config( 'click_slide' ) : 'close', // clicked on the slide
				clickOutside:   self.get_lightbox_config( 'click_outside' ) ? self.get_lightbox_config( 'click_outside' ) : 'toggleControls', // clicked on the background (backdrop) element

				// Same as previous two, but for double click
				dblclickContent : false,
				dblclickSlide   : false,
				dblclickOutside : false,

				// Video settings
				videoPlayIcon: true, /* self.get_config('videos_play_icon_thumbnails') ? true : false, */

				// Callbacks
				// ==========
				onInit: function(instance, current) {

					$( document ).trigger( 'envirabox_api_on_init', [ self, instance, current ] );
				},

				beforeLoad: function(instance, current) {

					$( document ).trigger( 'envirabox_api_before_load', [ self, instance, current ] );

				},
				afterLoad: function(instance, current) {

					$( document ).trigger( 'envirabox_api_after_load', [ self, instance, current ] );

				},

				beforeShow: function(instance, current) {

					/* override title in legacy to display gallery and not album title */
					if ( $( '#envirabox-buttons-title' ).length > 0 && current.gallery_title ) {
						document.getElementById( "envirabox-buttons-title" ).innerHTML = '<span>' + current.gallery_title + '</span>';
					}

					$( document ).trigger( 'envirabox_api_before_show', [ self, instance, current ] );

				},
				afterShow: function(instance, current) {

					if ( prepend == undefined || prepend_cap == undefined) {

						var prepend     = false,
							prepend_cap = false;

					}

					if ( prepend != true ) {

						$( '.envirabox-position-overlay' ).each(
							function(){
								$( this ).prependTo( current.$content );
							}
						);

						prepend = true;
					}

					/* support older albums or if someone overrides the keyboard configuration via a filter, etc. */

					if ( self.get_config( 'keyboard' ) !== undefined && self.get_config( 'keyboard' ) === 0 ) {

						$( window ).keypress(
							function(event){

								if ([32, 37, 38, 39, 40].indexOf( event.keyCode ) > -1) {
									event.preventDefault();
								}

							}
						);

					}

					/* legacy theme we hide certain elements initially to prevent user seeing them for a second in the upper left until the CSS fully loads */
					$( '.envirabox-title' ).css( 'visibility', 'visible' );
					$( '.envirabox-caption' ).css( 'visibility', 'visible' );
					$( '.envirabox-navigation' ).show();
					$( '.envirabox-navigation-inside' ).show();

					$( document ).trigger( 'envirabox_api_after_show', [ self, instance, current ] );

				},

				beforeClose: function(instance, current) {

					$( document ).trigger( 'envirabox_api_before_close', [ self, instance, current ] );

				},
				afterClose: function(instance, current) {

					$( document ).trigger( 'envirabox_api_after_close', [ self, instance, current ] );

				},

				onActivate: function(instance, current) {

					$( document ).trigger( 'envirabox_api_on_activate', [ self, instance, current ] );

				},
				onDeactivate: function( instance, current ) {

					$( document ).trigger( 'envirabox_api_on_deactivate', [ self, instance, current ] );

				},

		};

		// Mobile Overrides
		if ( self.is_mobile() ){

			if ( self.get_config( 'mobile_thumbnails' ) !== 1 ) {
				self.lightbox_options.thumbs = false;
			}

		}

		$(document).trigger('envirabox_options', self );

		function getVideoUrlVars( url ) {
			var vars = [], hash;
			var q    = url.split( '?' )[1];
			if (q != undefined) {
				q = q.split( '&' );
				for (var i = 0; i < q.length; i++) {
					hash = q[i].split( '=' );
					vars.push( hash[1] );
					vars[hash[0]] = hash[1];
				}
			}
			return vars;
		}

		$( '#envira-gallery-wrap-' + self.id + ' .envira-gallery-link' ).on(
			"click",
			function(e){

				e.preventDefault();
				e.stopImmediatePropagation();

				let $this      = $( this ),
				images         = [],
				$envira_images = $this.data( 'gallery-images' ),
				sorted_ids     = $this.data( 'gallery-sort-ids' ), // sort by sort ids, not by output of gallery-images, because retaining object key order between unserialisation and serialisation in JavaScript is never guaranteed.
				// backup plan in case there isn't gallery-sort-ids (maybe something cached?) or this option wasn't selected in the album settings
				sorting_factor      = sorted_ids !== undefined && self.data.gallery_sort == 'gallery' ? 'id' : 'image',
				sorting_factor_data = sorted_ids !== undefined && self.data.gallery_sort == 'gallery' ? sorted_ids : $envira_images,
				active              = $.envirabox.getInstance(),
				envira_autoplay     = '0';

				$.each(
					sorting_factor_data,
					function(i){

						if ( sorting_factor == 'id' ) {
							var envira_image          = $envira_images[this];
							envira_image.opts.caption = envira_image.caption;
						} else {
							var envira_image  = this;
							this.opts.caption = this.caption;
						}

						if ( envira_image.link !== undefined ) {
							envira_image.link.match( /(http:|https:|)\/\/(player.|www.)?(vimeo\.com|facebook\.com|videopress\.com|wistia\.com|twitch\.tv|metacafe\.com|instagram\.com|dailymotion\.com|youtu(be\.com|\.be|be\.googleapis\.com))\/(video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/ );
							if ( RegExp.$3.indexOf( 'youtu' ) > -1 && RegExp.$6.indexOf( 'videoser' ) > -1 ) {
								// youtube playlists
								envira_image.video = true;
								envira_image.type  = 'iframe';
								envira_image.src   = envira_image.link;
							} else if (RegExp.$3.indexOf( 'youtu' ) > -1 ) {
								// youtube
								var video_id_regExp    = /^.*((youtu.be\/|youtube.com\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/,
								match                  = envira_image.link.match( video_id_regExp ),
								passed_args            = getVideoUrlVars( envira_image.link ),
								envira_passed_autoplay = '0',
								envira_autoplay        = '0';
								if (match && match[7].length == 11) {
									// Do anything for being valid
									envira_image.video     = true;
									envira_image.type      = 'iframe';
									envira_image.contentProvider = 'youtube';
									envira_image.provider        = 'youtube';
									envira_passed_autoplay = ( passed_args.autoplay !== undefined ) ? passed_args.autoplay : '0';
									envira_autoplay        = ( self.data.videos_autoplay !== undefined ) ? self.data.videos_autoplay : envira_passed_autoplay;
									envira_image.src       = ('https://www.youtube.com/embed/' + match[7] + '?autoplay=' + envira_autoplay);
								}
							} else if (RegExp.$3.indexOf( 'vimeo' ) > -1) {
								// vimeo
								var video_id_regExp = /^.+vimeo.com\/(.*\/)?([\d]+)(.*)?/,
								match               = envira_image.link.match( video_id_regExp );
								if (match && match[2]) {
									envira_image.video = true;
									envira_image.type  = 'iframe';
									envira_image.contentProvider = 'vimeo';
									envira_image.provider        = 'vimeo';
									envira_autoplay    = ( self.data.videos_autoplay !== undefined ) ? self.data.videos_autoplay : '0';
									envira_image.src   = ('https://player.vimeo.com/video/' + match[2] + '?autoplay=' + envira_autoplay);
								}
							} else if (RegExp.$3.indexOf( 'dailymotion' ) > -1) {
								// dailymotion
								var video_id_regExp = /dailymotion.com\/video\/(.*)\/?(.*)/,
								match               = envira_image.link.match( video_id_regExp );
								if (match && match[1]) {
									envira_image.video = true;
									envira_image.type  = 'iframe';
									envira_image.contentProvider = 'dailymotion';
									envira_image.provider        = 'dailymotion';
									envira_autoplay    = ( self.data.videos_autoplay !== undefined ) ? self.data.videos_autoplay : '0';
									if ( envira_autoplay == '1' ) {
										var new_match = match[1].replace( 'autoplay=0', 'autoplay=' + envira_autoplay );
									} else {
										var new_match = match[1];
									}
									envira_image.src = ('//www.dailymotion.com/embed/video/' + new_match );
								}
							} else if (RegExp.$3.indexOf( 'facebook' ) > -1) {
								// facebook
								var video_id_regExp = /facebook.com\/facebook\/videos\/(.*)\/?(.*)/,
								match               = envira_image.link.match( video_id_regExp );
								if (match && match[1]) {
									envira_image.video   = true;
									envira_image.type    = 'genericDiv';
									envira_image.subtype = 'facebook',
									envira_image.contentProvider = 'facebook';
									envira_image.provider        = 'facebook';
									envira_image.src     = ('//www.facebook.com/facebook/videos/' + match[1] );
								}
							} else if (RegExp.$3.indexOf( 'metacafe' ) > -1) {
								// metacafe
								var video_id_regExp = /metacafe.com\/watch\/(\d+)\/(.*)?/,
								match               = envira_image.link.match( video_id_regExp );
								if (match && match[1]) {
									envira_image.video = true;
									envira_image.type  = 'iframe';
									envira_image.contentProvider = 'metacafe';
									envira_image.provider        = 'metacafe';
									envira_image.src   = ('//www.metacafe.com/embed/ ' + match[1] + '/?ap=1' );
								}
							} else if (RegExp.$3.indexOf( 'twitc' ) > -1) {
								// twitch
								var video_id_regExp = /twitch.tv\/videos\/(.*)\/?(.*)/,
								match               = envira_image.link.match( video_id_regExp );
								if (envira_image.link.indexOf( 'video=' ) > -1) {
									envira_image.video = true;
									envira_image.type  = 'iframe';
									envira_autoplay    = ( self.data.videos_autoplay !== undefined ) ? self.data.videos_autoplay : '0';
									if ( envira_autoplay == '1' && envira_image.link.indexOf( 'autoplay=false' ) > -1 ) {
										var new_match = envira_image.link.replace( 'autoplay=false', 'autoplay=true' );
									} else if ( envira_autoplay == '1' ) {
										var new_match = envira_image.link + '&autoplay=true';
									}
									envira_image.src = ( new_match );
									envira_image.contentProvider = 'twitch';
									envira_image.provider        = 'twitch';
								}
								envira_image.src = envira_image.link; /* overwrite the above, test further */
							} else if (RegExp.$3.indexOf( 'videopress' ) > -1) {
								// videopress
								var video_id_regExp = /videopress.com\/v\/(.*)\/?(.*)/,
								match               = envira_image.link.match( video_id_regExp );
								if (match && match[1]) {
									envira_image.video = true;
									envira_image.type  = 'iframe';
									envira_autoplay    = ( self.data.videos_autoplay !== undefined ) ? self.data.videos_autoplay : '0';
									if ( envira_autoplay == '1' ) {
										var new_match = envira_image.link.replace( 'autoplay=0', 'autoplay=' + envira_autoplay );
									} else {
										var new_match = envira_image.link;
									}
									envira_image.src             = new_match;
									envira_image.contentProvider = 'videopress';
									envira_image.provider        = 'videopress';
								}
							} else if (RegExp.$3.indexOf( 'wistia' ) > -1) {
								// wistia
								var video_id_regExp = /wistia.com\/medias\/(.*)\/?(.*)/,
								match               = envira_image.link.match( video_id_regExp );
								if (match && match[1]) {
									envira_image.video = true;
									envira_image.type  = 'iframe';
									envira_autoplay    = ( self.data.videos_autoplay !== undefined ) ? self.data.videos_autoplay : '0';
									if ( envira_autoplay == '1' && match[1].indexOf( 'autoplay=false' ) > -1 ) {
										var new_match = match[1].replace( 'autoPlay=false', 'autoPlay=true' );
									} else if ( envira_autoplay == '1' ) {
										var new_match = match[1] + '&autoPlay=true';
									} else { // if nothing, go back to this
										var new_match = match[1];
										envira_image.provider        = 'wistia';
									}
									envira_image.src = ('//fast.wistia.net/embed/iframe/' + new_match);
									envira_image.contentProvider = 'wistia';
									envira_image.provider        = 'wistia';
								}
							} else if ( RegExp.$3.indexOf( 'instagram' ) > -1 ) {
								// instagram
								var video_id_regExp = /(instagr\.am|instagram\.com)\/p\/([a-zA-Z0-9_\-]+)\/?/i,
								match               = envira_image.link.match( video_id_regExp );
								if (match && match[1]) {
									envira_image.video = true;
									envira_image.type  = 'iframe';
									envira_image.src   = ('//' + match[1] + '/p/' + match[2] + '/media/?size=l');
								}
								envira_image.src = envira_image.link; /* overwrite the above, test further */
							} else if (envira_image.link.indexOf( 'mp4' ) > -1) {
								envira_image.video            = true;
								envira_image.type             = 'video';
								envira_image.src              = envira_image.link;
								envira_image.opts.videoFormat = 'video/mp4';
							}
						}

						images.push( envira_image );

					}
				);

				if ( active ) {
					return;
				}

				$.envirabox.open( images, self.lightbox_options );

			}
		);

	}
	/**
	 * Get a config option based off of a key.
	 *
	 * @since 1.7.1
	 */
	get_config(key) {

		return this.data[key];

	}

	/**
	 * Helper method to get config by key.
	 *
	 * @since 1.7.1
	 */
	get_lightbox_config(key) {

		return this.envirabox_config[key];

	}

	/**
	 * Helper method to get image from id
	 *
	 * @since 1.7.1
	 */
	get_image(id) {

		return this.images[id];

	}

	is_mobile(){
		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
			return true;
		}
		return false;
	}

	/**
	 * Helper method for logging if ENVIRA_DEBUG is true.
	 *
	 * @since 1.7.1
	 */
	log(log) {

		// Bail if debug or log is not set.
		if (envira_gallery.debug == undefined || ! envira_gallery.debug || log == undefined) {

			return;

		}
		console.log( log );

	}


}

module.exports = Envira_Album;
