<?php get_header(); ?>
<?php get_template_part('button-back'); ?>
<div class="category-layout container">
    <!-- Nội dung chính -->
    <div class="category-content" style="border: 0.5px dotted #000000;">
        
        <!-- Tiêu đề danh mục -->
        <div class="category-heade">
            <?php 
            $cat = get_queried_object(); // Danh mục hiện tại
            echo '<h1 class="category-title">' . esc_html($cat->name) . '</h1>';
            ?>
        </div>

        <!-- Mô tả danh mục (chỉ hiển thị khi có) -->
        <?php if (!empty(trim($cat->description))) : ?>
            <div class="category-description">
                <?php echo apply_filters('the_content', $cat->description); ?>
            </div>
        <?php endif; ?>

        <!-- Danh sách truyện -->
        <div class="">
            <?php
            // Query tất cả truyện (qimao_story) thuộc category này
            $stories = new WP_Query([
                'post_type'      => 'qimao_story',
                'tax_query'      => [
                    [
                        'taxonomy' => 'category',
                        'field'    => 'term_id',
                        'terms'    => $cat->term_id,
                    ],
                ],
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ]);

            if ($stories->have_posts()) :
                echo '<ul class="post-list" id="post-list">';
                $count = 1; // biến đếm
              while ($stories->have_posts()) : $stories->the_post();
    echo '<li class="post-ite">';
    
    // Số + Tiêu đề
    echo '<div class="post-header">';
    echo '<span class="post-index">' . $count . '.</span> ';
    echo '<a href="' . get_permalink() . '" class="post-title">《' . get_the_title() . '》</a>';
    echo '</div>';

    // ==== Mô tả dưới post-header ====
    $story_id = get_the_ID();
    $story_description = '';

    if (has_excerpt()) {
        // Nếu có excerpt → dùng excerpt
        $story_description = get_the_excerpt();
    } else {
        // Lấy chương mới nhất làm mô tả
        $latest_chapter = get_children([
            'post_type'   => 'qimao_chapter',
            'post_parent' => $story_id,
            'orderby'     => 'ID',
            'order'       => 'DESC',
            'numberposts' => 1
        ]);

        if ($latest_chapter) {
            $latest_chapter = array_shift($latest_chapter);
            $chapter_content = $latest_chapter->post_content;

            // Cắt lấy 20 dòng đầu tiên
            $lines = explode("\n", wp_strip_all_tags($chapter_content));
            $lines = array_slice($lines, 0, 5);
            $story_description = implode("\n", $lines);

            if ( str_word_count($chapter_content) > str_word_count($story_description) ) {
                $story_description .= "\n...";
            }
        } else {
            // Nếu chưa có chương nào → fallback 20 từ từ nội dung truyện
            $story_description = wp_trim_words(get_the_content(), 20, '...');
        }
    }

    echo '<div class="post-description">' . wpautop(esc_html($story_description)) . '</div>';
    // ================================

    echo '</li>';
    $count++;
endwhile;

                echo '</ul>';
                wp_reset_postdata();
            else :
                echo '<p>' . __('此类别中没有小说。', 'xbookcn') . '</p>';
            endif;



            ?>
        </div>
    </div>
    <!-- Sidebar phải -->
        <?php get_template_part('sidebar-right'); ?>
</div>

<?php get_footer(); ?>
