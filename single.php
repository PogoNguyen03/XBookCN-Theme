<?php
/**
 * Template for displaying single chapter with infinite scroll + loading gif
 */

get_header();

// 获取当前章节
if (have_posts()) : while (have_posts()) : the_post();

    $current_chapter_id = get_the_ID();
    $parent_story_id    = wp_get_post_parent_id($current_chapter_id);

    // 获取父级小说的所有章节
    $chapters = get_posts([
        'post_type'      => 'qimao_chapter',
        'post_parent'    => $parent_story_id,
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ]);

    // 查找下一章
    $next_chapter_url = null;
    if ($chapters) {
        $index = array_search($current_chapter_id, $chapters);
        if ($index !== false && isset($chapters[$index + 1])) {
            $next_chapter_url = get_permalink($chapters[$index + 1]);
        }
    }

    // 输出 link rel="next"
    if ($next_chapter_url) {
        echo '<link rel="next" href="' . esc_url($next_chapter_url) . '" />';
    }
    ?>
<?php get_template_part('button-back'); ?>
<div class="novel-single-container" data-category="<?php echo esc_attr($current_category ?? ''); ?>">
    <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="novel-content-wrapper">
            <div id="infinite-chapters-container" class="">
                <article id="post-<?php the_ID(); ?>" class="novel-chapter">
                    <div class="novel-intro novel-box">
                        <h2 class="intro-title">
                            <?php the_title(); ?>
                            <?php _e('内容简介', 'xbookcn'); ?>
                        </h2>
                        <div class="novel-description">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </article>
            </div>

            <!-- 加载动画 GIF -->
           <div id="chapter-loading" style="display:none; text-align:center; padding:20px;">
                <div class="lds-spinner">
                    <div></div><div></div><div></div><div></div><div></div><div></div>
                    <div></div><div></div><div></div><div></div><div></div><div></div>
                </div>
            </div> 
           
        </div>
        
    </div>
      <!-- 右侧边栏 -->
     <?php get_template_part('sidebar-right'); ?>
</div>

<?php endwhile; endif; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let loading = false;
    let nextChapterUrl = document.querySelector("link[rel=next]")?.href || null;
    const loadingEl = document.getElementById("chapter-loading");
    const container = document.querySelector("#infinite-chapters-container");

    console.log("[初始化] 下一章 URL:", nextChapterUrl);

    async function loadNextChapter() {
        if (!nextChapterUrl || loading) {
            console.log("[跳过] 不加载，因为:", { nextChapterUrl, loading });
            return;
        }
        loading = true;
        loadingEl.style.display = "block"; 
        console.log("[加载] 开始加载:", nextChapterUrl);

        try {
            const res = await fetch(nextChapterUrl);
            console.log("[请求] 状态:", res.status);
            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            const newChapter = doc.querySelector(".novel-chapter");
            if (newChapter) {
                console.log("[追加] 添加章节:", nextChapterUrl);

                // 给新章节绑定 data-url
                newChapter.setAttribute("data-url", nextChapterUrl);
                container.appendChild(newChapter);

                // 更新历史记录为新章节
                window.history.pushState({}, "", nextChapterUrl);
                console.log("[历史记录] pushState:", nextChapterUrl);

                // 更新下一章链接
                nextChapterUrl = doc.querySelector("link[rel=next]")?.href || null;
                console.log("[更新] 新的下一章 URL:", nextChapterUrl);

                observeChapters(); // 重新绑定 observer
            } else {
                console.warn("[警告] 页面中未找到 .novel-chapter:", nextChapterUrl);
                nextChapterUrl = null;
            }
        } catch (err) {
            console.error("[错误] 加载章节时出错:", err);
        } finally {
            loading = false;
            loadingEl.style.display = "none"; 
            console.log("[完成] 加载结束，loading 重置。");
        }
    }

    // 观察器：滚动时更新 URL
    let observer;
    function observeChapters() {
        if (observer) observer.disconnect();
        console.log("[观察器] 重新初始化...");

        observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                const url = entry.target.getAttribute("data-url");
                console.log("[观察器检测]", {url, isIntersecting: entry.isIntersecting, ratio: entry.intersectionRatio});
                if (entry.isIntersecting && url) {
                    window.history.replaceState({}, "", url);
                    console.log("[历史记录] replaceState:", url);
                }
            });
        }, {
            root: null,
            rootMargin: "0px",
            threshold: 0
        });

        document.querySelectorAll(".novel-chapter").forEach(chapter => {
            console.log("[观察器] 跟踪:", chapter.getAttribute("data-url"));
            observer.observe(chapter);
        });
    }

    // 给初始章节绑定 URL
    document.querySelectorAll(".novel-chapter").forEach((chap, idx) => {
        if (!chap.hasAttribute("data-url")) {
            const canonicalUrl = document.querySelector("link[rel=canonical]")?.href || window.location.href;
            chap.setAttribute("data-url", canonicalUrl);
            console.log("[初始化] 设置章节 data-url", idx, ":", canonicalUrl);
        } else {
            console.log("[初始化] 已存在 data-url:", chap.getAttribute("data-url"));
        }
    });

    observeChapters();

    window.addEventListener("scroll", function () {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 300) {
            console.log("[滚动] 到达页面底部，加载下一章...");
            loadNextChapter();
        }
    });
});
</script>
