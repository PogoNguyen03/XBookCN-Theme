<?php
/**
 * The template for displaying search results pages
 */

get_header(); ?>

<div class="search-results">
    <div class="search-header">
        <h1 class="search-title">
            <?php
            printf(
                esc_html__('Search Results for: %s', 'xbookcn'),
                '<span>' . get_search_query() . '</span>'
            );
            ?>
        </h1>
    </div>

    <?php if (have_posts()) : ?>
        <div class="search-content">
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
                                <span class="post-type"><?php echo get_post_type(); ?></span>
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
        <div class="no-results">
            <h2><?php _e('Nothing Found', 'xbookcn'); ?></h2>
            <p><?php _e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'xbookcn'); ?></p>
            <?php get_search_form(); ?>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
