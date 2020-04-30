<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

  <div class="container">

    <h1 class="bien__title"><?php the_title(); ?></h1>
    <header class="bien-header">

      <div class="bien__photos js-slider">
        <?php foreach (get_attached_media('image', get_post()) as $image) : ?>
          <a href="<?= wp_get_attachment_url($image->ID) ?>">
            <img class="bien__photo" src="<?= wp_get_attachment_image_url($image->ID, 'property-carousel'); ?>" alt="">
          </a>
        <?php endforeach ?>
      </div>

    </header>

   
    <!--end flexslider-->


    <div class="bien-body">
      <h2 class="bien-body__title"><?= __('Description', 'agencia'); ?></h2>
      <div class="formatted">
        <?php the_content(); ?>
      </div>
    </div>


  </div>
<?php endwhile; ?>


<?php get_footer(); ?>