<?php
/*
* Template Name: Archive property
* description: >-
Page pour lister les édifices
*/

?>

<?php get_header() ?>

<?php

$types = get_terms([
    'taxonomy' => 'property_type'
]);
$currentType = get_query_var('property_type');
?>


<div class="container page-properties">
    <div class="search-form">
        <h1 class="search-form__title">Edifices et Monuments</h1>

        <hr>
        <form action="" class="search-form__form">
            <div class="text-info">
                <h3>Effectuez une recherche par type</h3>
            </div>


            <div class="form-group">
                <select name="property_type" id="property_type" class="form-control">
                    <option value=""><?= __('Tous les types', 'agencia') ?></option>
                    <?php foreach ($types as $type) : ?>
                        <option value="<?= $type->slug ?>" <?php selected($type->slug, $currentType) ?>><?= $type->name ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="property_type"><?= __('Type', 'agencia') ?></label>
            </div>

            <button type="submit" class="btn btn-filled">Rechercher</button>
        </form>


        <div class="text-info">
            <h2 class="text mt-4" style="font-weight: bold">La fierté de toute une communauté</h2>
            <p class="mt-4">Montius nos tumore inusitato quodam et novo ut rebellis et maiestati recalcitrantes Augustae per haec quae strepit incusat iratus nimirum quod contumacem praefectum, quid rerum ordo postulat ignorare dissimulantem formidine tenus iusserim custodiri.

                Sed tamen haec cum ita tutius observentur, quidam vigore artuum inminuto rogati ad nuptias ubi aurum dextris manibus cavatis offertur, inpigre vel usque Spoletium pergunt. haec nobilium sunt instituta.

        </div>
    </div>



    <!-- 
            <img src="https://i.picsum.photos/id/37/802/220.jpg" alt=""> 
    -->
    <?php $i = 0;
    while (have_posts()) : the_post(); ?>
        <?php set_query_var('property-large', $i === 7); ?>
        <?php get_template_part('template-parts/property') ?>
    <?php $i++;
    endwhile; ?>


</div>

<?php if (get_query_var('paged', 1) > 1) : ?>
    <?= agencia_paginate() ?>
<?php elseif ($nextPostLink = get_next_posts_link(__('Voir Plus +', 'agencia'))) : ?>
    <div class="pagination">
        <?= $nextPostLink ?>
    </div>
<?php endif ?>


<?php get_footer(); ?>