<?php
/**
 * The template for displaying archive pages
 */

get_header(); ?>

<div class="archive-page">
    <div class="archive-header">
        <?php
        the_archive_title('<h1 class="archive-title">', '</h1>');
        the_archive_description('<div class="archive-description">', '</div>');
        ?>
    </div>

    <?php if (have_posts()) : ?>
        <div class="archive-content">
            <div class="post-list">
                <?php while (have_posts()) : the_post(); ?>
                    <article class="post-item">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-content">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <div class="post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <span class="post-author"><?php _e('by', 'xbookcn'); ?> <?php the_author(); ?></span>
                                <span class="post-category"><?php _e('in', 'xbookcn'); ?> <?php the_category(', '); ?></span>
                            </div>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="read-more"><?php _e('Read More', 'xbookcn'); ?></a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php
            // Pagination
            pagination();
            ?>
        </div>
        
    <?php else : ?>
        <div class="no-posts">
            <h2><?php _e('Nothing Found', 'xbookcn'); ?></h2>
            <p><?php _e('It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'xbookcn'); ?></p>
            <?php get_search_form(); ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
