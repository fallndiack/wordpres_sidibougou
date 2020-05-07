<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')) :
    function chld_thm_cfg_locale_css($uri)
    {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('chld_thm_cfg_parent_css')) :
    function chld_thm_cfg_parent_css()
    {
        wp_enqueue_style('chld_thm_cfg_parent', trailingslashit(get_template_directory_uri()) . 'style.css', array());
    }
endif;
add_action('wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10);

// END ENQUEUE PARENT ACTION




require_once('inc/supports.php');
require_once('inc/assets.php');
require_once('inc/apparence.php');
require_once('inc/menus.php');
require_once('inc/images.php');
require_once('inc/style.php');
require_once('inc/query/posts.php');
require_once('inc/query/property.php');
require_once('inc/comments.php');

function agencia_icon(string $name): string
{
    $spriteUrl = get_stylesheet_directory_uri() . '/assets/sprite.14d9fd56.svg';
    return <<<HTML
<svg class="icon"><use xlink:href="{$spriteUrl}#{$name}"></use></svg>
HTML;
}

function agencia_paginate(): string
{
    return '<div class="pagination">' .
        paginate_links(['prev_text' => agencia_icon('arrow'), 'next_text' => agencia_icon('arrow')])
        . '</div>';
}


function agencia_paginate_comments(): void
{
    echo '<div class="pagination">';
    paginate_comments_links(['prev_text' => agencia_icon('arrow'), 'next_text' => agencia_icon('arrow')]);
    echo '</div>';
}


function revconcept_get_images($post_id)
{
    global $post;

    $thumbnail_ID = get_post_thumbnail_id();

    $images = get_children(array('post_parent' => $post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID'));

    if ($images) :

        foreach ($images as $attachment_id => $image) :

            $img_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true); //alt
            if ($img_alt == '') : $img_alt = $image->post_title;
            endif;

            $big_array = image_downsize($image->ID, 'large');
            $img_url = $big_array[0];

            echo '<li>';
            echo '<img src="';
            echo $img_url;
            echo '" alt="';
            echo $img_alt;
            echo '" />';
            echo '</li><!--end slide-->';

        endforeach;
    endif;
}


function mycustomstyles()
{

    //<!-- Favicons -->
    wp_register_style('custom-styles7', get_stylesheet_directory_uri() . '/assets/img/favicon.png');
    wp_register_style('custom-styles8', get_stylesheet_directory_uri() . '/assets/img/apple-touch-icon.png');



    // Register my custom stylesheet
    wp_register_style('custom-styles', get_stylesheet_directory_uri() . '/assets/vendor/bootstrap/css/bootstrap.min.css');
    wp_register_style('custom-styles1', get_stylesheet_directory_uri() . '/assets/vendor/icofont/icofont.min.css');
    wp_register_style('custom-styles2', get_stylesheet_directory_uri() . '/assets/vendor/boxicons/css/boxicons.min.css');
    wp_register_style('custom-styles3', get_stylesheet_directory_uri() . '/assets/vendor/animate.css/animate.min.css');
    wp_register_style('custom-styles4', get_stylesheet_directory_uri() . '/assets/vendor/venobox/venobox.css');
    wp_register_style('custom-styles5', get_stylesheet_directory_uri() . '/assets/vendor/owl.carousel/assets/owl.carousel.min.css');
    wp_register_style('custom-styles6', get_stylesheet_directory_uri() . '/assets/css/style.css');

    // Register my custom script
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/assets/vendor/jquery/jquery.min.js', [], false, true);
    wp_enqueue_script('custom-script1', get_stylesheet_directory_uri() . '/assets/vendor/bootstrap/js/bootstrap.bundle.min.js', [], false, true);
    wp_enqueue_script('custom-script2', get_stylesheet_directory_uri() . '/assets/vendor/jquery.easing/jquery.easing.min.js', [], false, true);
    wp_enqueue_script('custom-script3', get_stylesheet_directory_uri() . '/assets/vendor/jquery-sticky/jquery.sticky.js', [], false, true);
    wp_enqueue_script('custom-script4', get_stylesheet_directory_uri() . '/assets/vendor/owl.carousel/owl.carousel.min.js', [], false, true);
    wp_enqueue_script('custom-script5', get_stylesheet_directory_uri() . '/assets/vendor/waypoints/jquery.waypoints.min.js', [], false, true);
    wp_enqueue_script('custom-script6', get_stylesheet_directory_uri() . '/assets/vendor/counterup/counterup.min.js', [], false, true);
    wp_enqueue_script('custom-script7', get_stylesheet_directory_uri() . '/assets/vendor/isotope-layout/isotope.pkgd.min.js', [], false, true);
    wp_enqueue_script('custom-script8', get_stylesheet_directory_uri() . '/assets/vendor/venobox/venobox.min.js', [], false, true);

    // <!-- Template Main JS File -->
    wp_enqueue_script('custom-script9', get_stylesheet_directory_uri() . '/assets/js/main.js', [], false, true);


    // Load my custom stylesheet
    wp_enqueue_style('custom-styles7');
    wp_enqueue_style('custom-styles8');
    wp_enqueue_style('custom-styles');
    wp_enqueue_style('custom-styles1');
    wp_enqueue_style('custom-styles2');
    wp_enqueue_style('custom-styles3');
    wp_enqueue_style('custom-styles4');
    wp_enqueue_style('custom-styles5');
    wp_enqueue_style('custom-styles6');
}
add_action('wp_enqueue_scripts', 'mycustomstyles');

function wpb_add_google_fonts()
{

    wp_enqueue_style('wpb-google-fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i', false);
}

add_action('wp_enqueue_scripts', 'wpb_add_google_fonts');


function theme_name_custom_orderby($query_args)
{
    $query_args['orderby'] = 'meta_value'; //orderby will be according to data stored inside the particular meta key
    $query_args['order'] = 'ASC';
    return $query_args;
}

add_filter('event_manager_get_listings_args', 'theme_name_custom_orderby', 99);

function theme_name_custom_orderby_query_args($query_args)
{
    $query_args['meta_key'] = '_event_start_date'; //here you can change your meta key
    return $query_args;
}

add_filter('get_event_listings_query_args', 'theme_name_custom_orderby_query_args', 99);
