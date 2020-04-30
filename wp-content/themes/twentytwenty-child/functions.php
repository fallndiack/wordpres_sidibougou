<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );

if ( !function_exists( 'chld_thm_cfg_parent_css' ) ):
    function chld_thm_cfg_parent_css() {
        wp_enqueue_style( 'chld_thm_cfg_parent', trailingslashit( get_template_directory_uri() ) . 'style.css', array(  ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10 );

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

