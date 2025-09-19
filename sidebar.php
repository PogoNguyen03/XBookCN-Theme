<?php
/**
 * The sidebar containing the main widget area
 */

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside class="sidebar">
    <?php dynamic_sidebar('sidebar-1'); ?>
    
    <!-- Custom sidebar content -->
    <div class="widget">
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
    
    <div class="widget">
        <h3><?php _e('Popular Posts', 'xbookcn'); ?></h3>
        <ul>
            <?php
            $popular_posts = get_popular_posts(5);
            if ($popular_posts->have_posts()) :
                while ($popular_posts->have_posts()) : $popular_posts->the_post();
            ?>
                <li>
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    <span class="comment-count">(<?php comments_number('0', '1', '%'); ?>)</span>
                </li>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </ul>
    </div>
    
    <div class="widget">
        <h3><?php _e('Categories', 'xbookcn'); ?></h3>
        <ul>
            <?php
            wp_list_categories(array(
                'orderby' => 'count',
                'order' => 'DESC',
                'show_count' => true,
                'title_li' => '',
                'number' => 10,
            ));
            ?>
        </ul>
    </div>
    
    <div class="widget">
        <h3><?php _e('Tags', 'xbookcn'); ?></h3>
        <div class="tagcloud">
            <?php
            wp_tag_cloud(array(
                'smallest' => 12,
                'largest' => 18,
                'unit' => 'px',
                'number' => 20,
            ));
            ?>
        </div>
    </div>
    
    <div class="widget">
        <h3><?php _e('Archives', 'xbookcn'); ?></h3>
        <ul>
            <?php
            wp_get_archives(array(
                'type' => 'monthly',
                'limit' => 12,
            ));
            ?>
        </ul>
    </div>
</aside>
