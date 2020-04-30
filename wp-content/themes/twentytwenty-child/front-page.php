<?php get_header() ?>

<?php while (have_posts()) : the_post() ?>

    <main class="sections">
        <!-- Find your home -->
        <section>
            <div class="container">
                <div class="search-form">
                    <h1 class="search-form__title"><?php the_title() ?></h1>
                    <?php the_content() ?>
                    <hr>
                    <?php get_template_part('template-parts/searchform-property') ?>
                </div>

            </div>
            <?php if ($property = get_field('highlighted_property')) : ?>
                <div class="highlighted highlighted--home">
                    <?= get_the_post_thumbnail($property, 'property-thumbnail-home') ?>
                    <div class="highlighted__body">
                        <div class="highlighted__title"><a href="<?php the_permalink($property) ?>"><?= get_the_title($property) ?></a></div>
                       
                    </div>
                </div>
            <?php endif ?>
        </section>

        <!-- Feature properties -->
        <?php if (have_rows('recent_properties')) : while (have_rows('recent_properties')) : the_row() ?>
                <section class="container">
                    <div class="push-properties">
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
        endif ?>

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
                    <h2 class="push-news__title"><?php the_sub_field('title') ?></h2>
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
                        <!--
        <input type="email" class="form-control" placeholder="Enter your email adress">
        -->
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