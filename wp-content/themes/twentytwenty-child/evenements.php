<?php
/*
* Template Name: Page evenements
* description: >-
Page pour lister les évenements
*/
?>

<?php get_header() ?>

<div class="text-center jumbotron">
    <h1> <?= the_title() ?></h1>
</div>

<div class="container">
    <h3>Rechercher un événement</h3>
    <?= do_shortcode('[events]'); ?>
</div>



<?php get_footer(); ?>