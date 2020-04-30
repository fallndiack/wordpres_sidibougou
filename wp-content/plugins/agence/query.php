<?php

/**
 * Ce fichier permet de déclarer les filtres supplémentaires pour filtrer les biens
 */
defined('ABSPATH') or die('rien à voir');

$propertyCategories = [];


// Filtre les biens à acheter ou louer via le paramètre property_category
add_filter('query_vars', function (array $params): array {
    $params[] = 'property_category';
    $params[] = 'city';
    $params[] = 'price';
    $params[] = 'property_type';
    $params[] = 'rooms';
    return $params;
});
add_action('pre_get_posts', function (WP_Query $query) use (&$propertyCategories): void {

    if (is_admin() || !$query->is_main_query() || !is_post_type_archive('property')) {
        return;
    }

    $values = array_keys($propertyCategories);
    if (in_array(get_query_var('property_category'), $values)) {
        $meta_query = $query->get('meta_query', []);
        $meta_query[] = [
            'key' => 'property_category',
            'value' => $propertyCategories[get_query_var('property_category')]
        ];
        $query->set('meta_query', $meta_query);
    }

   


    $type = get_query_var('property_type');
    if ($type) {
        $tax_query = $query->get('tax_query', []);
        $tax_query[] = [
            'taxonomy' => 'property_type',
            'terms' => $type,
            'field' => 'slug'
        ];
        $query->set('tax_query', $tax_query);
    }

  
});

// Réécriture d'url
add_action('init', function () use (&$propertyCategories) {
    $propertyCategories = [
        _x('buy', 'URL', 'agence') => 'buy',
        _x('rent', 'URL', 'agence') => 'rent'
    ];
    add_rewrite_rule(
        _x('property', 'URL', 'agence') . '/(' . implode('|', array_keys($propertyCategories)) . ')/?$',
        'index.php?post_type=property&property_category=$matches[1]',
        'top',
    );
});

// Menu current page 
add_filter('nav_menu_css_class', function (array $classes, WP_Post $item): array {
    if (is_post_type_archive('property')) {
        global $wp;
        if ($wp->request === trim($item->url, '/')) {
            $classes[] = 'current_page_parent';
        }
    }

    if (is_singular('property') && function_exists('get_field')) {
        $property = get_queried_object();
        $category = get_field('property_category', $property);
        if ($category === 'buy') {
            $condition = agence_is_buy_url($item->url);
        } else {
            $condition = agence_is_rent_url($item->url);
        }
        if ($condition === true) {
            $classes[] = 'current_page_parent';
        }
    }

    return $classes;
}, 11, 2);

function agence_is_rent_url(string $url): bool
{
    return strpos($url, _x('property', 'URL', 'agence') . '/' . _x('rent', 'URL', 'agence')) !== false;
}

function agence_is_buy_url(string $url): bool
{
    return strpos($url, _x('property', 'URL', 'agence') . '/' . _x('buy', 'URL', 'agence')) !== false;
}
