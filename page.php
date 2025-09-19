<?php
/**
 * The template for displaying all pages
 */

get_header(); ?>
<?php get_template_part('button-back'); ?>
<div class="page">
    <?php while (have_posts()) : the_post(); ?>
        <article id="page-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <div class="entry-thumbnail">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                the_content();

                wp_link_pages(array(
                    'before' => '<div class="page-links">' . __('Pages:', 'xbookcn'),
                    'after'  => '</div>',
                ));
                ?>
            </div>

            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
        </article>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
