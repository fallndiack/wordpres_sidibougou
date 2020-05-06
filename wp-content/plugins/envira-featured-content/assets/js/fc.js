;/* ==========================================================
 * fc.js
 * http://enviragallery.com/
 * ==========================================================
 * Copyright 2015 Envira Team.
 *
 * Licensed under the GPL License, Version 2.0 or later (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */(function($){
	$(
		function(){

			function EnviraDateTime() {

				if ( typeof envira_gallery_schedule === "undefined" || envira_gallery_schedule.envira_format_date === "undefined" || envira_gallery_schedule.envira_format_date === "undefined" ) {
					return;
				}

				$( '#envira-config-fc-start-date' ).datetimepicker(
					{
						format: 'X',
						formatTime: envira_gallery_schedule.envira_format_time,
						formatDate: envira_gallery_schedule.envira_format_date,
						lang: 'en',
						inline: true,
					}
				);
		
				$( '#envira-config-fc-end-date' ).datetimepicker(
					{
						format: 'X',
						formatTime: envira_gallery_schedule.envira_format_time,
						formatDate: envira_gallery_schedule.envira_format_date,
						lang: 'en',
						inline: true,
					}
				);
		
			}

			// Trigger the enviraGalleryPreview when any setting is changed
			$( document ).on(
				'change',
				'#envira-fc select, #envira-fc input',
				function( e ) {

					$( document ).trigger( 'enviraGalleryPreview' );

				}
			);

			// Flag for determing if the user has selected anything or not yet since page load.
			var chosen_term = chosen_post = false;

			// Initialize JS.
			enviraFcInit();

			// Initialize JS when the Featured Content slider type is selected.
			$( document ).on(
				'enviraGalleryType',
				function(e, data){
					if ( data.type && 'fc' == data.type ) {
						enviraFcInit();
					}
				}
			);

			// Callback function to initialize the Featured Content JS.
			function enviraFcInit() {
				// Initialize chosen on specific select boxes.
				$( '.envira-fc-chosen' ).chosen();

				// Run conditionals.
				enviraFcConditionals();

				// Show/hide the inclusion groups (and even the inclusion step itself if certain conditions are met).
				var envira_fc_post_type_val = $( '#envira-config-fc-post-type' ).val();
				if ( envira_fc_post_type_val ) {
					$( '#envira-config-fc-inc-ex option:selected' ).trigger( 'change' ).trigger( 'chosen:updated' );

					// Conditionally refresh the posts selection box.
					if ( ! chosen_post ) {
						chosen_post = true;
						enviraFcRefreshPostsCond( envira_fc_post_type_val );
					} else {
						enviraFcRefreshPosts( envira_fc_post_type_val );
					}

					// Conditionally refresh the terms selection box.
					if ( ! chosen_term ) {
						chosen_term = true;
						enviraFcRefreshTermsCond( envira_fc_post_type_val );
					} else {
						enviraFcRefreshTerms( envira_fc_post_type_val );
					}
				} else {
					// Default to "post" and trigger events to make sure Chosen functions correctly.
					$( '#envira-config-fc-post-type option[value="post"]' ).attr( 'selected', 'selected' ).trigger( 'change' ).trigger( 'chosen:updated' );

					// Conditionally refresh the posts selection box.
					if ( ! chosen_post ) {
						chosen_post = true;
						enviraFcRefreshPostsCond( envira_fc_post_type_val );
					}

					// Conditionally refresh the terms selection box.
					if ( ! chosen_term ) {
						chosen_term = true;
						enviraFcRefreshTermsCond( envira_fc_post_type_val );
					}
				}

				// Use ajax to show/hide terms related to the currently selected post type(s) on value change.
				$( '#envira-config-fc-post-type' ).chosen().change(
					function(){
						var post_type_val = $( '#envira-config-fc-post-type' ).val();
						if ( post_type_val ) {
							enviraFcRefreshPosts( post_type_val );
							enviraFcRefreshTerms( post_type_val );
						}
					}
				);

		
				// Now implement datetimepicker
				$( document ).ready(
					function () {
			
						$.datetimepicker.setDateFormatter(
							{
								parseDate: function (date, format) {
									var d = moment( date, format );
									return d.isValid() ? d.toDate() : false;
								},
								formatDate: function (date, format) {
									return moment( date ).format( format );
								},
							}
						);
			
						EnviraDateTime();
			
					}
				);
			
				$( document ).on(
					'enviraInsert enviraRenderMeta enviraEditOpen',
					function () {

						if ( typeof envira_gallery_schedule === "undefined" || envira_gallery_schedule.envira_format_date === "undefined" || envira_gallery_schedule.envira_format_date === "undefined" ) {
							return;
						}
			
						$( '#schedule_meta_start' ).datetimepicker(
							{
								format: 'X',
								formatTime: envira_gallery_schedule.envira_format_time,
								formatDate: envira_gallery_schedule.envira_format_date,
								lang: 'en',
								fixed: false,
								inline: true,
							}
						);
			
						$( '#schedule_meta_end' ).datetimepicker(
							{
								format: 'X',
								formatTime: envira_gallery_schedule.envira_format_time,
								formatDate: envira_gallery_schedule.envira_format_date,
								lang: 'en',
								fixed: false,
								inline: true,
							}
						);
			
					}
				);

			}

			// Callback function to show/hide conditional elements.
			function enviraFcConditionals() {
				// Show/hide post title linking if the post title is to be output.
				if ( $( '#envira-config-fc-post-title' ).is( ':checked' ) ) {
					$( '#envira-config-fc-post-title-link-box' ).show();
				} else {
					$( '#envira-config-fc-post-title-link-box' ).hide();
				}
				$( document ).on(
					'change',
					'#envira-config-fc-post-title',
					function(){
						if ( $( this ).is( ':checked' ) ) {
							$( '#envira-config-fc-post-title-link-box' ).fadeIn();
						} else {
							$( '#envira-config-fc-post-title-link-box' ).fadeOut();
						}
					}
				);

				// Show/hide content length and ellipses box if content is to be output.
				if ( 'post_content' == $( '#envira-config-fc-content-type' ).val() ) {
					$( '#envira-config-fc-content-length-box, #envira-config-fc-content-ellipses-box, #envira-content-fc-content-html' ).show();
				} else {
					$( '#envira-config-fc-content-length-box, #envira-config-fc-content-ellipses-box, #envira-content-fc-content-html' ).hide();
				}
				$( document ).on(
					'change',
					'#envira-config-fc-content-type',
					function(){
						if ( 'post_content' == $( this ).val() ) {
							$( '#envira-config-fc-content-length-box, #envira-config-fc-content-ellipses-box, #envira-content-fc-content-html' ).fadeIn();
						} else {
							$( '#envira-config-fc-content-length-box, #envira-config-fc-content-ellipses-box, #envira-content-fc-content-html' ).fadeOut();
						}
					}
				);

				// Show/hide read more text if read more box is checked.
				if ( $( '#envira-config-fc-read-more' ).is( ':checked' ) ) {
					$( '#envira-config-fc-read-more-text-box' ).show();
				} else {
					$( '#envira-config-fc-read-more-text-box' ).hide();
				}
				$( document ).on(
					'change',
					'#envira-config-fc-read-more',
					function(){
						if ( $( this ).is( ':checked' ) ) {
							$( '#envira-config-fc-read-more-text-box' ).fadeIn();
						} else {
							$( '#envira-config-fc-read-more-text-box' ).fadeOut();
						}
					}
				);

				// Show/hide post title linking if the post title is to be output.
				if ( $( '#envira-config-fc-date-define-dropdown' ).val() == '' ) {
					$( '#envira-config-fc-start-date-box' ).hide();
					$( '#envira-config-fc-end-date-box' ).hide();
					$( '#envira-config-fc-age-box' ).hide();
				} else if ( $( '#envira-config-fc-date-define-dropdown' ).val() === 'datetime' ) {
					$( '#envira-config-fc-start-date-box' ).show();
					$( '#envira-config-fc-end-date-box' ).show();
					$( '#envira-config-fc-age-box' ).hide();
				} else if ( $( '#envira-config-fc-date-define-dropdown' ).val() === 'hours' ) {
					$( '#envira-config-fc-start-date-box' ).hide();
					$( '#envira-config-fc-end-date-box' ).hide();
					$( '#envira-config-fc-age-box' ).show();
				}
				$( document ).on(
					'change',
					'select#envira-config-fc-date-define-dropdown',
					function(){
						if ( $( this ).val() === '' ) {
							$( '#envira-config-fc-start-date-box' ).hide();
							$( '#envira-config-fc-end-date-box' ).hide();
							$( '#envira-config-fc-age-box' ).hide();
						} else if ( $( this ).val() === 'datetime' ) {
							$( '#envira-config-fc-start-date-box' ).show();
							$( '#envira-config-fc-end-date-box' ).show();
							$( '#envira-config-fc-age-box' ).hide();
						} else if ( $( this ).val() === 'hours' ) {
							$( '#envira-config-fc-start-date-box' ).hide();
							$( '#envira-config-fc-end-date-box' ).hide();
							$( '#envira-config-fc-age-box' ).show();
						}
					}
				);
				
			}

			// Callback function to process refreshing of terms.
			function enviraFcRefreshTerms(posttype){
				if ( ! chosen_term ) {
					chosen_term = true;
					return;
				}

				// Set the posttype array if none have been selected.
				if ( ! posttype ) {
					posttype = ['post'];
				}

				// Output the loading icon.
				$( '#envira_config_fc_terms_chosen' ).after( '<span class="spinner envira-spinner" style="display:inline-block;"></span>' );

				var opts = {
					type: 'post',
					url: envira_gallery_metabox.ajax,
					async: true,
					cache: false,
					dataType: 'json',
					data: {
						action:    'envira_featured_content_refresh_terms',
						nonce:     envira_fc_metabox.term_nonce,
						post_type: posttype,
						post_id:   envira_gallery_metabox.id
					},
					success: function(json){
						if ( json && json.error ) {
							$( '.envira-spinner' ).remove();
							$( '#envira-config-fc-terms option:selected' ).removeAttr( 'selected' ).trigger( 'change' ).trigger( 'chosen:updated' );
							$( '#envira-config-fc-terms-box' ).fadeOut();
							$( '#envira-config-fc-terms-relation-box' ).fadeOut();
						} else {
							$( '#envira-config-fc-terms-box' ).fadeIn(
								'normal',
								function() {
									$( '#envira-config-fc-terms-relation-box' ).fadeIn();
									$( '.envira-spinner' ).remove();
									$( '#envira-config-fc-terms' ).empty().append( json ).trigger( 'change' ).trigger( 'chosen:updated' );
								}
							);
						}
					},
					error: function(xhr){
						$( '.envira-spinner' ).remove();
					}
				}
				$.ajax( opts );
			}

			function enviraFcRefreshTermsCond(posttype){
				var opts = {
					type: 'post',
					url: envira_gallery_metabox.ajax,
					async: true,
					cache: false,
					dataType: 'json',
					data: {
						action:    'envira_featured_content_refresh_terms',
						nonce:     envira_fc_metabox.term_nonce,
						post_type: posttype,
						post_id:   envira_gallery_metabox.id
					},
					success: function(json){
						// We only need to handle errors if no taxonomy is shared between the post types.
						if ( json && json.error ) {
							$( '#envira-config-fc-terms-box' ).hide();
							$( '#envira-config-fc-terms-relation-box' ).hide();
							return;
						} else {
							/** Grab all currently chosen items and repopulate them */
							$( '#envira-config-fc-terms-box' ).show();
							$( '#envira-config-fc-terms-relation-box' ).show();
							$( '#envira-config-fc-terms' ).empty().append( json );
							$( '#envira_fc_terms_chzn .chzn-results li.result-selected' ).each(
								function(){
									var el = $( this );
									$( '#envira-config-fc-terms option' ).each(
										function(){
											if ( $( this ).text() == el.text() ) {
												$( this ).attr( 'selected', 'selected' );
											}
										}
									);
								}
							);
							$( '#envira-config-fc-terms' ).trigger( 'change' ).trigger( 'chosen:updated' );
						}
					},
					error: function(xhr){
					}
				}
				$.ajax( opts );
			}

			function enviraFcRefreshTermsCondMulti(posttype){
				var opts = {
					type: 'post',
					url: envira_gallery_metabox.ajax,
					async: true,
					cache: false,
					dataType: 'json',
					data: {
						action:    'envira_featured_content_refresh_terms',
						nonce:     envira_fc_metabox.term_nonce,
						post_type: posttype,
						post_id:   envira_gallery_metabox.id
					},
					success: function(json){
						if ( json && json.error ) {
							$( '.envira-spinner' ).remove();
							$( '#envira-config-fc-terms option:selected' ).removeAttr( 'selected' ).trigger( 'change' ).trigger( 'chosen:updated' );
							$( '#envira-config-fc-terms-box' ).fadeOut();
							$( '#envira-config-fc-terms-relation-box' ).fadeOut();
						} else {
							$( '#envira-config-fc-terms-box' ).fadeIn(
								'normal',
								function(){
									$( '#envira-config-fc-terms-relation-box' ).fadeIn();
									$( '.envira-spinner' ).remove();
									$( '#envira-config-fc-terms' ).empty().append( json ).trigger( 'change' ).trigger( 'chosen:updated' );
								}
							);
						}
					},
					error: function(xhr){
					}
				}
				$.ajax( opts );
			}

			function enviraFcRefreshPosts(posttype){
				if ( ! chosen_post ) {
					chosen_post = true;
					return;
				}

				// Set type to post in array if there is none selected.
				if ( ! posttype ) {
					posttype = ['post'];
				}

				// Output the loading icon.
				$( '#envira_config_fc_post_type_chosen' ).after( '<span class="spinner envira-spinner" style="display:inline-block;"></span>' );

				var opts = {
					type: 'post',
					url: envira_gallery_metabox.ajax,
					async: true,
					cache: false,
					dataType: 'json',
					data: {
						action:    'envira_featured_content_refresh_posts',
						nonce:     envira_fc_metabox.refresh_nonce,
						post_type: posttype,
						post_id:   envira_gallery_metabox.id
					},
					success: function(json){
						if ( json && json.error ) {
							$( '.envira-spinner' ).remove();
							$( '#envira-config-fc-inc-ex option:selected' ).removeAttr( 'selected' ).trigger( 'change' ).trigger( 'chosen:updated' );
							$( '#envira-config-fc-inc-ex-box' ).fadeOut();
						} else {
							$( '#envira-config-fc-inc-ex-box' ).fadeIn(
								'normal',
								function(){
									$( '.envira-spinner' ).remove();
									$( '#envira-config-fc-inc-ex' ).empty().append( json ).trigger( 'change' ).trigger( 'chosen:updated' );
								}
							);
						}
					},
					error: function(xhr){
						$( '.envira-spinner' ).remove();
					}
				}

				$.ajax( opts );
			}

			function enviraFcRefreshPostsCond(posttype){
				var data = {
					action:    'envira_featured_content_refresh_posts',
					nonce:     envira_fc_metabox.refresh_nonce,
					post_type: posttype,
					post_id:   envira_gallery_metabox.id
				};
				$.post(
					envira_gallery_metabox.ajax,
					data,
					function(json){
						// We only need to update the list of posts to chose from based on the user selection on page load.
						if ( json && json.error ) {
							$( '#envira-config-fc-inc-ex-box' ).hide();
							return;
						} else {
							// Grab all currently chosen items and repopulate them.
							$( '#envira-config-fc-inc-ex-box' ).show();
							$( '#envira-config-fc-inc-ex' ).empty().append( json );
							$( '#envira_fc_include_exclude_chzn .chzn-results li.result-selected' ).each(
								function(){
									var el = $( this );
									$( '#envira-config-fc-inc-ex option' ).each(
										function(){
											if ( $( this ).text() == el.text() ) {
												$( this ).attr( 'selected', 'selected' );
											}
										}
									);
								}
							);
							$( '#envira-config-fc-inc-ex' ).trigger( 'change' ).trigger( 'chosen:updated' );
						}
					},
					'json'
				);
			}
		}
	);
}(jQuery));
