<?php
// Theme bootstrap (recreated)
if (!defined('ABSPATH')) exit;

function setup(){
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	register_nav_menus(array('primary' => 'Primary Menu'));
}
add_action('after_setup_theme','setup');

function enqueue(){
	// main stylesheet (your merged gettheme CSS is already in style.css)
	$ver = file_exists(get_template_directory() . '/style.css') ? filemtime(get_template_directory() . '/style.css') : '1.0.0';
	wp_enqueue_style('xbookcn-style', get_stylesheet_uri(), array(), $ver);

	// optional: load static bundle if present
	$bundle = get_template_directory() . '/css/css_bundle.css';
	if (file_exists($bundle)){
		wp_enqueue_style('xbookcn-bundle', get_template_directory_uri() . '/css/css_bundle.css', array('xbookcn-style'), '1.0');
	}
	
	// Enqueue infinite scroll script
	wp_enqueue_script('jquery');
	wp_enqueue_script('infinite-scroll', get_template_directory_uri() . '/js/infinite-scroll.js', array('jquery'), '1.0.0', true);
	
	// Localize script for AJAX
	wp_localize_script('infinite-scroll', 'ajax_object', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('infinite_scroll_nonce')
	));

	// Enqueue category search script on all pages (lightweight)
	// Enqueue category search script with cache-busting version
    $cat_js_path = get_template_directory() . '/js/category-search.js';
    $cat_js_ver  = file_exists($cat_js_path) ? filemtime($cat_js_path) : time();
    wp_enqueue_script('xbookcn-category-search', get_template_directory_uri() . '/js/category-search.js', array('jquery'), $cat_js_ver, true);
}
add_action('wp_enqueue_scripts','enqueue');

// Sidebar
function widgets(){
	register_sidebar(array(
		'name' => 'Sidebar Chính',
		'id' => 'sidebar-main',
		'before_widget' => '<div class="widget">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="sidebar-title">',
		'after_title' => '</h3>'
	));
}
add_action('widgets_init','widgets');

// Simple pagination wrapper
function pagination(){
	global $wp_query; 
	$big = 999999999; 
	$links = paginate_links(array(
		'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
		'format' => '?paged=%#%',
		'current' => max(1, get_query_var('paged')),
		'total' => $wp_query->max_num_pages,
		'type' => 'list',
		'prev_text' => '«',
		'next_text' => '»',
	));
	if ($links){ echo '<nav class="pagination">' . $links . '</nav>'; }
}

// Allow rendering Category Father template via query var fallback
add_filter('query_vars', function($vars){
    $vars[] = 'categoryfather';
    $vars[] = 'root';
    return $vars;
});

add_filter('template_include', function($template){
    $flag = get_query_var('categoryfather');
    if (!empty($flag)){
        $candidate = get_template_directory() . '/categoryfather.php';
        if (file_exists($candidate)){
            return $candidate;
        }
    }
    return $template;
});

// Add skin class for imported CSS variables
add_filter('body_class', function($classes){ $classes[] = 'gettheme-skin'; return $classes; });

// Thumbnail helper
add_action('after_setup_theme', function(){ add_image_size('book-thumb', 120, 180, true); });

// AJAX: search qimao stories by title (used by search box)
add_action('wp_ajax_search_qimao_stories', 'search_qimao_stories');
add_action('wp_ajax_nopriv_search_qimao_stories', 'search_qimao_stories');

