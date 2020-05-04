<?php
/*
* Template Name: Page calendrier complet
* description: >-
Page pour lister les évenements dans le calendrier
*/
?>

<?php get_header() ?>

<div class="text-center jumbotron">
	<h1> <?= the_title() ?></h1>
</div>

<div class="container">
	<h2>Vous pouvez choisir le mois et l'année à afficher</h2>
	<?= do_shortcode('[events_calendar]'); ?>
</div>



<?php get_footer(); ?>