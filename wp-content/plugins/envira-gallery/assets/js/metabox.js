jQuery(document).ready(function($) {
	// Image Size: Random
	// conditional-fields doesn't support multiple conditions, so we manually show/hide
	// the Random Image Sizes option depending on the Image Size value
	$('select[name="_envira_gallery[image_size]"]').on('change', function() {
		if ($(this).val() === 'envira_gallery_random') {
			$('tr#envira-config-image-sizes-random-box').show();
		} else {
			$('tr#envira-config-image-sizes-random-box').hide();
		}
	});

	// Run the above conditions on load.
	$('select[name="_envira_gallery[image_size]"]').trigger('change');

	$('#envira-gallery.postbox').on('dragstart', function(e) {
		return false;
	});
});