function search_qimao_stories() {
    global $wpdb;

    $q     = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    $limit = isset($_GET['limit']) ? max(1, min(30, intval($_GET['limit']))) : 20;

    error_log("[search_qimao] START q='{$q}', limit={$limit}");

    if ($q === '') {
        error_log('[search_qimao] Empty query string → return []');
        wp_send_json_success(['items' => []]);
    }

    // --- STEP 1: Custom SQL LIKE with tokens ---
    $normalized = preg_replace(
        '/[\x{3000}\s，,。\.\-!！\?？、；;：:《》“”"\'\(\)\[\]]+/u',
        ' ',
        $q
    );
    $tokens = array_values(array_filter(array_map('trim', explode(' ', $normalized)), function ($s) {
        return $s !== '';
    }));

    $items = array();

    if (!empty($tokens)) {
        $likes = array();
        foreach ($tokens as $t) {
            $likes[] = '%' . $wpdb->esc_like($t) . '%';
        }
        $placeholders = implode(' OR post_title LIKE ', array_fill(0, count($likes), '%s'));

        $sql = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type='qimao_story' AND post_status='publish' 
             AND (post_title LIKE $placeholders) 
             ORDER BY post_date DESC LIMIT %d",
            ...array_merge($likes, array($limit))
        );

        error_log("[search_qimao] STEP1 SQL={$sql}");

        $ids = $wpdb->get_col($sql);
        error_log("[search_qimao] STEP1 found=" . count($ids));

        foreach ($ids as $pid) {
            $items[] = array(
                'id'    => (int) $pid,
                'title' => get_the_title($pid),
                'url'   => get_permalink($pid),
                'thumb' => get_the_post_thumbnail_url($pid, 'thumbnail'),
            );
        }
    }

    // --- STEP 2: Fallback WP_Query ---
    if (empty($items)) {
        $args = array(
            'post_type'           => 'qimao_story',
            'post_status'         => 'publish',
            's'                   => $q,
            'posts_per_page'      => $limit,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
			'post_parent'		  => '0',
        );

        error_log("[search_qimao] STEP2 WP_Query args=" . json_encode($args));

        $query = new WP_Query($args);
        error_log("[search_qimao] STEP2 found=" . intval($query->post_count));

        if ($query->have_posts()) {
            foreach ($query->posts as $p) {
                $items[] = array(
                    'id'    => $p->ID,
                    'title' => get_the_title($p->ID),
                    'url'   => get_permalink($p->ID),
                    'thumb' => get_the_post_thumbnail_url($p->ID, 'thumbnail'),
                );
            }
        }
        wp_reset_postdata();
    }

    // --- STEP 3: Simple LIKE fallback ---
    if (empty($items)) {
        $like = '%' . $wpdb->esc_like($q) . '%';
        $sql  = $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
             WHERE post_type='qimao_story' AND post_status='publish' 
             AND post_title LIKE %s 
             ORDER BY post_date DESC LIMIT %d",
            $like,
            $limit
        );

        error_log("[search_qimao] STEP3 SQL={$sql}");

        $ids = $wpdb->get_col($sql);
        error_log("[search_qimao] STEP3 found=" . count($ids));

        foreach ($ids as $pid) {
            $items[] = array(
                'id'    => (int) $pid,
                'title' => get_the_title($pid),
                'url'   => get_permalink($pid),
                'thumb' => get_the_post_thumbnail_url($pid, 'thumbnail'),
            );
        }
    }

    // --- STEP 4: Latest posts fallback ---
    if (empty($items)) {
        $latest = get_posts(array(
            'post_type'           => 'qimao_story',
            'post_status'         => 'publish',
            'numberposts'         => 20,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
        ));

        error_log("[search_qimao] STEP4 latest fallback count=" . count($latest));

        foreach ($latest as $p) {
            $items[] = array(
                'id'    => $p->ID,
                'title' => get_the_title($p->ID),
                'url'   => get_permalink($p->ID),
                'thumb' => get_the_post_thumbnail_url($p->ID, 'thumbnail'),
            );
        }
    }

    error_log("[search_qimao] END return count=" . count($items));

    wp_send_json_success(['items' => $items]);
}

// Removed old category search endpoint: now search only stories (qimao_story)

// Customizer: choose root category slug for homepage grouping
add_action('customize_register', function($wp_customize){
    $wp_customize->add_section('home', array(
        'title' => 'XBookCN Homepage',
        'priority' => 30,
    ));
    $wp_customize->add_setting('root_slug', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_title',
    ));
    $wp_customize->add_control('root_slug', array(
        'label' => 'Root category slug (group children)',
        'section' => 'home',
        'type' => 'text',
        'description' => 'Set a parent category slug; its child categories will render as sections. You can override via ?root=slug.',
    ));
});

// --- DEMO DATA SEEDER (optional) ---
// Visit: /?seed_xbookcn=1 while logged in as admin to insert demo categories and posts.
add_action('admin_init', function(){
	if (!is_user_logged_in() || !current_user_can('manage_options')) return;
	if (!isset($_GET['seed_xbookcn'])) return;
	if (get_option('demo_seeded')) return;

	$category_slugs = array(
		'tong-su' => '通俗小说',
		'do-thi' => '都市小说',
		'vo-hiep' => '武侠小说',
		'ky-ao' => '奇幻小说',
		'phieu-luu' => '冒险小说',
		'xuyen-khong' => '穿越小说',
		'den-toi' => '黑暗小说',
		'ngon-tinh' => '言情小说',
		'truyen-ngan' => '情色小说(短篇)',
		'binh-luan' => '文学评论',
	);

	$slug_to_term_id = array();
	foreach ($category_slugs as $slug => $name){
		$term = get_category_by_slug($slug);
		if (!$term){
			$result = wp_insert_term($name, 'category', array('slug' => $slug));
			if (!is_wp_error($result)){
				$slug_to_term_id[$slug] = (int)$result['term_id'];
			}
		} else {
			$slug_to_term_id[$slug] = (int)$term->term_id;
		}
	}

	// Create demo posts per category
	$lorem_titles = array(
		'少年阿宾','梦中女孩','风水相师','玉女盟','黑星女侠','炼狱天使','六朝云龙','永堕黑暗','情欲乐园','十大经典'
	);
	$author_id = get_current_user_id();
	foreach ($slug_to_term_id as $slug => $term_id){
		for ($i = 1; $i <= 8; $i++){
			$title = $lorem_titles[array_rand($lorem_titles)] . ' ' . $i;
			$post_id = wp_insert_post(array(
				'post_title' => $title,
				'post_content' => 'Demo content for ' . $title . '. Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
				'post_status' => 'publish',
				'post_author' => $author_id,
				'post_category' => array($term_id),
			));
		}
	}

	update_option('demo_seeded', 1);
	wp_safe_redirect(remove_query_arg('seed_xbookcn'));
	exit;
});

