<?php get_header() ?>
<!-- le conteneur fenêtre -->
<div class="marquee-rtl">
    <!-- le contenu défilant -->
    <div>
        <h4 class="text-defilant">

            ==== COMITE DE DEVELOPPEMENT DE SIDI BOUGOU ====</h4>
    </div>
</div><!-- ======= Hero Section ======= -->
<section id="hero">
    <div class="hero-container">
        <div id="heroCarousel" class="carousel slide carousel-fade" data-ride="carousel">

            <ol class="carousel-indicators" id="hero-carousel-indicators"></ol>

            <div class="carousel-inner" role="listbox">

                <!-- Slide 1 -->
                <div class="carousel-item active" style="background: url('<?= get_stylesheet_directory_uri() ?>/assets/img/slide/2.jpg');">
                    <div class="carousel-container">
                        <div class="carousel-content">
                            <h2 class="animated fadeInDown">Le Village de <span>SIDI BOUGOU</span></h2>
                            <p class="animated fadeInUp">
                                Situé à l’ouest de MBoulème et à l’est Warang,
                                Sidi Bougou est un petit village paisible des problèmes des villes.
                                Il comprend un quartier Wolof qui séparé par une centaine de mètre
                                d’un quartier Bambara.
                                Les deux communautés n'en font qu'une en vrai et vivent dans la paix.
                            </p>
                            <a href="" class="btn-get-started animated fadeInUp">BIENVENUE!!!</a>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="carousel-item" style="background: url('<?= get_stylesheet_directory_uri() ?>/assets/img/slide/s2.jpg');">
                    <div class="carousel-container">
                        <div class="carousel-content">
                            <h2 class="animated fadeInDown">Vivre <span>Ensemble</span></h2>
                            <p class="animated fadeInUp">
                                Pour vivre ensemble de manière harmonieuse, il est essentiel de partager des valeurs.

                                Par le développement de valeurs humaines, nous exprimons notre humanité par
                                des marques de respect, de considération, d’empathie, etc. envers les autres.

                                Les valeurs éthiques requièrent une conduite respectant autrui et ne nuisant
                                pas aux autres. Cela concerne les rapports humains .
                            </p>
                            <a href="" class="btn-get-started animated fadeInUp">BIENVENUE!!!</a>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="carousel-item" style="background: url('<?= get_stylesheet_directory_uri() ?>/assets/img/slide/slide3.png');">
                    <div class="carousel-container">
                        <div class="carousel-content">
                            <h2 class="animated fadeInDown">Nous avançons <span>Main dans la Main</span></h2>
                            <p class="animated fadeInUp">
                                Main dans la main, on peut aller plus loin, et plus qu'on ne le croit.
                                Main dans la main avançons sur la route du bonheur sans jamais se retourner. ♥
                                Le Village porte des valeurs humanistes, 
                                fondées sur l’ouverture sur les autres cultures, 
                                la liberté de création, la liberté des peuples, le partage.
                            </p>
                            <a href="" class="btn-get-started animated fadeInUp">BIENVENUE!!!</a>
                        </div>
                    </div>
                </div>

            </div>

            <a class="carousel-control-prev" href="#heroCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon icofont-rounded-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>

            <a class="carousel-control-next" href="#heroCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon icofont-rounded-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>

        </div>
    </div>
</section><!-- End Hero -->


