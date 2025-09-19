<?php get_header(); ?>
<style>
    .home.blog.wp-theme-xbookcn_v4.gettheme-skin #page{
        border: 1px solid;
        background: var(--main-bg-color);
        margin: 0 auto;
        width: fit-content;
    }
    @media (max-width: 768px) {
        .home.blog.wp-theme-xbookcn_v4.gettheme-skin #page{
            
        border: 0px solid !important;
        }
    }
</style>

<div class="tp-box ok" bis_skin_checked="1">
    <h2><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('description'); ?></a></h2>

    <?php
    // ===== Function đếm chương trong toàn bộ truyện thuộc category cha =====
    if (!function_exists('count_chapters_in_parent_category')) {
        function count_chapters_in_parent_category($parent_cat_id) {
            $chapter_count = 0;

            // Lấy toàn bộ category con (cấp bất kỳ) của category cha
            $child_categories = get_categories(array(
                'child_of'   => $parent_cat_id,
                'hide_empty' => false,
            ));

            if (!empty($child_categories)) {
                $child_cat_ids = wp_list_pluck($child_categories, 'term_id');

                // Lấy tất cả truyện trong category con
                $stories = get_posts(array(
                    'post_type'      => 'qimao_story',
                    'posts_per_page' => -1,
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'category',
                            'field'    => 'term_id',
                            'terms'    => $child_cat_ids,
                        ),
                    ),
                    'fields' => 'ids',
                ));

                if (!empty($stories)) {
                    // Lấy tất cả chương thuộc các truyện đó
                    $chapters = get_posts(array(
                        'post_type'      => 'qimao_chapter',
                        'posts_per_page' => -1,
                        'post_parent__in'=> $stories,
                        'fields'         => 'ids',
                    ));
                    $chapter_count = count($chapters);
                }
            }

            return $chapter_count;
        }
    }

    // Hàm chuyển số sang chữ Hán (0 - lớn hơn, có thể mở rộng)
    if (!function_exists('number_to_chinese')) {
        function number_to_chinese($num) {
            $chars = array('零','一','二','三','四','五','六','七','八','九');
            $units = array('','十','百','千','万','十万','百万','千万','亿');

            if ($num == 0) return $chars[0];

            $str = '';
            $unitPos = 0;
            $needZero = false;

            while ($num > 0) {
                $section = $num % 10;
                if ($section == 0) {
                    if ($needZero) {
                        $str = $chars[0] . $str;
                        $needZero = false;
                    }
                } else {
                    $str = $chars[$section] . $units[$unitPos] . $str;
                    $needZero = true;
                }
                $num = intval($num / 10);
                $unitPos++;
            }

            // Xử lý trường hợp "一十" => "十"
            $str = preg_replace('/^一十/','十',$str);
            return $str;
        }
    }

    // ==================== CODE CŨ CỦA BẠN =====================

    if (!function_exists('get_categoryfather_url')) {
        function get_categoryfather_url() {
            $pages = get_pages(array(
                'meta_key'   => '_wp_page_template',
                'meta_value' => 'categoryfather.php',
                'number'     => 1,
            ));
            if (!empty($pages)) {
                return get_permalink($pages[0]->ID);
            }
            return add_query_arg('categoryfather', '1', home_url('/'));
        }
    }

    function render_list($q) {
        if ($q && $q->have_posts()) {
            echo "<ul>";
            while ($q->have_posts()) {
                $q->the_post();
                echo '<li><a href="' . esc_url(get_permalink()) . '" title="' . esc_attr(get_the_title()) . '">' . esc_html(get_the_title()) . '</a></li>';
            }
            echo "</ul>";
            wp_reset_postdata();
        } else {
            echo '<ul class="story-list"><li>' . esc_html__('还没有帖子', 'xbookcn') . '</li></ul>';
        }
    }

    function render_list_or_children($category_id) {
        $q = new WP_Query(array(
            'cat' => (int) $category_id,
            'posts_per_page' => 8,
            'ignore_sticky_posts' => true,
        ));
        if ($q->have_posts()) {
            echo '<ul class="story-list">';
            while ($q->have_posts()) {
                $q->the_post();
                echo '<li><a href="' . esc_url(get_permalink()) . '" title="' . esc_attr(get_the_title()) . '">' . esc_html(get_the_title()) . '</a></li>';
            }
            echo '</ul>';
            wp_reset_postdata();
            return;
        }
        wp_reset_postdata();

        $children = get_categories(array(
            'parent' => (int) $category_id,
            'hide_empty' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
        ));
        if (!empty($children)) {
            echo '<ul>';
            foreach ($children as $child_cat) {
                echo '<li><a href="' . esc_url(get_category_link($child_cat)) . '" title="' . esc_attr($child_cat->name) . '">' . esc_html($child_cat->name) . '</a></li>';
            }
            echo '</ul>';
        } else {
            echo '<ul class="story-list"><li>' . esc_html__('还没有帖子', 'xbookcn') . '</li></ul>';
        }
    }

    // 1) Nếu có root
    $root_slug = isset($_GET['root']) ? sanitize_title($_GET['root']) : get_theme_mod('root_slug', '');
    if ($root_slug) {
        $parent = get_category_by_slug($root_slug);
        if ($parent) {
            $target_url = add_query_arg('root', $parent->slug, get_categoryfather_url());
            $total_chapters = count_chapters_in_parent_category($parent->term_id);
            $chinese_num = number_to_chinese($total_chapters);
            echo '<h2><a href="' . esc_url($target_url) . '">' . esc_html($parent->name) . '</a>（' . esc_html($chinese_num) . '章）</h2>';

            $children = get_categories(array(
                'parent' => $parent->term_id,
                'hide_empty' => 0,
                'orderby' => 'name',
                'order' => 'ASC',
            ));
            foreach ($children as $child) {
                echo '<h3><a href="' . esc_url(get_category_link($child)) . '">' . esc_html($child->name) . '</a></h3>';
                $args = array(
                    'post_type' => 'qimao_story',
                    'posts_per_page' => 12,
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'cat' => (int) $child->term_id,
                    'ignore_sticky_posts' => true,
                );
                $stories = new WP_Query($args);
                if ($stories->have_posts()):
                    echo '<div class="story-list">';
                    while ($stories->have_posts()):
                        $stories->the_post();
                        echo '<div class="story-item">';
                        echo '<a href="' . esc_url(get_permalink()) . '">';
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('medium');
                        }
                        echo '<h2>' . esc_html(get_the_title()) . '</h2>';
                        echo '</a>';
                        echo '</div>';
                    endwhile;
                    echo '</div>';
                    wp_reset_postdata();
                else:
                    echo '<p>' . esc_html__('暂无故事。', 'xbookcn') . '</p>';
                endif;
            }
        }
    }

    // 2) Nếu chưa có root → liệt kê toàn bộ top categories
    if (!$root_slug) {
        $exclude_id = (int) get_option('default_category');
        $top_cats = get_categories(array(
            'parent' => 0,
            'hide_empty' => 0,
            'orderby' => 'name',
            'order' => 'ASC',
            'exclude' => $exclude_id ? array($exclude_id) : array(),
        ));
        foreach ($top_cats as $cat_obj) {
            $target_url = add_query_arg('root', $cat_obj->slug, get_categoryfather_url());
            $total_chapters = count_chapters_in_parent_category($cat_obj->term_id);
            $chinese_num = number_to_chinese($total_chapters);
            // <-- SỬA: dùng $cat_obj->name (không phải $parent)
            echo '<h2><a href="' . esc_url($target_url) . '">' . esc_html($cat_obj->name) . '</a>（' . esc_html($chinese_num) . '章）</h2>';

            $children = get_categories(array(
                'parent' => $cat_obj->term_id,
                'hide_empty' => 0,
                'orderby' => 'name',
                'order' => 'ASC',
            ));
            if (!empty($children)) {
                foreach ($children as $child) {
                    echo '<h3><a href="' . esc_url(get_category_link($child)) . '">' . esc_html($child->name) . '</a></h3>';
                    $args = array(
                        'post_type' => 'qimao_story',
                        'posts_per_page' => 12,
                        'orderby' => 'date',
                        'order' => 'DESC',
                        'cat' => (int) $child->term_id,
                        'ignore_sticky_posts' => true,
                    );
                    $stories = new WP_Query($args);
                    if ($stories->have_posts()):
                        echo '<ul class="story-list">';
                        while ($stories->have_posts()):
                            $stories->the_post();
                            echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                        endwhile;
                        echo '</ul>';
                        wp_reset_postdata();
                    else:
                        echo '<p>' . esc_html__('暂无故事。', 'xbookcn') . '</p>';
                    endif;
                }
            } else {
                render_list_or_children($cat_obj->term_id);
            }
        }
    }
    ?>

    <h2>网站分类</h2>
    <ul class="story-list">
        <li><a href="/">繁體小說</a></li>
        <li><a href="/">简体小说</a></li>
        <li><a href="/">经典小说</a></li>
        <li><a href="https://novels.stcroixcreative.com/2025/09/18/contact/">联系我们</a></li>
    </ul>
    <h2>站内全文搜索</h2>
</div>

<?php get_footer(); ?>
