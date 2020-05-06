; (function ($, window, document, envira_gallery) {

	// FANCYBOX
	var envira_playing = false;

	$(document).on(
		'envirabox_api_after_show',
		function (e, obj, instance, current) {

			instance.SlideShow.clear(); // clear/reset timer because user can click navigation which messes up things

			if (instance.SlideShow.isActive === true) {

				envira_playing = true;

			}

			if (obj.get_config('slideshow_hover') === 1) {

				$('.envirabox-inner img.envirabox-image, .envirabox-inner iframe').on(
					{
						mouseenter: function () {

							if (instance.SlideShow.isActive === true) {
								/* it was on, so remember that */
								envira_playing = true;
							} else {
								envira_playing = false;
							}
							instance.SlideShow.stop();
						},
						mouseleave: function () {
							/* was envira playing when you entered the area? if so, restore */
							if (instance.SlideShow.isActive === false) {
								instance.SlideShow.start();
							}
						}
					}
				);

			}

		}
	);

})(jQuery, window, document, envira_gallery);
