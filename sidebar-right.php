<?php
/**
 * Sidebar Right (column-right-outer)
 */
?>
<div class='column-right-outer'>
    <div class='column-right-inner'>
        <aside>
            <div class='sidebar section' id='sidebar-right-1'>
                <div class='widget PageList' id='PageList1'>
                    <h2>分类</h2>
                    <div class='widget-content'>
                        <ul>
                            <?php
                            $root_slug = isset($_GET['root']) ? sanitize_title($_GET['root']) : get_theme_mod('root_slug', '');
                            $parent = $root_slug ? get_category_by_slug($root_slug) : null;
                            if ($parent) {
                                $children = get_categories(array(
                                    'parent' => (int) $parent->term_id,
                                    'hide_empty' => 0,
                                    'orderby' => 'name',
                                    'order' => 'ASC',
                                ));
                            } else {
                                // Không có root: liệt kê toàn bộ danh mục cấp con (không phải cấp cha)
                                $exclude_id = (int) get_option('default_category');
                                $top = get_categories(array(
                                    'parent' => 0,
                                    'hide_empty' => 0,
                                    'exclude' => $exclude_id ? array($exclude_id) : array(),
                                ));
                                $children = array();
                                foreach ($top as $t) {
                                    $direct_children = get_categories(array(
                                        'parent' => (int) $t->term_id,
                                        'hide_empty' => 0,
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                    ));
                                    $children = array_merge($children, $direct_children);
                                }
                                // Deduplicate by term_id
                                $unique = array();
                                foreach ($children as $c) {
                                    $unique[$c->term_id] = $c;
                                }
                                $children = array_values($unique);
                            }
                            foreach ($children as $cat) {
                                echo '<li><a href="' . esc_url(get_category_link($cat)) . '">' . esc_html($cat->name) . '</a></li>';
                            }
                            ?>
                        </ul>
                        <div class='clear'></div>
                    </div>
                </div>
                <div class='widget LinkList' id='LinkList2'>
                    <h2>列表</h2>
                    <div class='widget-content'>
                        <ul>
                            <?php
                            $grandchildren = array();
                            if ($parent) {
                                $children = get_categories(array(
                                    'parent' => (int) $parent->term_id,
                                    'hide_empty' => 0,
                                ));
                                foreach ($children as $child) {
                                    $gc = get_categories(array(
                                        'parent' => (int) $child->term_id,
                                        'hide_empty' => 0,
                                        'orderby' => 'name',
                                        'order' => 'ASC',
                                    ));
                                    $grandchildren = array_merge($grandchildren, $gc);
                                }
                            } else {
                                $exclude_id = (int) get_option('default_category');
                                $top = get_categories(array(
                                    'parent' => 0,
                                    'hide_empty' => 0,
                                    'exclude' => $exclude_id ? array($exclude_id) : array(),
                                ));
                                foreach ($top as $t) {
                                    $children = get_categories(array(
                                        'parent' => (int) $t->term_id,
                                        'hide_empty' => 0,
                                    ));
                                    foreach ($children as $child) {
                                        $gc = get_categories(array(
                                            'parent' => (int) $child->term_id,
                                            'hide_empty' => 0,
                                            'orderby' => 'name',
                                            'order' => 'ASC',
                                        ));
                                        $grandchildren = array_merge($grandchildren, $gc);
                                    }
                                }
                            }
                            // DEBUG + YÊU CẦU: Chỉ lấy ~20 truyện qimao_story mới nhất, KHÔNG lọc theo danh mục
                            $args = array(
                                'post_type' => 'qimao_story',
                                'posts_per_page' => 20,
                                'orderby' => 'date',
                                'order' => 'DESC',
                                'ignore_sticky_posts' => true,
                                'no_found_rows' => true,
                            );

                            // Debug: ghi log tham số truy vấn
                            if (function_exists('error_log')) {
                                error_log('[sidebar-right] qimao_story latest args: ' . wp_json_encode($args));
                            }

                            $stories = new WP_Query($args);
                            // Debug: ghi log số bài lấy được
                            if (function_exists('error_log')) {
                                error_log('[sidebar-right] qimao_story latest found: ' . intval($stories->post_count));
                            }
                            if ($stories->have_posts()) {
                                while ($stories->have_posts()) {
                                    $stories->the_post();
                                    echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                                }
                                wp_reset_postdata();
                            }
                            ?>
                        </ul>
                        <div class='clear'></div>
                    </div>
                </div>
                <div class="widget LinkList bsfloat" id="LinkList1" style="width:200px;">
                    <h2>导航</h2>
                    <div class="widget-content">
                        <ul>
                            <li><a href="/">首页</a></li>
                            <?php
                            // Hiển thị danh mục CHA (cấp 1)
                            $exclude_id = (int) get_option('default_category');
                            $parents = get_categories(array(
                                'parent' => 0,
                                'hide_empty' => 0,
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'exclude' => $exclude_id ? array($exclude_id) : array(),
                            ));
                            foreach ($parents as $cat) {
                                echo '<li><a href="' . esc_url(get_category_link($cat)) . '">' . esc_html($cat->name) . '</a></li>';
                            }
                            ?>
                        </ul>
                        <div class="clear"></div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const floatingBox = document.querySelector(".widget.LinkList.bsfloat");
    const header = document.querySelector("header");


    if (floatingBox && header) {
        const headerHeight = header.offsetHeight;
        const boxOffsetTop = floatingBox.offsetTop;
        function checkScroll() {
            const scrollY = window.scrollY;
            console.log("scrollY:", scrollY);

            if (scrollY >= 1250) {
                floatingBox.classList.add("bsfloating");
            } else {
                floatingBox.classList.remove("bsfloating");
            }
        }

        // Gọi 1 lần khi load xong
        checkScroll();

        // Gọi lại mỗi khi cuộn
        window.addEventListener("scroll", checkScroll);
    } else {
        console.warn("OK");
    }
});
</script>

