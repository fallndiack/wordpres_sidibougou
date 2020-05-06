<?php
/*
* Template Name: Contact page
* description: >-
Page pour nous contacter
*/

?>


<?php get_header() ?>


<div class="row container">
    <?= do_shortcode('[contact-form-7 id="295" title="Monochrome light"]'); ?>
    <?= do_shortcode('[envira-gallery id="384"]'); ?>
</div>







<?php get_footer(); ?>