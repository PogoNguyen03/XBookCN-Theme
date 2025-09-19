<?php
/**
 * The template for displaying 404 pages (not found)
 */

get_header(); ?>

<div class="error-404">
    <div class="error-content">
        <h1 class="error-title"><?php _e('404', 'xbookcn'); ?></h1>
        <h2 class="error-subtitle"><?php _e('Page Not Found', 'xbookcn'); ?></h2>
        <p class="error-description">
            <?php _e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'xbookcn'); ?>
        </p>
        
        <div class="error-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">
                <?php _e('Go Home', 'xbookcn'); ?>
            </a>
        </div>
        
        <div class="search-form">
            <?php get_search_form(); ?>
        </div>
    </div>
    
    <div class="recent-posts">
        <h3><?php _e('Recent Posts', 'xbookcn'); ?></h3>
        <ul>
            <?php
            $recent_posts = get_recent_posts(5);
            if ($recent_posts->have_posts()) :
                while ($recent_posts->have_posts()) : $recent_posts->the_post();
            ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <span class="post-date"><?php echo get_the_date(); ?></span>
                </li>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </ul>
    </div>
</div>

<?php get_footer(); ?>
