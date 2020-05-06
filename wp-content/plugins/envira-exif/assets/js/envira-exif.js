jQuery(document).ready(
	function ($) {

		function envira_is_mobile_exif() {
			var isMobile = false; // initiate as false
			// device detection
			if (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
				|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0, 4))) {
				isMobile = true;
			}
			return isMobile;
		}

		function envira_is_valid_date(d) {
			return d instanceof Date && !isNaN(d);
		}

		/******* LIGHTBOX *********/

		$(document).on(
			'envirabox_api_after_show',
			function (e, obj, instance, current) {

				if (envira_is_mobile_exif() && obj.get_config('mobile_exif_lightbox') === 0) {
					return;
				}

				if (obj.get_config('exif_lightbox') === 0) {
					return;
				}

				$('div.envirabox-exif').remove();

				var envira_lb_image = $('.envirabox-slide--current img.envirabox-image').attr('src'),
					envira_gallery_item_id = false,
					envira_gallery_item_exif_data = false,
					envira_is_album = obj.images ? false : true;

				// determine the image id
				if (obj.images) {

					for (prop in obj.images) {
						if (obj.images[prop].src == envira_lb_image) {
							envira_gallery_item_id = prop;
							envira_gallery_item_exif_data = obj.images[prop].image_meta;
						} else if (obj.images[prop].link == envira_lb_image) {
							envira_gallery_item_id = prop;
							envira_gallery_item_exif_data = obj.images[prop].image_meta;
						}
					}

				} else if (obj.galleries) {

					if (current.image_meta !== undefined) {
						envira_gallery_item_id = current.id;
						envira_gallery_item_exif_data = current.image_meta;
					}

				}

				if (envira_gallery_item_id === undefined || envira_gallery_item_id === false) {
					return;
				}

				/* define css class */

				var exif_lightbox_position = obj.get_config('exif_lightbox_position'),
					exif_lightbox_outside = obj.get_config('exif_lightbox_outside'),
					css_class = (exif_lightbox_position !== '') ? 'position-' + exif_lightbox_position : 'position-top-left';

				css_class = css_class + (exif_lightbox_outside ? ' outside' : '');

				if (typeof envira_gallery_item_exif_data != 'undefined') {
					var envira_html_start = '<div class="envira-exif ' + css_class + '">',
						envira_html = '',
						envira_html_end = '';

					if (typeof envira_gallery_item_exif_data.Make !== 'undefined' || typeof envira_gallery_item_exif_data.Model !== 'undefined') {
						envira_html += '<div class="model"><span>';
						if (typeof envira_gallery_item_exif_data.Make !== 'undefined' && obj.get_config('exif_lightbox_make')) {
							envira_html += envira_gallery_item_exif_data.Make;
						}
						if (typeof envira_gallery_item_exif_data.Model !== 'undefined' && obj.get_config('exif_lightbox_model')) {
							envira_html += ' ' + envira_gallery_item_exif_data.Model;
						}
						envira_html += '</span></div>';
					}
					if (typeof envira_gallery_item_exif_data.Aperture !== 'undefined' && obj.get_config('exif_lightbox_aperture')) {
						envira_html += '<div class="aperture"><span>f/' + envira_gallery_item_exif_data.Aperture + '</span></div>';
					}
					if (typeof envira_gallery_item_exif_data.ShutterSpeed !== 'undefined' && obj.get_config('exif_lightbox_shutter_speed')) {
						envira_html += '<div class="shutter-speed"><span>' + envira_gallery_item_exif_data.ShutterSpeed + '</span></div>';
					}
					if (typeof envira_gallery_item_exif_data.FocalLength !== 'undefined' && obj.get_config('exif_lightbox_focal_length')) {
						envira_html += '<div class="focal-length"><span>' + envira_gallery_item_exif_data.FocalLength + '</span></div>';
					}
					if (typeof envira_gallery_item_exif_data.iso !== 'undefined' && obj.get_config('exif_lightbox_iso')) {
						envira_html += '<div class="iso"><span>' + envira_gallery_item_exif_data.iso + '</span></div>';
					}
					if (typeof envira_gallery_item_exif_data.CaptureTime !== 'undefined' && obj.get_config('exif_lightbox_capture_time')) {
						if (current.captureTimeDisplay !== undefined && current.captureTimeDisplay !== false) {
							envira_html += '<div class="capture-time"><span>' + current.captureTimeDisplay + '</span></div>';
						} else if (envira_is_album === true && typeof obj.get_config('exif_lightbox_capture_time_format') !== undefined && obj.get_config('exif_lightbox_capture_time_format') !== false) {
							var exifDate = new Date(envira_gallery_item_exif_data.CaptureTime * 1000);
							if (envira_is_valid_date(exifDate)) {
								envira_html += '<div class="capture-time"><span>' + exifDate.format(obj.get_config('exif_lightbox_capture_time_format')) + '</span></div>';
							}
						}
					}
					envira_html_end = '</div>';

					if (envira_html !== '') {
						// Move the social div, assign CSS
						$('.envirabox-stage .envirabox-slide--current .envirabox-image-wrap').prepend('<div class="envirabox-exif" />');
						$('div.envirabox-exif').html(envira_html_start + envira_html + envira_html_end).fadeIn(300);
					}
				}

			}
		);

	}
);