// AJAX handler for infinite scroll
add_action('wp_ajax_load_more_novels', 'load_more_novels');
add_action('wp_ajax_nopriv_load_more_novels', 'load_more_novels');

// AJAX handler để lấy URL của post
add_action('wp_ajax_get_post_url', 'get_post_url');
add_action('wp_ajax_nopriv_get_post_url', 'get_post_url');

// AJAX handler để load posts phía trên
add_action('wp_ajax_load_more_novels_up', 'load_more_novels_up');
add_action('wp_ajax_nopriv_load_more_novels_up', 'load_more_novels_up');

function load_more_novels() {
	// Verify nonce
	if (!wp_verify_nonce($_POST['nonce'], 'infinite_scroll_nonce')) {
		wp_die('Security check failed');
	}
	
	$page = intval($_POST['page']);
	$category_slug = sanitize_text_field($_POST['category']);
	$current_post_id = intval($_POST['current_post_id']); // Lấy post ID hiện tại
	$loaded_post_ids = isset($_POST['loaded_post_ids']) ? array_map('intval', $_POST['loaded_post_ids']) : array($current_post_id);
	
	// Tìm danh mục con dựa trên slug
	$category = get_category_by_slug($category_slug);
	if (!$category) {
		wp_send_json_error('Category not found: ' . $category_slug);
	}
	
	// Lấy tất cả posts trong danh mục, sắp xếp theo thứ tự tuần tự
	$all_posts = get_posts(array(
		'post_type' => 'post',
		'category__in' => array($category->term_id),
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC',
		'numberposts' => -1,
		'fields' => 'ids'
	));
	
	// Tìm chương tiếp theo dựa trên chương hiện tại
	$next_post_id = null;
	$current_index = array_search($current_post_id, $all_posts);
	
	if ($current_index !== false) {
		// Tìm chương tiếp theo chưa được load
		for ($i = $current_index + 1; $i < count($all_posts); $i++) {
			if (!in_array($all_posts[$i], $loaded_post_ids)) {
				$next_post_id = $all_posts[$i];
				break;
			}
		}
	}
	
	// Nếu không tìm thấy chương tiếp theo
	if (!$next_post_id) {
		wp_send_json_success(array(
			'html' => '',
			'has_more' => false,
			'current_page' => $page,
			'max_pages' => 0,
			'found_posts' => 0
		));
	}
	
	// Query chương tiếp theo
	$args = array(
		'post_type' => 'post',
		'p' => $next_post_id, // Lấy post cụ thể
		'post_status' => 'publish'
	);
	
	$query = new WP_Query($args);
	
	if ($query->have_posts()) {
		ob_start();
		
		while ($query->have_posts()) {
			$query->the_post();
			// Chỉ hiển thị phần nội dung chính, không có header và sidebar
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<!-- Box Giới thiệu -->
				<div class="novel-intro novel-box">
					<h2 class="intro-title"><?php the_title(); ?> <?php _e('内容简介', 'xbookcn'); ?></h2>
					<div class="novel-description">
						<?php the_content(); ?>
					</div>
				</div>
			</article>
			<?php
		}
		
		wp_reset_postdata();
		
		$html = ob_get_clean();
		
		// Tính toán has_more dựa trên việc còn chương tiếp theo không
		$has_more = false;
		if ($current_index !== false) {
			// Kiểm tra xem còn chương nào sau chương hiện tại chưa được load
			for ($i = $current_index + 1; $i < count($all_posts); $i++) {
				if (!in_array($all_posts[$i], $loaded_post_ids)) {
					$has_more = true;
					break;
				}
			}
		}
		
		// Debug: Log thông tin để kiểm tra
		error_log('AJAX Debug - Page: ' . $page . ', Current post ID: ' . $current_post_id . ', Current index: ' . $current_index . ', Total posts: ' . count($all_posts) . ', Loaded posts: ' . count($loaded_post_ids) . ', Has more: ' . ($has_more ? 'true' : 'false') . ', Next post ID: ' . $next_post_id);
		
		wp_send_json_success(array(
			'html' => $html,
			'has_more' => $has_more,
			'current_page' => $page,
			'max_pages' => $query->max_num_pages,
			'found_posts' => $query->found_posts
		));
	} else {
		wp_send_json_success(array(
			'html' => '',
			'has_more' => false
		));
	}
}