<?php while (have_posts()) : the_post() ?>



    <main class="sections">

        <!-- ======= Featured Section ======= -->

        <?php $the_query = new WP_Query('posts_per_page=3envent'); ?>

        <section id="featured" class="featured">
            <div class="container">
                <div class="row">
                    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
                        <div class="col-lg-4 mt-4 mt-lg-0">
                            <a href="<?php the_permalink() ?>">
                                <div class="icon-box">
                                    <?php the_post_thumbnail('thumbnail'); ?>
                                    <h3><?php the_title(); ?></h3>
                                    <p><?php the_excerpt(__('(more…)')); ?></p>
                                </div>
                            </a>
                        </div>

                    <?php
                    endwhile;
                    wp_reset_postdata();
                    ?>



                </div>
                <h2>Effectuez une recherche</h2>
                <?= get_search_form() ?>
            </div>

        </section><!-- End Featured Section -->


        <!--slider events  -->
        <section class="container" style="padding-top: 5px;margin-top: 5px;">
            <h2 class="text-center mb-4">Consultez l'agenda des Evénements</h2>
            <div>
                <?php echo do_shortcode('[events_slider]'); ?>
            </div>
        </section>

        <!--     Feature properties -->
        <!--  <?php if (have_rows('recent_properties')) : while (have_rows('recent_properties')) : the_row() ?>
                <section class="container" style="padding-top: 5px;margin-top: 5px;">
                    <div class=" push-properties">
                        <div class="push-properties__title"><?php the_sub_field('title') ?></div>
                        <?php the_sub_field('description') ?>
                        <div class="push-properties__grid">
                            <?php
                            $query = [
                                'post_type' => 'property',
                                'posts_per_page' => 4
                            ];
                            $property = get_sub_field('highlighted_property');
                            if ($property) {
                                $query['post__not_in'] = [$property->ID];
                            }
                            $query = new WP_Query($query);
                            while ($query->have_posts()) {
                                $query->the_post();
                                get_template_part('template-parts/property');
                            }
                            wp_reset_postdata();
                            ?>


                        </div>

                        <?php if ($property) : ?>

                            <div class="highlighted">
                                <?= get_the_post_thumbnail($property, 'property-thumbnail-home') ?>
                                <div class="highlighted__body">
                                    <div class="highlighted__title"><a href="<?php the_permalink($property) ?>"><?= get_the_title($property) ?></a></div>

                                </div>
                            </div>

                        <?php endif ?>

                        <a class="push-properties__action btn" href="<?= get_post_type_archive_link('property') ?>">
                            <?= __('Parcourir', 'agencia') ?>
                            <?= agencia_icon('arrow'); ?>
                        </a>

                    </div>
                </section>
        <?php endwhile;
                endif ?> -->

        <?php if (have_rows('quote')) : while (have_rows('quote')) : the_row() ?>
                <section class="container quote">



                    <div class="quote__title"><?php the_sub_field('title') ?></div>
                    <div class="quote__body">
                        <div class="quote__image">
                            <img src="<?php the_sub_field('avatar') ?>" alt="">
                            <div class="quote__author"><?php the_sub_field('cite') ?></div>
                        </div>
                        <blockquote>
                            <?php the_sub_field('content') ?>
                        </blockquote>
                    </div>

                    <?php if ($action = get_sub_field('action')) : ?>
                        <a class="quote__action btn" href="<?= $action['url'] ?>">
                            <?= $action['title']; ?>
                            <?= agencia_icon('arrow') ?>
                        </a>
                    <?php endif ?>
                </section>
        <?php endwhile;
        endif ?>

        <!-- Read our stories -->
        <?php if (have_rows('recent_posts')) : while (have_rows('recent_posts')) : the_row() ?>
                <section class="container push-news">
                    <h2 class="push-news__title text-center"><?php the_sub_field('title') ?></h2>
                    <?php the_sub_field('description') ?>
                    <?php
                    $query = new WP_Query(['post_type' => 'post', 'posts_per_page' => 3]);
                    ?>
                    <div class="push-news__grid">
                        <?php $i = 0;
                        while ($query->have_posts()) : $query->the_post();
                            $i++; ?>
                            <a href="<?php the_permalink() ?>" class="push-news__item">
                                <?php the_post_thumbnail('post-thumbnail-home') ?>
                                <span class="push-news__tag">Tendance</span>
                                <h3 class="push-news__label"><?php the_title() ?></h3>
                            </a>
                            <?php if ($i === 1) : ?>
                                <div class="news-overlay">
                                    <img src="<?= get_sub_field('background')['sizes']['post-thumbnail-home'] ?>">
                                    <div class="news-overlay__body">
                                        <div class="news-overlay__title">
                                            <?= __('Consultez tous nos articles sur le village', 'agencia') ?>
                                        </div>
                                        <a href="<?= get_post_type_archive_link('post') ?>" class="news-overlay__btn btn">
                                            <?= __('Voir tous les articles') ?>
                                            <?= agencia_icon('arrow') ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endif ?>
                        <?php endwhile;
                        wp_reset_postdata() ?>
                    </div>
                </section>
        <?php endwhile;
        endif ?>

        <!-- Newsletter -->
        <?php if (have_rows('newsletter')) : while (have_rows('newsletter')) : the_row() ?>
                <section class="newsletter">
                    <form class="newsletter__body">
                        <div class="newsletter__title"><?php the_sub_field('title') ?></div>
                        <?php the_sub_field('description') ?>
                        <div class="form-group">
                            <input type="email" class="form-control" id="email" placeholder="Entrez votre email">
                            <label for="email">Votre email</label>
                        </div>

                        <button type="submit" class="btn"><?= __('Signup', 'agencia') ?></button>
                    </form>
                    <div class="newsletter__image">
                        <img src="<?php the_sub_field('avatar') ?>" alt="">
                    </div>
                </section>
        <?php endwhile;
        endif ?>

    </main>
<?php endwhile ?>
<?php get_footer(); ?>