/* eslint no-extend-native: 0 */

(function () {
	// Defining locale
	Date.shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
	Date.longMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
	Date.shortDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
	Date.longDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
	// Defining patterns
	var replaceChars = {
		// Day
		d: function () { var d = this.getDate(); return (d < 10 ? '0' : '') + d },
		D: function () { return Date.shortDays[this.getDay()] },
		j: function () { return this.getDate() },
		l: function () { return Date.longDays[this.getDay()] },
		N: function () { var N = this.getDay(); return (N === 0 ? 7 : N) },
		S: function () { var S = this.getDate(); return (S % 10 === 1 && S !== 11 ? 'st' : (S % 10 === 2 && S !== 12 ? 'nd' : (S % 10 === 3 && S !== 13 ? 'rd' : 'th'))) },
		w: function () { return this.getDay() },
		z: function () { var d = new Date(this.getFullYear(), 0, 1); return Math.ceil((this - d) / 86400000) },
		// Week
		W: function () {
			var target = new Date(this.valueOf())
			var dayNr = (this.getDay() + 6) % 7
			target.setDate(target.getDate() - dayNr + 3)
			var firstThursday = target.valueOf()
			target.setMonth(0, 1)
			if (target.getDay() !== 4) {
				target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7)
			}
			var retVal = 1 + Math.ceil((firstThursday - target) / 604800000)

			return (retVal < 10 ? '0' + retVal : retVal)
		},
		// Month
		F: function () { return Date.longMonths[this.getMonth()] },
		m: function () { var m = this.getMonth(); return (m < 9 ? '0' : '') + (m + 1) },
		M: function () { return Date.shortMonths[this.getMonth()] },
		n: function () { return this.getMonth() + 1 },
		t: function () {
			var year = this.getFullYear()
			var nextMonth = this.getMonth() + 1
			if (nextMonth === 12) {
				year = year++
				nextMonth = 0
			}
			return new Date(year, nextMonth, 0).getDate()
		},
		// Year
		L: function () { var L = this.getFullYear(); return (L % 400 === 0 || (L % 100 !== 0 && L % 4 === 0)) },
		o: function () { var d = new Date(this.valueOf()); d.setDate(d.getDate() - ((this.getDay() + 6) % 7) + 3); return d.getFullYear() },
		Y: function () { return this.getFullYear() },
		y: function () { return ('' + this.getFullYear()).substr(2) },
		// Time
		a: function () { return this.getHours() < 12 ? 'am' : 'pm' },
		A: function () { return this.getHours() < 12 ? 'AM' : 'PM' },
		B: function () { return Math.floor((((this.getUTCHours() + 1) % 24) + this.getUTCMinutes() / 60 + this.getUTCSeconds() / 3600) * 1000 / 24) },
		g: function () { return this.getHours() % 12 || 12 },
		G: function () { return this.getHours() },
		h: function () { var h = this.getHours(); return ((h % 12 || 12) < 10 ? '0' : '') + (h % 12 || 12) },
		H: function () { var H = this.getHours(); return (H < 10 ? '0' : '') + H },
		i: function () { var i = this.getMinutes(); return (i < 10 ? '0' : '') + i },
		s: function () { var s = this.getSeconds(); return (s < 10 ? '0' : '') + s },
		v: function () { var v = this.getMilliseconds(); return (v < 10 ? '00' : (v < 100 ? '0' : '')) + v },
		// Timezone
		e: function () { return Intl.DateTimeFormat().resolvedOptions().timeZone },
		I: function () {
			var DST = null
			for (var i = 0; i < 12; ++i) {
				var d = new Date(this.getFullYear(), i, 1)
				var offset = d.getTimezoneOffset()

				if (DST === null) DST = offset
				else if (offset < DST) { DST = offset; break } else if (offset > DST) break
			}
			return (this.getTimezoneOffset() === DST) | 0
		},
		O: function () { var O = this.getTimezoneOffset(); return (-O < 0 ? '-' : '+') + (Math.abs(O / 60) < 10 ? '0' : '') + Math.floor(Math.abs(O / 60)) + (Math.abs(O % 60) === 0 ? '00' : ((Math.abs(O % 60) < 10 ? '0' : '')) + (Math.abs(O % 60))) },
		P: function () { var P = this.getTimezoneOffset(); return (-P < 0 ? '-' : '+') + (Math.abs(P / 60) < 10 ? '0' : '') + Math.floor(Math.abs(P / 60)) + ':' + (Math.abs(P % 60) === 0 ? '00' : ((Math.abs(P % 60) < 10 ? '0' : '')) + (Math.abs(P % 60))) },
		T: function () { var tz = this.toLocaleTimeString(navigator.language, { timeZoneName: 'short' }).split(' '); return tz[tz.length - 1] },
		Z: function () { return -this.getTimezoneOffset() * 60 },
		// Full Date/Time
		c: function () { return this.format('Y-m-d\\TH:i:sP') },
		r: function () { return this.toString() },
		U: function () { return Math.floor(this.getTime() / 1000) }
	}

	// Simulates PHP's date function
	Date.prototype.format = function (format) {
		var date = this
		return format.replace(/(\\?)(.)/g, function (_, esc, chr) {
			return (esc === '' && replaceChars[chr]) ? replaceChars[chr].call(date) : chr
		})
	}
}).call(this)