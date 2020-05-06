;(function ( $, window, document ) {

	var  socialElement = false,
		obj            = null,
		instance       = null,
		current        = null;

	// DOM ready
	$(
		function(){

			$( document ).on(
				'envirabox_api_before_show',
				function(){
					obj      = null,
					instance = null,
					current  = null;
					// Initial social div or cloned? We use cloned for certain LB actions
					if ( socialElement === undefined || socialElement === false ) {

						socialElement = $( '.envirabox-inner div.envira-social-buttons-exterior' );

					}
				}
			);

			$( document ).on(
				'envirabox_api_after_show',
				function( e, object, inst, cur ){

					obj      = object,
					instance = inst,
					current  = cur;

					// if the overlay exists, display it now
					$( '.envirabox-position-overlay' ).css( 'z-index', '1' );
					$( '.envirabox-position-overlay' ).css( 'position', 'absolute' );
					$( '.envirabox-position-overlay' ).css( 'visibility', 'visible' );
					$( '.envirabox-position-overlay' ).css( 'opacity', '1' );

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( current );
					}
					// Move the social div, assign CSS
					if ( socialElement !== undefined && socialElement !== false ) {

						socialElement.prependTo( current.$content ).addClass( 'social-active' );

					}

				}
			);

			$( document ).on(
				'click',
				'.envirabox-inner .envira-social-buttons a',
				function( e ) {

					/* is this an album or a gallery? */
					if (obj.data.album_id !== undefined ) {
						var item_type     = 'album',
						envira_album_id   = $.trim( obj.id ),
						envira_gallery_id = ( current.gallery_id !== undefined && current.gallery_id != '' ) ? current.gallery_id : false; /* make sure gallery is passed otherwise FB, LinkedIn, etc. won't be able to grab an image! */
					} else {
						var item_type     = 'gallery',
						envira_gallery_id = $.trim( obj.id ),
						envira_album_id   = false;
					}

					/* this only focuses on LB social stuff obviouslly */
					var 	hash                  = window.location.hash,
					url                           = $( this ).attr( 'href' ),
					width                         = $( this ).parent().data( 'width' ),
					height                        = $( this ).parent().data( 'height' ),
					network                       = $( this ).parent().data( 'network' ),
					deeplinking                   = $( this ).parent().data( 'deeplinking' ),
					page_string                   = '';
					envira_pagination_page        = false;
					envira_lb_image               = $( 'img.envirabox-image' ).attr( 'src' ),
					envira_gallery_item_id        = false,
					envira_gallery_item_title     = false,
					envira_gallery_item_caption   = false,
					envira_gallery_item_src       = false,
					envira_gallery_item_full_size = false,
					link_source                   = $( '.envirabox-wrap' ),
					rand                          = Math.floor( ( Math.random() * 10000 ) + 1 ),
					envira_permalink              = envira_social_get_query_arg( 'envira', window.location.href );

					// first, we determine the image id - can't so anything without it
					if ( item_type == 'gallery' ) {

						envira_gallery_item_id      = current.enviraItemId;
						envira_gallery_item_title   = ( current.enviraCaption !== undefined && current.enviraCaption != '' ) ? current.enviraCaption : '';
						envira_gallery_item_caption = ( current.caption !== undefined && current.caption != '' ) ? current.caption : '';
						envira_gallery_item_src     = current.src;

						if ( current.type == 'iframe' || ( current.subtype !== undefined && current.subtype == 'facebook' ) ) {

							envira_gallery_item_full_size = ( current.videoPlaceholder !== undefined && current.videoPlaceholder != '' ) ? current.videoPlaceholder : current.thumb;

						} else {

							// assign a size from data object
							var the_size = obj.data.social_email_image_size;

							// determine what size url to pass
							if ( the_size !== '' && the_size !== undefined ) {

								// start with the default/max as default
								envira_gallery_item_full_size = current.link;

								// find the size in the object.images
								if ( obj.images !== undefined && obj.images.constructor === Array ) { // fastest in Chrome
									if ( envira_gallery_item_id in obj.images ) {
										if ( obj.images[envira_gallery_item_id][the_size] !== undefined ) {
											envira_gallery_item_full_size = obj.images[envira_gallery_item_id][the_size];
										}
									}
								}

							} else {

								// go with large/full as default
								envira_gallery_item_full_size = current.link;

							}

						}

					} else if ( item_type == 'album' ) {

						envira_gallery_item_id      = current.id;
						envira_gallery_item_title   = current.title;
						envira_gallery_item_caption = current.caption;
						envira_gallery_item_src     = current.src;

						// assign a size from data object
						var the_size = obj.data.social_email_image_size;

						// determine what size url to pass
						if ( the_size !== '' && the_size !== undefined ) {

							// start with the default/max as default
							envira_gallery_item_full_size = current.link;

							if ( the_size in current ) {
								envira_gallery_item_full_size = current[the_size];
							}

						} else {

							// go with large/full as default
							envira_gallery_item_full_size = current.link;

						}

					} else {

						return;

					}

					if ( $( '.envira-pagination' ).length > 0 ) {

						// attempt to get page via URL querystring
						envira_pagination_page = envira_social_get_query_arg( 'page', $( this ).attr( 'href' ) );
						if ( ! envira_pagination_page ) {
							// attempt to get page via pagination bar
							envira_pagination_page = $( this ).closest( '.envira-gallery-wrap' ).find( '.envira-pagination' ).data( 'page' );
							if ( ! envira_pagination_page ) {
								// attempt to get page via fancybox data value, which should be added upon afterShow
								envira_pagination_page = $( 'img.envirabox-image' ).data( 'pagination-page' );
							}
						}
					}

					if ( envira_pagination_page !== undefined && envira_pagination_page !== false ) {
						// now turn this into a string we can add after the url
						page_string = envira_pagination_page + '/';
					}

					if ( typeof envira_permalink !== "undefined" && envira_permalink !== null ) {
						envira_permalink = 'envira=' + envira_permalink;
					} else {
						envira_permalink = '';
					}

					if ( typeof envira_gallery_item_full_size === "undefined" || envira_gallery_item_full_size === null ) {
						envira_gallery_item_full_size = envira_gallery_item_src;
					}

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( 'detected gallery_id (lightbox):' + envira_gallery_id );
						console.log( 'detected gallery_item_id (lightbox):' + envira_gallery_item_id );
						console.log( 'detected hash:' + hash );
						console.log( 'detected envira_permalink:' + envira_permalink );
					}

					// if there are undefined vars for some reason, make them empty strings
					if (typeof title === "undefined") {
						caption = '';
					}
					if (typeof caption === "undefined") {
						caption = '';
					}

					// generate base_link, detect deeplinking
					var base_link = ( hash.length > 0 ) ? window.location.href.split( '#' )[0] : window.location.href.split( '?' )[0];

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( 'base_link (lightbox):' + base_link );
					}

					// "clean" the base_link var
					base_link = envira_clean_base_link( base_link );

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( 'base_link cleaned (lightbox):' + base_link );
					}

					if ( envira_permalink ) {
						envira_permalink = '&' + envira_permalink;
					}

					// generate the actual link based on the base_link, depending if deeplinking/hash exists
					link = ( hash.length > 0 ) ? base_link + '?envira_album_id=' + envira_album_id + '&envira_social_gallery_id=' + envira_gallery_id + '&envira_social_gallery_item_id=' + envira_gallery_item_id + '&rand=' + rand + envira_permalink + hash : base_link + page_string + '?envira_album_id=' + envira_album_id + '&envira_social_gallery_id=' + envira_gallery_id + '&envira_social_gallery_item_id=' + envira_gallery_item_id + '&rand=' + rand + envira_permalink;

					switch ( network ) {
						case 'facebook':

							var quote     = '',
							facebook_text = '',
							title         = '',
							tags          = '';

							if (typeof $( this ).attr( 'data-envira-social-facebook-text' ) !== "undefined") {
								facebook_text = decodeURIComponent( $( this ).data( 'envira-social-facebook-text' ) );
								facebook_text = facebook_text.replace( new RegExp( "\\+","g" ),' ' );
								if ( $.trim( facebook_text ) == '' ) {
									facebook_text = ' '; } // blank spaces force Facebook to not display description
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating facebook_text' );
									console.log( facebook_text );
								}
							}

							if (typeof $( this ).attr( 'data-envira-facebook-quote' ) !== "undefined") {
								quote = decodeURIComponent( $( this ).data( 'envira-facebook-quote' ) );
								quote = quote.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating quote' );
									console.log( quote );
								}

							}

							if (typeof $( this ).attr( 'data-envira-title' ) !== "undefined") {
								title = decodeURIComponent( $( this ).data( 'envira-title' ) );
								title = title.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating title' );
									console.log( title );
								}
							}

							if (typeof $( this ).attr( 'data-facebook-tags-manual' ) !== "undefined") {
								tags = decodeURIComponent( $( this ).data( 'facebook-tags-manual' ) );
								tags = tags.replace( new RegExp( "\\+","g" ),' ' );
								// remove any dashes, since FB doesn't like them
								tags = tags.replace( /-/g, '' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating tags' );
									console.log( title );
								}
							} else {
								if ( envira_social.debug ) {
									console.log( 'updating tags - missing' );
									console.log( $( '.envirabox-wrap' ).find( 'img.envirabox-image' ).data( 'envira-facebook-tags-manual' ) );
								}
							}

							url = 'https://www.facebook.com/dialog/feed?app_id=' + envira_social.facebook_app_id + '&display=popup&link=' + link + '&picture=' + envira_gallery_item_src + '&name=' + title + '&caption=' + caption + '&description=' + facebook_text + '&redirect_uri=' + link + '#envira_social_sharing_close';

							break;

						case 'twitter':

							var twitter_text = '';
							// caption and link var is taken from the 'general' caption above
							if (typeof $( this ).attr( 'data-envira-social-twitter-text' ) !== "undefined") {
								twitter_text = decodeURIComponent( $( this ).data( 'envira-social-twitter-text' ) );
								twitter_text = twitter_text.replace( new RegExp( "\\+","g" ),' ' );
								if ( envira_social.debug ) {
									console.log( 'updating twitter_text' );
									console.log( twitter_text );
								}
							}

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'updating twitter_text' );
								console.log( twitter_text );
								console.log( 'caption' );
								// $(caption).text() = 'teset3';
							}

							// Remove HTML from Caption
							var caption = caption.replace( /"/g, '&quote;' );

							url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent( $.trim( $.trim( caption ) + ' ' + $.trim( twitter_text ) ) ) + '&url=' + encodeURIComponent( link );

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'twitter url (lightbox):' );
								console.log( url );
							}

							break;

						case 'google':
							// link var is taken from the 'general' caption above
							url = 'https://plus.google.com/share?url=' + encodeURIComponent( link ); /* does not appear encodeURIComponent is needed */

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'google url (lightbox):' );
								console.log( url );
							}

							break;

						case 'pinterest':

							if ( envira_album_id !== undefined && envira_album_id !== false ) {

								if ( obj.data.social_pinterest_title !== undefined && obj.data.social_pinterest_title == 'title' && current.title !== undefined ) {
									caption = (current.gallery_title).length > 0 ? current.gallery_title : current.title;
								} else if ( current.caption !== undefined ) {
									caption = (current.gallery_title).length > 0 ? current.gallery_title : current.caption;
								} else {
									caption = '';
								}

							} else {

								// check and see if we are overriding the caption with the title
								if ( obj.data.social_pinterest_title !== undefined && obj.data.social_pinterest_title == 'title' && current.title !== undefined ) {
									caption = current.title;
								} else if ( current.caption !== undefined ) {
									caption = current.caption;
								} else {
									caption = '';
								}

							}

							// convert some quotes here
							caption = caption.replace('&#8217;', "'");
							caption = caption.replace('&#8216;', "'");
							caption = caption.replace('&#8220;', '"');
							caption = caption.replace('&#8221;', '"');
							
							// caption, image, and link var is taken from the 'general' caption above
							url = 'http://pinterest.com/pin/create/button/?url=' + link + '&media=' + envira_gallery_item_full_size + '&description=' + encodeURI( caption );

							// If WP_DEBUG enabled, output error details.
							if ( envira_social.debug ) {
								console.log( 'pinterest url (lightbox):' );
								console.log( url );
							}

							break;

						case 'whatsapp':
							// caption, image, and link var is taken from the 'general' caption above
							url = 'whatsapp://send?text=' + encodeURIComponent( link );

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'whatsapp url (lightbox):' );
								console.log( url );
								console.log( link );
								console.log( encodeURIComponent( link ) );
							}

							break;

						case 'linkedin':

							/* var source = ( obj.social_linkedin_show_option_source !== undefined && obj.social_linkedin_show_option_source != '' && current.title !== undefined ) ? current.title : false; */

							// caption, image, and link var is taken from the 'general' caption above
							url = 'https://www.linkedin.com/shareArticle?mini=true&url=' + encodeURIComponent( link ); /* + '&title=' + encodeURIComponent( title ) + '&description=' + encodeURIComponent( caption ) + '&source=' + encodeURIComponent( source ); */

							width  = 800;
							height = 600;

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'linkedin url (lightbox):' );
								console.log( url );
							}

							break;

						case 'email':

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'envira_permalink (lightboxx):' );
								console.log( envira_permalink );
								console.log( envira_permalink.length );
							}

							if ( typeof envira_permalink !== "undefined" && envira_permalink !== null && envira_permalink.length > 0 ) {
								var cleaned_email_link = ( hash.length > 0 ) ? base_link + '?' + envira_permalink + hash : base_link + page_string + '?' + envira_permalink;
							} else {
								var cleaned_email_link = ( hash.length > 0 ) ? base_link + hash : base_link + page_string;
							}

							if ( caption == '' ) {
								caption = '&nbsp;';
							}

							var 	social_email_title 		  = false,
								social_email_message       = false,
								social_email_subject       = false;

							// assign title
							social_email_title = current.title !== undefined && current.title != '' ? current.title : false;

							// assign email body/message
							social_email_message = obj.data.social_email_message !== undefined && obj.data.social_email_message != '' ? obj.data.social_email_message : 'Photo: ' + envira_gallery_item_full_size + '\r\n\r\nURL: ' + cleaned_email_link,

							// assign subject
							social_email_subject = obj.data.social_email_subject !== undefined && obj.data.social_email_subject != '' ? obj.data.social_email_subject : false; 
							if ( envira_album_id !== undefined ) {
								social_email_subject = ( social_email_subject === false && current.gallery_title ) ? encodeURIComponent( current.gallery_title ) : social_email_subject;
								social_email_subject = ( current.gallery_title ) ? social_email_subject.replace( '{title}', current.gallery_title ) : social_email_subject.replace( '{title}', social_email_title );
							} else {
								social_email_subject = ( social_email_subject === false && current.title ) ? encodeURIComponent( current.title ) : social_email_subject;
								social_email_subject = social_email_subject.replace( '{title}', social_email_title );
							}
							social_email_subject = social_email_subject.replace( '{url}', cleaned_email_link );
							social_email_subject = social_email_subject.replace( '{photo_url}', envira_gallery_item_full_size );
							social_email_subject = social_email_subject.trim();

							// parse message
							social_email_message = social_email_message.trim();
							social_email_message = social_email_message.replace( '{title}', social_email_title );
							social_email_message = social_email_message.replace( '{url}', cleaned_email_link );
							social_email_message = social_email_message.replace( '{photo_url}', envira_gallery_item_full_size );
							social_email_message = encodeURIComponent( social_email_message );

							// caption, image, and link var is taken from the 'general' caption above
							url = 'mailto:?subject='+social_email_subject+'&body=' + social_email_message;

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'email url (lightbox):' );
								console.log( envira_gallery_item_full_size );
								console.log( url );
								console.log( link );
							}

							break;
					}

					// Open The Social Window
					// Depending on the network, we might do this via the social JS or open our own window, etc.
					if ( network === 'pinterest' ) {

						/* Using New Pinterest JS - PINIT.JS */

						if (typeof description === "undefined" && typeof caption !== "undefined") {
							description = caption;
						} else if (typeof description === "undefined") {
							// if there is no caption, then make the description blank instead of undefined
							// this helps make using the Pinterest API more stable
							description = '';
						}

						var ptype = null;

						if ( ptype !== undefined && ptype == "pin-all" ) {

							PinUtils.pinAny(
								{
									'media': envira_gallery_item_full_size,
									'description': description,
									'url': link
								}
							);

						} else {

							PinUtils.pinOne(
								{
									'media': envira_gallery_item_full_size,
									'description': description,
									'url': link
								}
							);

						}

					} else if ( network === 'facebook' ) {

						if ( hash.length > 0 ) {
							// var the_href = window.location.href.split('#')[0];
							var the_href = link;
						} else if ( link.length > 0 ) {
							var the_href = link;
						} else {
							var the_href = window.location.href;
						}

						// If WP_DEBUG enabled, output error details
						if ( envira_social.debug ) {
							console.log( 'sending facebook link:' );
							console.log( the_href );
						}

						the_href = $.trim( the_href );
						the_href = the_href.replace( /(\r\n|\n|\r)/gm,"" );

						if (typeof FB !== 'undefined') {

							FB.ui(
								{
									method: 'share',
									display: 'popup',
									href: the_href,
									title: title,
									description: facebook_text,
									caption: caption,
									picture: envira_gallery_item_full_size,
									hashtag: tags,
									quote: quote,
								}
							);

						} else {

							/* if FB isn't defined, use this as a backup */

							window.open( 'https://www.facebook.com/sharer/sharer.php?u=' + the_href, '_blank' );

						}

						clicked = true;

					} else if ( network === 'email' ) {

						window.location = url;

					} else if ( network === 'whatsapp' ) {

						if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test( navigator.userAgent ) ) {
							// var article = jQuery(this).attr("data-text");
							// var weburl = jQuery(this).attr("data-link");
							// var whats_app_message = encodeURIComponent(article)+" - "+encodeURIComponent(weburl);
							// var whatsapp_url = "whatsapp://send?text="+whats_app_message;
							window.location.href = url;
						} else {
							alert( 'You are not on mobile device.' );
						}

					} else {

						var enviraSocialWin = window.open( url, 'Share', 'width=' + width + ',height=' + height );

						// If WP_DEBUG enabled, output error details
						if ( envira_social.debug ) {
							console.log( 'url:' );
							console.log( url );
							console.log( encodeURIComponent( url ) );
						}

					}

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( 'link (lightbox):' + link );
					}

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( 'envira_gallery_item_id' );
						console.log( envira_gallery_item_id );
						console.log( 'full_size_image url (lightbox):' );
						console.log( envira_gallery_item_full_size );
						console.log( 'src' );
						console.log( envira_gallery_item_src );
						console.log( 'title' );
						console.log( envira_gallery_item_title );
						console.log( 'caption' );
						console.log( envira_gallery_item_caption );
						console.log( 'envira_permalink' );
						console.log( envira_permalink );
						console.log( '----' );
					}

					return false;

				}
			);

			/******* GALLERY *********/
			$( document ).on(
				'click',
				'.envira-gallery-wrap .envira-social-buttons a',
				function( e ) {

					e.preventDefault();

					// Get some attributes
					var hash               = window.location.hash,
					url                    = $( this ).attr( 'href' ),
					width                  = $( this ).parent().data( 'width' ),
					height                 = $( this ).parent().data( 'height' ),
					network                = $( this ).parent().data( 'network' ),
					deeplinking            = $( this ).parent().data( 'deeplinking' ),
					page_string            = '',
					envira_pagination_page = false; // If there's pagination, grab the current page

					if ( $( '.envira-pagination' ).length > 0 ) {
						// attempt to get page via URL querystring
						envira_pagination_page = envira_social_get_query_arg( 'page', $( this ).attr( 'href' ) );
						if ( ! envira_pagination_page ) {
							// attempt to get page via pagination bar
							envira_pagination_page = $( this ).closest( '.envira-gallery-wrap' ).find( '.envira-pagination' ).data( 'page' );
							if ( ! envira_pagination_page ) {
								// attempt to get page via fancybox data value, which should be added upon afterShow
								envira_pagination_page = $( 'img.envirabox-image' ).data( 'pagination-page' );
							}
						}
					}

					if ( envira_pagination_page ) {
						// now turn this into a string we can add after the url
						var page_string = envira_pagination_page + '/';
					}

					/* GALLERY LINKS */
					var  gallery_id  = $( this ).data( 'envira-gallery-id' ),
					gallery_item_id  = $( this ).data( 'envira-item-id' ),
					rand             = Math.floor( ( Math.random() * 10000 ) + 1 ),
					envira_permalink = envira_social_get_query_arg( 'envira', window.location.href );

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( 'detected gallery_id (gallery): ' + gallery_id );
						console.log( 'detected gallery_item_id (gallery): ' + gallery_item_id );
					}

					// Album ID might not exist, so let's check for this
					if (typeof $( this ).attr( 'data-envira-album-id' ) !== "undefined") {
						var album_id = $( this ).data( 'envira-album-id' );

						// If WP_DEBUG enabled, output error details
						if ( envira_social.debug ) {
							console.log( 'album_id is currently:' );
							console.log( album_id );
						}

					} else {
						var album_id = false;
					}

					if ( typeof envira_permalink !== "undefined" && envira_permalink !== null ) {
						envira_permalink = 'envira=' + envira_permalink;
					} else {
						envira_permalink = '';
					}

					// If WP_DEBUG enabled, output error details
					if ( envira_social.debug ) {
						console.log( 'envira_permalink:' + envira_permalink );
					}

					switch ( network ) {
						case 'facebook':

							var quote     = '',
							facebook_text = '',
							title         = '',
							tags          = '',
							caption       = '',
							image         = '';

							if (typeof $( this ).attr( 'data-envira-social-facebook-text' ) !== "undefined") {
								facebook_text = decodeURIComponent( $( this ).data( 'envira-social-facebook-text' ) );
								facebook_text = facebook_text.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating facebook_text:' );
									console.log( facebook_text );
								}
							}
							if (typeof $( this ).attr( 'data-envira-caption' ) !== "undefined") {
								var caption = decodeURIComponent( $( this ).data( 'envira-facebook-caption' ) );
								caption     = caption.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating caption:' );
									console.log( caption );
								}
							}
							if (typeof $( this ).attr( 'data-envira-facebook-quote' ) !== "undefined") {
								quote = decodeURIComponent( $( this ).data( 'envira-facebook-quote' ) );
								quote = quote.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating quote:' );
									console.log( quote );
								}
							}

							if (typeof $( this ).attr( 'data-envira-title' ) !== "undefined") {
								title = decodeURIComponent( $( this ).data( 'envira-title' ) );
								title = title.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating title:' );
									console.log( title );
								}
							}

							if (typeof $( this ).attr( 'data-envira-facebook-tags' ) !== "undefined") {
								tags = decodeURIComponent( $( this ).data( 'envira-facebook-tags' ) );
								tags = tags.replace( new RegExp( "\\+","g" ),' ' );
								// remove any dashes, since FB doesn't like them
								tags = tags.replace( /-/g, '' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating tags:' );
									console.log( tags );
								}
							}

							if (typeof $( this ).attr( 'data-envira-social-picture' ) !== "undefined") {
								image = decodeURIComponent( $( this ).data( 'envira-social-picture' ) );
								image = image.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating image:' );
									console.log( image );
								}
							}

							var link = ( hash.length > 0 ) ? window.location.href.split( '#' )[0] + encodeURIComponent( hash ) + '&envira_album_id=' + album_id + '&envira_social_gallery_id=' + gallery_id + '&envira_social_gallery_item_id=' + gallery_item_id : window.location.href.split( '?' )[0] + page_string + '?envira_album_id=' + album_id + '&envira_social_gallery_id=' + gallery_id + '&envira_social_gallery_item_id=' + gallery_item_id + '&rand=' + rand + '&' + envira_permalink;

							break;

						case 'pinterest':

							var  description = '',
							ptype            = 'pin-one', // always the default
							image            = '';

							if (typeof $( this ).attr( 'data-envira-social-pinterest-description' ) !== "undefined") {
								description = decodeURIComponent( $( this ).data( 'envira-social-pinterest-description' ) );
								description = description.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating pinterest description' );
									console.log( description );
								}
							}

							if (typeof $( this ).attr( 'data-envira-social-picture' ) !== "undefined") {
								image = decodeURIComponent( $( this ).data( 'envira-social-picture' ) );
								image = image.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating pinterest image' );
									console.log( image );
								}
							}

							if (typeof $( this ).attr( 'data-envira-pinterest-type' ) !== "undefined") {
								ptype = decodeURIComponent( $( this ).data( 'envira-pinterest-type' ) );
								ptype = ptype.replace( new RegExp( "\\+","g" ),' ' );
								// If WP_DEBUG enabled, output error details
								if ( envira_social.debug ) {
									console.log( 'updating pinterest ptype' );
									console.log( ptype );
								}
							}

							var link = ( hash.length > 0 ) ? window.location.href.split( '#' )[0] + encodeURIComponent( hash ) + '&envira_album_id=' + album_id + '&envira_social_gallery_id=' + gallery_id + '&envira_social_gallery_item_id=' + gallery_item_id + '&' + envira_permalink : window.location.href.split( '?' )[0] + '?envira_album_id=' + album_id + '&envira_social_gallery_id=' + gallery_id + '&envira_social_gallery_item_id=' + gallery_item_id + '&rand=' + rand + '&' + envira_permalink;

							// If WP_DEBUG enabled, output error details
							if ( envira_social.debug ) {
								console.log( 'pinterest link:' );
								console.log( image );
							}

						break;

					}

					// Open The Social Window
					// Depending on the network, we might do this via the social JS or open our own window, etc.
					if ( network === 'pinterest' ) {

						/* Using New Pinterest JS - PINIT.JS */

						if (typeof description === "undefined" && typeof caption !== "undefined") {
							description = caption;
						} else if (typeof description === "undefined") {
							// if there is no caption, then make the description blank instead of undefined
							// this helps make using the Pinterest API more stable
							description = '';
						}

						if ( ptype !== undefined && ptype == "pin-all" ) {

							PinUtils.pinAny(
								{
									'media': image,
									'description': description,
									'url': link
								}
							);

						} else {

							PinUtils.pinOne(
								{
									'media': image,
									'description': description,
									'url': link
								}
							);

						}

					} else if ( network === 'facebook' ) {

						if ( hash.length > 0 ) {
							// var the_href = window.location.href.split('#')[0];
							var the_href = link;
						} else if ( link.length > 0 ) {
							var the_href = link;
						} else {
							var the_href = window.location.href;
						}

						// If WP_DEBUG enabled, output error details
						if ( envira_social.debug ) {
							console.log( 'sending facebook link:' );
							console.log( the_href );
						}

						if (typeof FB !== 'undefined') {

							FB.ui(
								{
									method: 'share',
									display: 'popup',
									href: the_href,
									title: title,
									description: facebook_text,
									caption: caption,
									picture: image,
									hashtag: tags,
									quote: quote,
								}
							);

						} else {

							/* if FB isn't defined, use this as a backup */

							window.open( 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent( the_href ), '_blank' );

						}


					} else if ( network === 'email' ) {

						window.location = url;

					} else {

						var enviraSocialWin = window.open( url, 'Share', 'width=' + width + ',height=' + height );

						// If WP_DEBUG enabled, output error details
						if ( envira_social.debug ) {
							console.log( 'url:' );
							console.log( url );
							console.log( encodeURIComponent( url ) );
						}

					}

					return false;
				}
			);

			// Gallery: Show Sharing Buttons on Image Hover
			//
			// New: If this is a "touch" device, then it's likely we don't want to do this since it will require
			// another "touch" to get to the gallery, especially if there are no social items
			//
			$( 'div.envira-gallery-item-inner' ).each(
				function() {
					if ( $( this ).find( '.envira-social-buttons .envira-social-network' ).length === 0 ) {
						$( this ).find( 'div.envira-social-buttons' ).remove();
					}
				}
			);

			// If the envira_social_sharing_close=1 key/value parameter exists, close the window
			if ( location.href.search( 'envira_social_sharing_close' ) > -1 ) {
				window.close();
			}

		}
	);

	document.getElementsByClassName( 'button-facebook' ).onclick = function() {
		FB.ui(
			{
				method: 'share',
				display: 'popup',
				href: 'https://developers.facebook.com/docs/',
			},
			function(response){}
		);
	}

	  /**
	   * Returns a URL parameter by name
	   *
	   * @since 1.1.7
	   *
	   * @param   string  name
	   * @param   string  url
	   * @return  string  value
	   */
	function envira_social_get_query_arg( name, url ) {

		name        = name.replace( /[\[\]]/g, "\\$&" );
		var regex   = new RegExp( "[?&]" + name + "(=([^&#]*)|&|#|$)" ),
			results = regex.exec( url );

		if ( ! results ) {
			return null;
		}
		if ( ! results[2] ) {
			return '';
		}

		return decodeURIComponent( results[2].replace( /\+/g, " " ) );

	}

	  /**
	   * "Cleans" a base_link so vars aren't repeated, etc.
	   * Uses envira_remove_URL_parameter()
	   *
	   * @since 1.1.7
	   *
	   * @param   string  base_link
	   * @return  string  value
	   */
	function envira_clean_base_link( base_link ) {
		var arr = ['doing_wp_cron', 'envira_social_gallery_id', 'envira_social_gallery_item_id', 'rand','envira', 'envira_album', 'album_id' ];

		 base_link = envira_remove_URL_parameter( base_link, 'doing_wp_cron' );
		 base_link = envira_remove_URL_parameter( base_link, 'envira_social_gallery_id' );
		 base_link = envira_remove_URL_parameter( base_link, 'envira_social_gallery_item_id' );
		 base_link = envira_remove_URL_parameter( base_link, 'rand' );
		 base_link = envira_remove_URL_parameter( base_link, 'envira' );
		 base_link = envira_remove_URL_parameter( base_link, 'envira_album' );
		 base_link = envira_remove_URL_parameter( base_link, 'album_id' );

		 return base_link;
	}

	  /**
	   * Removes parameters in URLs
	   *
	   * @since 1.1.7
	   *
	   * @param   string  url
	   * @param   string  parameter
	   * @return  string  value
	   */
	function envira_remove_URL_parameter(url, parameter) {

		// prefer to use l.search if you have a location/link object
		var urlparts = url.split( '?' );
		if (urlparts.length >= 2) {

			var prefix = encodeURIComponent( parameter ) + '=';
			var pars   = urlparts[1].split( /[&;]/g );

			// reverse iteration as may be destructive
			for (var i = pars.length; i-- > 0;) {
				// idiom for string.startsWith
				if (pars[i].lastIndexOf( prefix, 0 ) !== -1) {
					pars.splice( i, 1 );
				}
			}

			url = urlparts[0] + (pars.length > 0 ? '?' + pars.join( '&' ) : "");
			return url;
		} else {
			return url;
		}
	}

})( jQuery , window, document );
