<?php get_header(); ?>
<?php get_template_part('button-back'); ?>
<div class="category-layout"> <!-- Nội dung chính -->
    <div class="category-page">
        <div class="category-header">
            <?php the_title(); ?>
        </div>
        <?php
        global $post;
        $story_id = $post->ID; // ID của truyện hiện tại

        // Lấy mô tả từ custom field / excerpt / description (nếu có)
        $category_description = ! empty( $post->post_excerpt ) ? $post->post_excerpt : '';

        // Nếu không có mô tả thì lấy chương mới nhất
        if ( empty( $category_description ) ) {
            $latest_chapter = get_children([
                'post_type'   => 'qimao_chapter',
                'post_parent' => $story_id,
                'orderby'     => 'ID',
                'order'       => 'DESC',
                'numberposts' => 1
            ]);

            if ( $latest_chapter ) {
                $latest_chapter = array_shift($latest_chapter);
                $chapter_content = $latest_chapter->post_content;

                // Cắt lấy 20 dòng đầu tiên
                $lines = explode("\n", wp_strip_all_tags($chapter_content));
                $lines = array_slice($lines, 0, 10);
                $category_description = implode("\n", $lines);

                if ( str_word_count($chapter_content) > str_word_count($category_description) ) {
                    $category_description .= "\n...";
                }
            }
        }

        if ( ! empty( $category_description ) ) : ?>
            <div class="category-description">
                <h2 class="description-heading">
                    <?php echo esc_html( get_the_title($story_id) ); ?> 内容简介
                </h2>
                <?php echo wpautop( esc_html( $category_description ) ); ?>
            </div>
        <?php endif; ?>
        
        <div class="category-posts">
            <?php if ( have_posts() ) : ?>
            <ul class="post-list" id="post-list">
                <?php
                $chapters = get_children([
                    'post_type'   => 'qimao_chapter',
                    'post_parent' => $story_id,
                    'orderby'     => 'ID',
                    'order'       => 'ASC',
                ]);

                if ( $chapters ) :
                    foreach ( $chapters as $chapter ) : ?>
                        <li class="post-item">
                            <a href="<?php echo get_permalink($chapter->ID); ?>">
                                <?php echo esc_html($chapter->post_title); ?>
                            </a>
                        </li>
                    <?php endforeach;
                else : ?>
                    <li class="post-item">暂无章节.</li>
                <?php endif; ?>
            </ul>
            <div id="pagination" style="display:none;">
                <?php next_posts_link('Next Page'); ?>
            </div>
            <div id="loading-spinner" style="display:none; text-align:center; padding:20px;">
                <div class="lds-spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
            </div>
            <?php else : ?>
                <p><?php _e('还没有小说', 'xbookcn'); ?></p>
            <?php endif; ?>
        </div>
    </div>
    <?php get_template_part('sidebar-right'); ?>
</div>
<?php get_footer(); ?>
