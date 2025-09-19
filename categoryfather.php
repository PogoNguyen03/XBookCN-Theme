<?php get_header(); ?>
<?php get_template_part('button-back'); ?>
<div class='content-outer'>
    <div class='content-inner'>
        <div class='main-outer'>
            <div class='region-inner main-inner'>
                <div class='columns fauxcolumns'>
                    <div class='columns-inner'>
                        <div class='column-center-outer'>
                            <div class='column-center-inner'>
                                <div class='main section' id='main' name='主体'>
                                    <div class='widget Blog' id='Blog1'>
                                        <div class='blog-posts hfeed'>

                                            <div class="date-outer">
                                                <div class="date-posts">
                                                    <div class='post-outer'>
                                                        <div class='post hentry uncustomized-post-template' itemprop='blogPost'>
                                                            <h3 class='post-title entry-title' itemprop='name'>
                                                                <a>情色小说目录索引</a>
                                                            </h3>
                                                            <div class='post-header'>
                                                                <div class='post-header-line-1'></div>
                                                            </div>
                                                            <div class='post-body entry-content' itemprop='description articleBody'>
                                                                <div class="tp-box">
                                                                    <h2><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('description'); ?></a></h2>

                                                                    <?php
                                                                    // ======================================================
                                                                    // Hàm helper: render danh sách UL/li
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
                                                                            echo '<ul><li>' . esc_html__('还没有帖子。', 'xbookcn') . '</li></ul>';
                                                                        }
                                                                    }

                                                                    // Hàm helper: render danh sách bài hoặc con category
                                                                    function render_list_or_children($category_id) {
                                                                        $q = new WP_Query(array(
                                                                            'cat' => (int) $category_id,
                                                                            'posts_per_page' => 8,
                                                                            'ignore_sticky_posts' => true,
                                                                        ));
                                                                        if ($q->have_posts()) {
                                                                            echo '<ul>';
                                                                            while ($q->have_posts()) {
                                                                                $q->the_post();
                                                                                echo '<li><a href="' . esc_url(get_permalink()) . '" title="' . esc_attr(get_the_title()) . '">' . esc_html(get_the_title()) . '</a></li>';
                                                                            }
                                                                            echo '</ul>';
                                                                            wp_reset_postdata();
                                                                            return;
                                                                        }
                                                                        wp_reset_postdata();
                                                                        // fallback con category
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
                                                                            echo '<ul><li>' . esc_html__('还没有帖子。', 'xbookcn') . '</li></ul>';
                                                                        }
                                                                    }

                                                                    // ======================================================
                                                                    // Hàm đếm tổng số chương trong tất cả truyện của tất cả category con
                                                                    function count_chapters_in_parent_category($parent_cat_id) {
                                                                        // lấy con category
                                                                        $children = get_categories(array(
                                                                            'parent' => (int) $parent_cat_id,
                                                                            'hide_empty' => 0,
                                                                            'fields' => 'ids'
                                                                        ));
                                                                        if (empty($children)) return 0;

                                                                        // lấy tất cả truyện thuộc con category
                                                                        $stories = get_posts(array(
                                                                            'post_type'      => 'qimao_story',
                                                                            'numberposts'    => -1,
                                                                            'tax_query'      => array(
                                                                                array(
                                                                                    'taxonomy' => 'category',
                                                                                    'field'    => 'term_id',
                                                                                    'terms'    => $children,
                                                                                    'include_children' => true,
                                                                                ),
                                                                            ),
                                                                            'fields' => 'ids',
                                                                        ));
                                                                        if (empty($stories)) return 0;

                                                                        // đếm số chương
                                                                        $chapters = get_posts(array(
                                                                            'post_type'      => 'qimao_chapter',
                                                                            'numberposts'    => -1,
                                                                            'post_parent__in'=> $stories,
                                                                            'fields'         => 'ids',
                                                                        ));
                                                                        return count($chapters);
                                                                    }

                                                                    // ======================================================
                                                                    // 1) Nếu có root → hiển thị category cha
                                                                    $root_slug = isset($_GET['root']) ? sanitize_title($_GET['root']) : get_theme_mod('root_slug', '');
                                                                    if ($root_slug) {
                                                                        $parent = get_category_by_slug($root_slug);
                                                                        if ($parent) {
                                                                            $total_chapters = count_chapters_in_parent_category($parent->term_id);
                                                                           echo '<h2><a href="' . esc_url(get_category_link($parent)) . '">' 
                                                                                . esc_html($parent->name) . '（' . number_to_chinese($total_chapters) . '章）</a></h2>';
                                                                            $children = get_categories(array(
                                                                                'parent' => $parent->term_id,
                                                                                'hide_empty' => 0,
                                                                                'orderby' => 'name',
                                                                                'order' => 'ASC',
                                                                            ));
                                                                            foreach ($children as $child) {
                                                                                echo '<h3><a href="' . esc_url(get_category_link($child)) . '">' . esc_html($child->name) . '</a></h3>';
                                                                                $args = array(
                                                                                    'post_type'      => 'qimao_story',
                                                                                    'posts_per_page' => 12,
                                                                                    'orderby'        => 'date',
                                                                                    'order'          => 'DESC',
                                                                                    'cat'            => (int) $child->term_id,
                                                                                    'ignore_sticky_posts' => true,
                                                                                );
                                                                                $stories = new WP_Query($args);
                                                                                if ($stories->have_posts()) :
                                                                                    echo '<ul class="story-list">';
                                                                                    while ($stories->have_posts()) : $stories->the_post();
                                                                                        echo '<li class="story-item"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                                                                                    endwhile;
                                                                                    echo '</ul>';
                                                                                    wp_reset_postdata();
                                                                                else :
                                                                                    echo '<p>' . esc_html__('暂无故事。', 'xbookcn') . '</p>';
                                                                                endif;
                                                                            }
                                                                        }
                                                                    }

                                                                    // ======================================================
                                                                    // 2) Nếu chưa set root → hiển thị tất cả category cấp 1
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
                                                                            $total_chapters = count_chapters_in_parent_category($cat_obj->term_id);
                                                                           echo '<h2><a href="' . esc_url(get_category_link($cat_obj)) . '">' 
                                                                                . esc_html($cat_obj->name) . '（' . number_to_chinese($total_chapters) . '章）</a></h2>';

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
                                                                                        'post_type'      => 'qimao_story',
                                                                                        'posts_per_page' => 12,
                                                                                        'orderby'        => 'date',
                                                                                        'order'          => 'DESC',
                                                                                        'cat'            => (int) $child->term_id,
                                                                                        'ignore_sticky_posts' => true,
                                                                                    );
                                                                                    $stories = new WP_Query($args);
                                                                                    if ($stories->have_posts()) :
                                                                                        echo '<ul class="story-list">';
                                                                                        while ($stories->have_posts()) : $stories->the_post();
                                                                                            echo '<li class="story-item"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                                                                                        endwhile;
                                                                                        echo '</ul>';
                                                                                        wp_reset_postdata();
                                                                                    else :
                                                                                        echo '<p>' . esc_html__('暂无故事。', 'xbookcn') . '</p>';
                                                                                    endif;
                                                                                }
                                                                            } else {
                                                                                render_list_or_children($cat_obj->term_id);
                                                                            }
                                                                        }
                                                                    }
                                                                    
                                                                    // Chuyển số sang chữ Hán giản thể
                                                                        function number_to_chinese($number) {
                                                                            $chars = array('零','一','二','三','四','五','六','七','八','九');
                                                                            $units = array('', '十', '百', '千', '万', '亿');
                                                                        
                                                                            if ($number == 0) return $chars[0];
                                                                        
                                                                            $result = '';
                                                                            $unitPos = 0;
                                                                            $needZero = false;
                                                                        
                                                                            while ($number > 0) {
                                                                                $section = $number % 10;
                                                                                if ($section == 0) {
                                                                                    if ($needZero) {
                                                                                        $result = $chars[0] . $result;
                                                                                        $needZero = false;
                                                                                    }
                                                                                } else {
                                                                                    $result = $chars[$section] . $units[$unitPos] . $result;
                                                                                    $needZero = true;
                                                                                }
                                                                                $unitPos++;
                                                                                $number = intval($number / 10);
                                                                            }
                                                                        
                                                                            // Xử lý đặc biệt: 10 → 十, 11 → 十一 (không phải 一十一)
                                                                            $result = preg_replace('/^一十/', '十', $result);
                                                                        
                                                                            return $result;
                                                                        }

                                                                    ?>
                                                                </div>
                                                                <div style='clear: both;'></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Search -->
                                            <div class="date-outer">
                                                <div class="date-posts">
                                                    <div class="post-outer">
                                                        <div class="post hentry uncustomized-post-template" itemprop="blogPost">
                                                            <h3 class="post-title entry-title"><a href="/">站内搜索</a></h3>
                                                            <div class="post-header"><div class="post-header-line-1"></div></div>
                                                            <div class="post-body entry-content">
                                                                <form id="category-search-form" onsubmit="return false;">
                                                                    <input id="category-search-input" type="text" placeholder="搜索小说" autocomplete="off">
                                                                    <button id="category-search-button" type="button">站内搜索</button>
                                                                </form>
                                                                <div id="category-search-results"></div>
                                                                <div style="clear: both;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php
                                            // ======================================================
                                            // 最新章节
                                            $get_parent_story_title = function ($chapter_id) {
                                                $parent_id = wp_get_post_parent_id($chapter_id);
                                                if (!$parent_id) return '';
                                                $story = get_post($parent_id);
                                                return $story ? $story->post_title : '';
                                            };

                                            $latest = new WP_Query(array(
                                                'post_type'           => 'qimao_chapter',
                                                'posts_per_page'      => -1,
                                                'orderby'             => 'date',
                                                'order'               => 'DESC',
                                                'ignore_sticky_posts' => true,
                                            ));
                                            if ($latest->have_posts()) {
                                                $i = 1;
                                                while ($latest->have_posts()) {
                                                    $latest->the_post();
                                                    $story_name = $get_parent_story_title(get_the_ID());
                                                    $display = get_the_title();
                                                    echo '<div class="date-outer"><div class="date-posts"><div class="post-outer"><div class="post hentry">';
                                                    echo '<h3 class="post-title entry-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html($display) . '</a></h3>';
                                                    echo '<div class="post-header"><div class="post-header-line-1"></div></div>';
                                                    if ((($i - 1) % 15) == 0) {
                                                        echo '<div class="post-body entry-content"><div class="clamp-3">';
                                                        the_excerpt();
                                                        echo '</div><div style="clear: both;"></div></div>';
                                                    }
                                                    echo '</div></div></div></div>';
                                                    $i++;
                                                }
                                                wp_reset_postdata();
                                            }
                                            ?>

                                        </div>
                                        <div class='loader'></div>
                                        <div class='blog-pager' id='blog-pager'>
                                            <?php pagination(); ?>
                                        </div>
                                        <div class='clear'></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php get_template_part('sidebar', 'right'); ?>

                    </div>
                    <div style='clear: both'></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
