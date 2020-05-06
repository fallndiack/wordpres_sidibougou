;(function ( $, window, document ) {

	var socialElement = false,
		obj           = null,
		instance      = null,
		current       = null,
		YouTubePlayer = null;

		$( document ).on(
			'envirabox_api_before_show',
			function( e, object, inst, cur ){

				obj      = object,
				instance = inst,
				current  = cur;

				$( '.envirabox-caption' ).show();
				$( '.envirabox-navigation' ).show();
				$( '.envirabox-navigation-inside' ).show();

				var vid = document.querySelector( ".envirabox-content video" );
				if ( vid !== null ) {
					vid.pause();
				}

			}
		);

		$( document ).on(
			'envirabox_api_after_show',
			function( e, object, inst, cur ) {

				var obj        = object,
				instance       = inst,
				current        = cur,
				slide          = current,
				$content       = slide.$content,
				$video_content = $content.find( 'video' ),
				opts           = current.opts.iframe,
					$slide     = current.$slide,
					$iframe    = $slide.find( 'iframe' ),
					$fb_video  = $slide.find( '.fb-video' ),
				$video         = $slide.find( 'video' ),
				current_src    = current.src;

				if ( current.type == 'video' || ( current.type == 'iframe' && current_src.indexOf( '.pdf' ) === -1 ) || current.subtype == 'facebook' ) {
					instance.showLoading( current );
				}

				if ( obj.data.videos_enlarge === 1 && ( current.type == 'video' || current.type == 'iframe' ) ) {
					slide.$slide.trigger( 'refresh' );
					envira_video_reset_width_height();
				} else if ( current.type == 'video' || current.type == 'iframe' ) {
					envira_video_reset_width_height();
				}

				if ( current.subtype == 'facebook' || current.subtype == 'facebook-video' ) {
					/* because facebook takes a while to parse */
					instance.showLoading( current );
					var the_id = $fb_video.attr( 'id' );
					if (typeof FB !== 'undefined') {
						FB.XFBML.parse(
							document,
							function() {
								instance.hideLoading( current );
								envira_video_reset_width_height();
							}
						);
					} else {
						alert( 'Having Trouble Connecting To Facebook' );
					}
				}

				/* size video lightboxes best we can */

				/* set iframe on load */

				$iframe.on(
					'load.fb error.fb',
					function(e) {
						this.isReady = 1;
						slide.$slide.trigger( 'refresh' );
						instance.afterLoad( slide );
					}
				);

				/* recalculate iframe's width and height when it's refreshed */

				function envira_video_reset_width_height() {

					var $content      = slide.$content,
					contentWidth      = $iframe.width(),
					contentHeight     = $iframe.height(),
					frameWidth        = opts.css.width,
					frameHeight       = opts.css.height,
					contentWidthCalc  = 4 / 3,
					contentHeightCalc = 3 / 4,
					$contents,
					newContentWidth   = $content.width(),
					newContentHeight  = $content.height(),
					$body,
					$is_video         = false,
					$is_generic       = false;

					if ( $iframe[0] !== undefined && $iframe[0].isReady !== 1) {
						// setTimeout( envira_video_reset_width_height(), 3000);
						return;
					}

					if ( $iframe.width() === null ) { // we might not be dealing with an iframe, so check for video
						$video_content   = $content.find( 'video' );
						$generic_content = $content.find( 'div.fb-video' );
						if ( $video_content !== undefined && $video_content.length > 0 ) {
							$content  = $video_content;
							$is_video = true;
						} else if ( $generic_content !== undefined && $generic_content.length > 0 ) {
							$is_generic = true;
						} else {
							// no idea what this is
							return;
						}
					}

					if ( obj.data.videos_enlarge === 1 && ( slide.contentProvider == 'vimeo' || slide.contentProvider == 'wistia' || slide.contentProvider == 'youtube' || current.type == 'video' || slide.contentProvider == 'videopress' || slide.contentProvider == 'twitch' ) ) {
						if (  slide.contentProvider == 'vimeo' || slide.contentProvider == 'youtube' || slide.contentProvider == 'wistia' || slide.contentProvider == 'videopress' || slide.contentProvider == 'twitch' ) {
							$content.css( 'max-width', '95%' );
						}
						newContentWidth = $slide.width();
						if ( newContentWidth > ( $( window ).height() * .85 ) ) {
							newContentWidth = $( window ).width() * .85;
						}
						newContentHeight = $slide.height();
						if ( newContentHeight > ( $( window ).height() * .85 ) ) {
							newContentHeight = $( window ).height() * .85;
						}
					} else if ( $is_generic == true ) {
						$content.css( 'max-width', '80%' );
					}

					if ( ( slide.videoAspectRatio !== undefined && slide.videoAspectRatio == '16:9' ) || ( obj.data.videos_enlarge === 1 && ( current.type == 'video' || slide.contentProvider == 'wistia' || slide.contentProvider == 'vimeo' || slide.contentProvider == 'youtube' || slide.contentProvider == 'videopress' || slide.contentProvider == 'twitch' ) ) ) {

						contentWidthCalc  = 16 / 9;
						contentHeightCalc = 9 / 16;

						if ( $content.parent().height() < $content.parent().width() ) {
							$content.css( 'height', newContentHeight ).css( 'width', $content.height() * contentWidthCalc ).css( 'height', $content.width() * contentHeightCalc );
						} else {
							$content.css( 'width', newContentWidth ).css( 'height', $content.width() * contentHeightCalc );
						}

						// $iframe.css('width', $content.width() + 'px' );
						// $iframe.css('height', $content.height() + 'px' );
					}

					instance.hideLoading( slide );

					if ( $is_generic == true &&
					( $( '.envirabox-container' ).hasClass( 'envirabox-theme-captioned' ) || $( '.envirabox-container' ).hasClass( 'envirabox-theme-polaroid' ) ) ) {
						$content.css( 'max-width', '80%' );
					}

					$content.removeClass( "envirabox-iframe-hidden" );
					$content.removeClass( "envirabox-hidden" ); // not sure this is the fix
					instance.hideLoading( current );

				};
 
				$slide.on(
					"refresh.fb",
					function() {
						envira_video_reset_width_height();
					}
				);

				$( '.envirabox-caption' ).show();
				$( '.envirabox-navigation' ).show();
				$( '.envirabox-navigation-inside' ).show();
				// $content.removeClass("envirabox-iframe-hidden");
				// $content.removeClass("envirabox-hidden"); // not sure this is the fix
				
				if ( obj.data.videos_controls === 0 ) {
					/* if there are no controls, then we autoplay the video */
					obj.data.videos_autoplay = 1;
				}
			
				/* if auto play is on, if this is a self-hosted video then play it */
				if ( obj.data.videos_autoplay === 1 ) {
					// autoplay tricks
					if ( obj.data.slideshow !== undefined && obj.data.slideshow === 1 && ( slide.contentProvider == 'youtube' ) ) {
						var video_url   = $iframe.attr( 'src' ),
						video_iframe_id = $iframe.attr( 'id' );
						// youtube videos likely need to be muted to autoplay
						// remove any reference to muting
						video_url = ( video_url.replace( 'mute=0', '' ) ) + '&mute=1';
						video_url = ( video_url.replace( 'muted=1', '' ) ) + '&muted=1';
						$iframe.attr( 'src', video_url );
						instance.SlideShow.stop();

						YouTubePlayer = new YT.Player(
							video_iframe_id,
							{
								playerVars: { 'autoplay': 1, 'mute': 0 },
								events: {
									'onReady': YTonPlayerReady,
									'onStateChange': YTonPlayerStateChange
								}
							}
						);

						function YTonPlayerReady() {
							// when player is ready
						}

						function YTonPlayerStateChange( event ) {
							if (event.data == YT.PlayerState.ENDED) {
								instance.SlideShow.start();
								instance.next();
							}
						}
					} else if ( ( obj.data.slideshow === undefined || obj.data.slideshow === 0 ) && ( slide.contentProvider == 'vimeo' ) ) {
						var video_url       = $iframe.attr( 'src' ),
							video_iframe_id = $iframe.attr( 'id' );

						// vimeo videos likely need to be muted to autoplay
						// add reference to muting
						if ( video_url.indexOf( 'muted=' ) >= 0 ) {					
							video_url = ( video_url.replace( 'muted=0', '' ) ) + '&muted=1';
							video_url = ( video_url.replace( 'muted=1', '' ) ) + '&muted=1';
						} else {
							video_url = ( video_url.replace( 'autoplay=1', 'autoplay=1&muted=1' ) );
							$iframe.attr('allow','autoplay');
						}

						$iframe.attr( 'src', video_url );

					} else if ( obj.data.slideshow !== undefined && obj.data.slideshow === 1 && ( slide.contentProvider == 'vimeo' ) ) {
						var video_url       = $iframe.attr( 'src' ),
							video_iframe_id = $iframe.attr( 'id' );
						// vimeo videos likely need to be muted to autoplay
						// remove any reference to muting
						video_url = ( video_url.replace( 'muted=0', '' ) ) + '&muted=1';
						video_url = ( video_url.replace( 'muted=1', '' ) ) + '&muted=1';
						// remove api=1... important, or .Player below doesn't work
						video_url = ( video_url.replace( 'api=1', '' ) );

						$iframe.attr( 'src', video_url );
						var vimeoIframe = document.querySelector( 'iframe' );
						var vimeoPlayer = new Vimeo.Player( vimeoIframe );
						instance.SlideShow.stop();
						vimeoPlayer.on(
							'timeupdate',
							function( data ) {
								if (data.percent === 1) {
									instance.SlideShow.start();
									instance.next();
								}
							}
						);
					} else if ( obj.data.slideshow !== undefined && obj.data.slideshow === 1 && ( slide.contentProvider == 'dailymotion' ) ) {
						var video_url = $iframe.attr( 'src' );
						// youtube videos likely need to be muted to autoplay
						// remove any reference to muting
						video_url = ( video_url.replace( 'mute=0', '' ) ) + '&mute=1';
						$iframe.attr( 'src', video_url );
					} else {
						var vid = document.querySelector( 'div.envirabox-slide--current div.envirabox-content video' );
						if ( vid !== null ) {
							vid.currentTime = 0;
							vid.load();
							vid.play();

							if ( obj.data.slideshow !== undefined && obj.data.slideshow === 1 && instance.SlideShow.isActive === true ) {
								vid.addEventListener( 'ended',enviraVideoHandler,false );
								instance.SlideShow.stop();
								function enviraVideoHandler(e) {
									vid.currentTime = 0;
									instance.SlideShow.start();
									instance.next();
								}
							}
						}
					}
				}

			}
		);

		$( document ).on(
			'envirabox_api_before_close',
			function( e, object, inst, cur ){

			}
		);

		/* special tricks regarding embedded videos in galleries */

		$( window ).load(
			function() {
				$( "iframe.envira_facebook_embed" ).each(
					function() {
						var parent_div_height = $( this ).closest( '.envira-facebook-responsive' ).innerHeight();
						$( this ).attr( 'style', 'height: ' + parent_div_height + 'px' );
						var video_id = $( this ).closest( '.envira-gallery-item' ).attr( 'id' ),
						gallery_id   = $( this ).closest( '.envira-gallery-public' ).data( 'envira-id' );
						$( document ).trigger(
							{
								type:           'envira_image_lazy_load_complete',
								video_id: 		   video_id,
								gallery_id:        gallery_id,
							}
						);
					}
				);
				$( "video.envira-video" ).each(
					function() {
						var video_id = $( this ).closest( '.envira-gallery-item' ).attr( 'id' ),
						gallery_id   = $( this ).closest( '.envira-gallery-public' ).data( 'envira-id' );
						$( document ).trigger(
							{
								type:           'envira_image_lazy_load_complete',
								video_id: 		   video_id,
								gallery_id:        gallery_id,
							}
						);
					}
				);
				$( "iframe.envira_youtube_embed" ).each(
					function() {
						var video_id = $( this ).closest( '.envira-gallery-item' ).attr( 'id' ),
						gallery_id   = $( this ).closest( '.envira-gallery-public' ).data( 'envira-id' );
						$( document ).trigger(
							{
								type:           'envira_image_lazy_load_complete',
								video_id: 		   video_id,
								gallery_id:        gallery_id,
							}
						);
					}
				);
				$( "iframe.envira_wistia_embed" ).each(
					function() {
						var video_id = $( this ).closest( '.envira-gallery-item' ).attr( 'id' ),
						gallery_id   = $( this ).closest( '.envira-gallery-public' ).data( 'envira-id' );
						$( document ).trigger(
							{
								type:           'envira_image_lazy_load_complete',
								video_id: 		   video_id,
								gallery_id:        gallery_id,
							}
						);
					}
				);
				$( "iframe.envira_vimeo_embed" ).each(
					function() {
						var video_id = $( this ).closest( '.envira-gallery-item' ).attr( 'id' ),
						gallery_id   = $( this ).closest( '.envira-gallery-public' ).data( 'envira-id' );
						$( document ).trigger(
							{
								type:           'envira_image_lazy_load_complete',
								video_id: 		   video_id,
								gallery_id:        gallery_id,
							}
						);
					}
				);

			}
		);

		// function EnviraInitYouTubeLightboxPlayer( video_iframe_id, video_url, video_id ) {
		// }
})( jQuery , window, document );
