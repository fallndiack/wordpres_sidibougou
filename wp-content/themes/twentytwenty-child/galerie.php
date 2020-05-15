<?php
/*
* Template Name: Page Gallerie
* description: >-
Page pour les galleries
*/
?>

<?php get_header() ?>

<div class="text-center jumbotron">
    <h1> <?= the_title() ?></h1>
</div>
<div class="container">
    <?php the_content() ?>

</div>



<?php get_footer(); ?>