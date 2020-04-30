<?php
add_action('after_setup_theme', function () {
    register_nav_menu('header', __('Main navigation', 'agencia'));
});

require_once('widgets/social.php');

add_action('widgets_init', function () {
    register_widget(Agencia_Social_Widget::class);
    register_sidebar([
        'id' => 'footer',
        'name' => __('Footer', 'agencia'),
        'before_title' => '<div class="footer__title">',
        'after_title' => '</div>',
        'before_widget' => '<div class="footer__col">',
        'after_widget' => '</div>'
    ]);
    register_sidebar([
        'id' => 'blog',
        'name' => __('Blog sidebar', 'agencia'),
        'before_title' => '<div class="sidebar__title">',
        'after_title' => '</div>',
        'before_widget' => '<div class="sidebar__widget">',
        'after_widget' => '</div>'
    ]);
});