// AJAX handler để lấy URL của post
function get_post_url() {
	// Verify nonce
	if (!wp_verify_nonce($_POST['nonce'], 'infinite_scroll_nonce')) {
		wp_die('Security check failed');
	}
	
	$post_id = intval($_POST['post_id']);
	
	if (!$post_id) {
		wp_send_json_error('Invalid post ID');
	}
	
	$post_url = get_permalink($post_id);
	
	if ($post_url) {
		wp_send_json_success(array(
			'url' => $post_url
		));
	} else {
		wp_send_json_error('Post not found');
	}
}

// AJAX handler để load posts phía trên (scroll lên)
function load_more_novels_up() {
	// Verify nonce
	if (!wp_verify_nonce($_POST['nonce'], 'infinite_scroll_nonce')) {
		wp_die('Security check failed');
	}
	
	$page = intval($_POST['page']);
	$category_slug = sanitize_text_field($_POST['category']);
	$current_post_id = intval($_POST['current_post_id']);
	$loaded_post_ids = isset($_POST['loaded_post_ids']) ? array_map('intval', $_POST['loaded_post_ids']) : array($current_post_id);
	
	// Tìm danh mục con dựa trên slug
	$category = get_category_by_slug($category_slug);
	if (!$category) {
		wp_send_json_error('Category not found: ' . $category_slug);
	}
	
	// Lấy tất cả posts trong danh mục, sắp xếp theo thứ tự tuần tự
	$all_posts = get_posts(array(
		'post_type' => 'post',
		'category__in' => array($category->term_id),
		'post_status' => 'publish',
		'orderby' => 'title',
		'order' => 'ASC',
		'numberposts' => -1,
		'fields' => 'ids'
	));
	
	// Tìm chương trước đó (chưa được load)
	$previous_post_id = null;
	$current_index = array_search($current_post_id, $all_posts);
	
	if ($current_index !== false && $current_index > 0) {
		// Tìm chương trước đó chưa được load
		for ($i = $current_index - 1; $i >= 0; $i--) {
			if (!in_array($all_posts[$i], $loaded_post_ids)) {
				$previous_post_id = $all_posts[$i];
				break;
			}
		}
	}
	
	// Nếu không tìm thấy chương trước đó
	if (!$previous_post_id) {
		wp_send_json_success(array(
			'html' => '',
			'has_more' => false,
			'current_page' => $page,
			'max_pages' => 0,
			'found_posts' => 0
		));
	}
	
	// Query chương trước đó
	$args = array(
		'post_type' => 'post',
		'p' => $previous_post_id,
		'post_status' => 'publish'
	);
	
	$query = new WP_Query($args);
	
	if ($query->have_posts()) {
		ob_start();
		
		while ($query->have_posts()) {
			$query->the_post();
			// Chỉ hiển thị phần nội dung chính
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<!-- Box Giới thiệu -->
				<div class="novel-intro novel-box">
					<h2 class="intro-title"><?php the_title(); ?> <?php _e('内容简介', 'xbookcn'); ?></h2>
					<div class="novel-description">
						<?php the_content(); ?>
					</div>
				</div>
			</article>
			<?php
		}
		
		wp_reset_postdata();
		
		$html = ob_get_clean();
		
		// Tính toán has_more dựa trên việc còn chương trước đó không
		$remaining_posts_up = array();
		$current_index = array_search($current_post_id, $all_posts);
		if ($current_index !== false) {
			for ($i = 0; $i < $current_index; $i++) {
				if (!in_array($all_posts[$i], $loaded_post_ids)) {
					$remaining_posts_up[] = $all_posts[$i];
				}
			}
		}
		$has_more = count($remaining_posts_up) > 1; // Còn ít nhất 1 chương khác (không tính chương vừa load)
		
		wp_send_json_success(array(
			'html' => $html,
			'has_more' => $has_more,
			'current_page' => $page,
			'max_pages' => $query->max_num_pages,
			'found_posts' => $query->found_posts
		));
	} else {
		wp_send_json_success(array(
			'html' => '',
			'has_more' => false
		));
	}
}


