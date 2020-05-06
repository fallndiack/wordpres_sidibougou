; (function ($, window, document) {

    var socialElement = false,
        obj = null,
        instance = null,
        current = null,
        envira_deeplinking_slug = (envira_gallery_deeplinking.slug !== undefined) ? envira_gallery_deeplinking.slug : 'enviragallery';

    $(document).on('envira_loaded', function (e, envira_galleries, envira_links) {
        envira_galleries_temp = envira_galleries;
        if (envira_galleries !== undefined) {
            for (prop in envira_galleries) {
                if (parseInt(envira_galleries[prop].data.deeplinking) === 1) {
                    var display_all_images = (envira_galleries[prop].data.pagination_lightbox_display_all_images === 1) ? true : false;
                    //$('#envira-gallery-wrap-12335 #envira-gallery-item-12328 .envira-gallery-link' ).trigger('click');
                    envira_deeplinking(envira_galleries, display_all_images);
                    break;
                }
            }
        }
    });

    /******* GENERAL STUFF ********/

    /**
    * Fired when a hash is detected in the URL bar
    * If the Lightbox is open, jumps to the chosen image
    */
    function envira_deeplinking(envira_galleries, display_all_images) {

        // Get hash
        var hash = window.location.hash.substr(1);

        // Check if a hash exists, and it's an Envira Gallery Hash
        if (!hash || hash.length == 0 || hash.indexOf(envira_deeplinking_slug) == -1) {
            return;
        }

        var params = hash.split('&'); //break up other url params
        result = params[0].split('-'),
            gallery = result[0].split('!')[1],
            gallery_id = gallery.split(envira_deeplinking_slug)[1],
            image_id = result[1],
            obj = false,
            $images = false,
            $suffix_id = '',
            gallery_data = {};

        if ($.isEmptyObject(envira_galleries)) {
            envira_galleries = {};
            $envira_galleries = $('div').find('[data-envira-id="' + gallery_id + '"]').data('gallery-config');
            $envira_images = $('div').find('[data-envira-id="' + gallery_id + '"]').data('gallery-images');
            $envira_lightbox = $('div').find('[data-envira-id="' + gallery_id + '"]').data('lightbox-theme');
            gallery_data = $envira_galleries;
            $images = $('.envira-gallery-' + gallery_id);
        } else if (envira_galleries !== undefined) {
            obj = envira_galleries[gallery_id] ? envira_galleries[gallery_id] : false;
            gallery_data = envira_galleries[gallery_id].data !== undefined ? envira_galleries[gallery_id].data : false;
            $images = $('.envira-gallery-' + gallery_id);

            var super_index = 0;
            for (prop in envira_galleries[gallery_id].images) {
                if (Number(envira_galleries[gallery_id].images[prop].id) === Number(image_id)) {
                    var lightbox_images = [];
                    $.each(obj.images, function (i) {
                        /* check and see if this is a video, and if so override the src so it's using the video embed link */
                        if (this.video.embed_url !== undefined) {
                            this.src = this.video.embed_url;
                        }
                        lightbox_images.push(this);
                    });
                    var instance = $.envirabox.open(lightbox_images, obj.lightbox_options, super_index);
                    return;
                }

                super_index++;
            }
        } else {
            return;
        }

        if (gallery_data === false || obj === false) {
            return;
        }

        // If here, hash is valid.

        var clone = false,
            envira_found_counter = 0,
            envira_found_image = false,
            lightbox_images = [];

        $.each(obj.images, function (i) {
            /* check and see if this is a video, and if so override the src so it's using the video embed link */
            if (this.video.embed_url !== undefined) {
                this.src = this.video.embed_url;
            }
            lightbox_images.push(this);
        });

        if (display_all_images === true) {
            for (prop in lightbox_images) {
                if (envira_found_image === false && lightbox_images[prop].id == image_id) {
                    var instance = $.envirabox.open(lightbox_images, obj.lightbox_options, envira_found_counter);
                    envira_found_image = true;
                    envira_item_id = image_id;
                }
                envira_found_counter++;
            }

        } else {

            for (prop in $images) {
                if ($.isNumeric(prop) && envira_found_image == false) {
                    if (gallery_data.columns == 0) {
                        var envira_item_id = $images[prop].childNodes[0].dataset["enviraItemId"];
                    } else {
                        if ($images[prop].childNodes[0].childNodes[0] !== undefined && $images[prop].childNodes[0].childNodes[0].dataset["enviraItemId"] !== undefined) {
                            var envira_item_id = $images[prop].childNodes[0].childNodes[0].dataset["enviraItemId"];
                        } else if ($images[prop].childNodes[0] !== undefined && $images[prop].childNodes[0].dataset["enviraItemId"] !== undefined) {
                            var envira_item_id = $images[prop].childNodes[0].dataset["enviraItemId"];
                        } else {
                            var envira_item_id = false;
                        }
                    }

                    if (envira_found_image == false && envira_item_id !== false && envira_item_id == image_id) {
                        envira_found_image = true;
                        // below is being depreciated due to HTML not being rendered in base dark/light captions
                        var instance = $.envirabox.open(lightbox_images, obj.lightbox_options, envira_found_counter);
                        break;
                    }
                    envira_found_counter++;
                }
            }

        }

    }

    /**
    * Bind the envira_deeplinking function when the window hash changes
    */
    jQuery(window).on('hashchange', function () {
        envira_deeplinking(envira_galleries_temp);
    });


    // DOM ready
    $(function () {

        /******* FANCYBOX *********/

        $(document).on('envirabox_api_after_show', function (e, obj, instance, current) {

            var envira_lb_image = $('img.envirabox-image').attr('src'),
                envira_gallery_id = obj.id,
                envira_gallery_item_id = (current.enviraItemId !== undefined) ? current.enviraItemId : false, // enviraItemId may not exist, but current.id might
                envira_gallery_item_id = (current.id !== undefined) ? current.id : envira_gallery_item_id;

            if (envira_gallery_item_id === undefined || envira_gallery_item_id === false) {
                return;
            }

            /* Should be executed BEFORE any hash change has occurred. */

            (function (namespace) {
                /* Closure to protect local variable "var hash" */
                if ('replaceState' in history) {
                    /* Yay, supported! */
                    namespace.replaceHash = function (newhash) {
                        if (('' + newhash).charAt(0) !== '#') newhash = '#' + newhash;
                        history.replaceState('', '', newhash);
                    };
                } else {
                    var hash = location.hash;
                    namespace.replaceHash = function (newhash) {
                        if (location.hash !== hash) history.back();
                        location.hash = newhash;
                    };
                }
            })(window);

            window.replaceHash("!" + envira_deeplinking_slug + envira_gallery_id + "-" + envira_gallery_item_id);

        });


        $(document).on('envirabox_api_after_close', function (e, obj, instance, current) {

            if ('pushState' in history) {
                history.pushState(null, null, window.location.pathname + window.location.search);
                /* history.pushState( '', document.title, window.location.pathname ); */
            }

        });

    });

})(jQuery, window, document);
