;/**
 * Envira Schedule Library.
 *
 * @author Travis Smith
 * @author Envira Team
 */
/**
 * Envira Schedule Object.
 */ (function ($) {

	'use strict';

	function EnviraDateTime() {

		$( '#envira-config-schedule-start' ).datetimepicker(
			{
				format: 'X',
				formatTime: envira_gallery_schedule.envira_format_time,
				formatDate: envira_gallery_schedule.envira_format_date,
				lang: 'en',
				inline: true,
			}
		);

		$( '#envira-config-schedule-end' ).datetimepicker(
			{
				format: 'X',
				formatTime: envira_gallery_schedule.envira_format_time,
				formatDate: envira_gallery_schedule.envira_format_date,
				lang: 'en',
				inline: true,
			}
		);

	}

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

})( jQuery );
