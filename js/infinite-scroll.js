// jQuery(document).ready(function($) {
//     let isLoading = false;
//     let hasMorePosts = true;
//     let hasMorePostsUp = true; // Có thể load thêm posts phía trên
//     let currentPage = 1;
//     let currentPageUp = 1; // Trang cho scroll lên
//     let currentCategory = '';
//     let loadedPostIds = []; // Danh sách các post IDs đã được load
//     let allPosts = []; // Danh sách tất cả posts để biết thứ tự
//     let currentPostId = ''; // Post ID hiện tại đang xem
//     let lastScrollTop = 0; // Vị trí scroll trước đó
//     let scrollTimeout = null; // Throttle scroll events
//     let urlUpdateTimeout = null; // Debounce URL updates
    
//     // Lấy danh mục con từ URL hoặc từ data attribute
//     function getCurrentCategory() {
//         // Lấy danh mục từ URL nếu có
//         const urlParams = new URLSearchParams(window.location.search);
//         const categoryFromUrl = urlParams.get('category');
        
//         if (categoryFromUrl) {
//             return categoryFromUrl;
//         }
        
//         // Hoặc lấy từ data attribute
//         const categoryElement = $('.novel-single-container').data('category');
//         return categoryElement || '';
//     }
    
//     // Lấy post ID hiện tại (chương đang xem)
//     function getCurrentPostId() {
//         return currentPostId;
//     }
    
//     // Cập nhật post ID hiện tại
//     function updateCurrentPostId(postId) {
//         currentPostId = postId;
//         console.log('Current post ID updated to:', currentPostId);
//     }
    
//     // Lấy danh sách tất cả post IDs đã được load
//     function getAllLoadedPostIds() {
//         const postIds = [];
//         // Chỉ lấy từ infinite-chapters-container (bao gồm cả chương ban đầu)
//         $('#infinite-chapters-container article').each(function() {
//             const id = $(this).attr('id');
//             if (id) {
//                 postIds.push(id.replace('post-', ''));
//             }
//         });
//         return postIds;
//     }
    
//     // Cập nhật URL cho chương mới được load
//     function updateURLForNewChapter($newPosts) {
//         if ($newPosts.length > 0) {
//             const $latestChapter = $newPosts.last(); // Lấy chương mới nhất
//             const postId = $latestChapter.attr('id');
//             if (postId) {
//                 const postIdNumber = postId.replace('post-', '');
//                 // Lấy URL của post từ WordPress
//                 $.ajax({
//                     url: ajax_object.ajax_url,
//                     type: 'POST',
//                     data: {
//                         action: 'get_post_url',
//                         post_id: postIdNumber,
//                         nonce: ajax_object.nonce
//                     },
//                     success: function(response) {
//                         if (response.success && response.data.url) {
//                             // Cập nhật URL mà không reload trang
//                             history.pushState(null, '', response.data.url);
//                             console.log('URL updated to:', response.data.url);
//                         }
//                     }
//                 });
//             }
//         }
//     }
    
//     // Cập nhật URL khi scroll (không load lại chương)
//     function updateURLOnScroll() {
//         const currentScrollTop = $(window).scrollTop();
        
//         // Nếu scroll lên hoặc xuống
//         if (currentScrollTop !== lastScrollTop) {
//             console.log('Scroll detected, current scroll:', currentScrollTop, 'last scroll:', lastScrollTop);
            
//             const $articles = $('#infinite-chapters-container article');
//             console.log('Total articles found:', $articles.length);
            
//             // Debug: Log tất cả articles để xem có gì
//             $articles.each(function(index) {
//                 const $article = $(this);
//                 const articleId = $article.attr('id');
//                 console.log(`Debug - Article ${index}: ${articleId}`);
//             });
            
//             if ($articles.length > 0) {
//                 // Tìm chương đang visible trên màn hình với logic cải tiến
//                 let currentVisibleChapter = null;
//                 const windowHeight = $(window).height();
//                 const scrollTop = $(window).scrollTop();
//                 const scrollCenter = scrollTop + windowHeight / 2; // Điểm giữa màn hình
                
//                 // Logic nâng cao: Tìm chương phù hợp dựa trên vị trí scroll
//                 let bestChapter = null;
//                 let minDistance = Infinity;
                
//                 $articles.each(function(index) {
//                     const $article = $(this);
//                     const articleTop = $article.offset().top;
//                     const articleBottom = articleTop + $article.outerHeight();
//                     const articleId = $article.attr('id');
                    
//                     console.log(`Article ${index}: ID=${articleId}, Top=${articleTop}, Bottom=${articleBottom}, Scroll=${scrollTop}, Center=${scrollCenter}`);
                    
//                     // Tính khoảng cách từ scroll center đến chương này
//                     let distance;
//                     if (scrollCenter < articleTop) {
//                         // Scroll center ở trên chương này
//                         distance = articleTop - scrollCenter;
//                     } else if (scrollCenter > articleBottom) {
//                         // Scroll center ở dưới chương này
//                         distance = scrollCenter - articleBottom;
//                     } else {
//                         // Scroll center ở trong chương này
//                         distance = 0;
//                     }
                    
//                     console.log(`Article ${index} distance: ${distance}`);
                    
//                     // Chọn chương có khoảng cách nhỏ nhất
//                     if (distance < minDistance) {
//                         minDistance = distance;
//                         bestChapter = $article;
//                         console.log('Best chapter so far:', articleId, 'distance:', distance);
//                     }
//                 });
                
//                 // Nếu khoảng cách nhỏ hơn 200px, chọn chương đó
//                 if (bestChapter && minDistance < 200) {
//                     currentVisibleChapter = bestChapter;
//                     console.log('Selected chapter:', currentVisibleChapter.attr('id'), 'distance:', minDistance);
//                 }
                
//                 // Nếu tìm thấy chương đang visible và khác với currentPostId
//                 if (currentVisibleChapter) {
//                     const visiblePostId = currentVisibleChapter.attr('id');
//                     if (visiblePostId) {
//                         const actualPostId = visiblePostId.replace('post-', '');
//                         console.log('Visible post ID:', actualPostId, 'Current post ID:', currentPostId);
                        
//                         if (actualPostId !== currentPostId) {
//                             console.log('Updating URL for post ID:', actualPostId);
                            
//                             // Debounce URL updates để tránh cập nhật quá nhiều lần
//                             if (urlUpdateTimeout) {
//                                 clearTimeout(urlUpdateTimeout);
//                             }
                            
//                             urlUpdateTimeout = setTimeout(function() {
//                                 updateCurrentPostId(actualPostId);
//                                 updateURLForNewChapter($(currentVisibleChapter));
//                             }, 300); // Debounce 300ms
//                         } else {
//                             console.log('Same post ID, no URL update needed');
//                         }
//                     }
//                 } else {
//                     console.log('No visible chapter found');
//                 }
//             }
//         }
        
//         lastScrollTop = currentScrollTop;
//     }
    
//     // Kiểm tra xem có cần load thêm posts không (scroll xuống)
//     function checkIfShouldLoad() {
//         if (isLoading || !hasMorePosts) return false;
        
//         const scrollTop = $(window).scrollTop();
//         const windowHeight = $(window).height();
//         const documentHeight = $(document).height();
        
//         // Load khi còn cách cuối trang 200px
//         return (scrollTop + windowHeight >= documentHeight - 200);
//     }
    
//     // Kiểm tra xem có cần auto-load chương tiếp theo không
//     function checkIfShouldAutoLoad() {
//         if (isLoading || !hasMorePosts) return false;
        
//         const windowHeight = $(window).height();
//         const documentHeight = $(document).height();
//         const scrollTop = $(window).scrollTop();
        
//         console.log('Checking auto-load - Window:', windowHeight, 'Document:', documentHeight, 'Scroll:', scrollTop);
        
//         // Tính toán vùng nội dung thực tế
//         const $contentArea = $('#infinite-chapters-container');
//         const contentTop = $contentArea.offset().top;
//         const contentHeight = $contentArea.outerHeight();
//         const contentBottom = contentTop + contentHeight;
        
//         console.log('Content area - Top:', contentTop, 'Height:', contentHeight, 'Bottom:', contentBottom);
        
//         // Kiểm tra các điều kiện auto-load
//         const conditions = {
//             // 1. Trang quá ngắn (không đủ fill màn hình)
//             pageTooShort: documentHeight < windowHeight + 100,
            
//             // 2. Nội dung quá ngắn (không đủ fill màn hình)
//             contentTooShort: contentHeight < windowHeight * 0.6, // Ít hơn 60% màn hình
            
//             // 3. Đã scroll gần hết nội dung (người dùng đã đọc hết)
//             scrolledToEnd: scrollTop + windowHeight >= contentBottom - 50,
            
//             // 4. Có nhiều khoảng trống dưới nội dung
//             hasEmptySpace: contentBottom < scrollTop + windowHeight - 100
//         };
        
//         console.log('Auto-load conditions:', conditions);
        
//         // Nếu thỏa mãn bất kỳ điều kiện nào
//         if (conditions.pageTooShort || conditions.contentTooShort || 
//             (conditions.scrolledToEnd && conditions.hasEmptySpace)) {
//             console.log('Auto-loading next chapter - Reason:', Object.keys(conditions).filter(k => conditions[k]));
//             return true;
//         }
        
//         return false;
//     }
    
//     // Kiểm tra xem có cần load thêm posts không (scroll lên)
//     function checkIfShouldLoadUp() {
//         if (isLoading || !hasMorePostsUp) return false;
        
//         const scrollTop = $(window).scrollTop();
        
//         // Load khi còn cách đầu trang 200px
//         return (scrollTop <= 200);
//     }
    
//     // Load posts mới
//     function loadMorePosts() {
//         console.log('loadMorePosts called - isLoading:', isLoading, 'hasMorePosts:', hasMorePosts);
//         if (isLoading || !hasMorePosts) return;
        
//         isLoading = true;
//         currentPage++;
//         console.log('Loading page:', currentPage, 'Category:', getCurrentCategory(), 'Post ID:', getCurrentPostId());
        
//         // Hiển thị loading indicator
//         showLoadingIndicator();
        
//         $.ajax({
//             url: ajax_object.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'load_more_novels',
//                 page: currentPage,
//                 category: getCurrentCategory(),
//                 current_post_id: getCurrentPostId(),
//                 loaded_post_ids: getAllLoadedPostIds(),
//                 nonce: ajax_object.nonce
//             },
//             success: function(response) {
//                 console.log('AJAX Response:', response);
//                 if (response.success && response.data.html) {
//                     console.log('HTML received:', response.data.html.length, 'characters');
//                     console.log('Has more:', response.data.has_more);
                    
//                     // Thêm posts mới vào container riêng cho infinite chapters
//                     const $newPosts = $(response.data.html);
//                     $('#infinite-chapters-container').append($newPosts);
                    
//                     // Thêm animation class cho posts mới
//                     $newPosts.addClass('new-post');
                    
//                     // Cập nhật URL cho chương mới được load
//                     updateURLForNewChapter($newPosts);
                    
//                     // Cập nhật current post ID (chương mới nhất)
//                     const $latestChapter = $newPosts.last();
//                     const latestPostId = $latestChapter.attr('id');
//                     if (latestPostId) {
//                         updateCurrentPostId(latestPostId.replace('post-', ''));
//                     }
                    
//                     // Cập nhật danh sách loaded post IDs
//                     loadedPostIds = getAllLoadedPostIds();
//                     console.log('Loaded post IDs:', loadedPostIds);
                    
//                     // Ẩn loading indicator
//                     hideLoadingIndicator();
                    
//                     // Kiểm tra xem còn posts không
//                     if (!response.data.has_more) {
//                         console.log('No more posts available');
//                         hasMorePosts = false;
//                         hideLoadMoreButton();
//                         showEndIndicator();
//                     }
//                 } else {
//                     console.log('No HTML received or error:', response);
//                     hasMorePosts = false;
//                     hideLoadingIndicator();
//                     showEndIndicator();
//                 }
//             },
//             error: function(xhr, status, error) {
//                 console.log('AJAX Error:', status, error);
//                 console.log('Response:', xhr.responseText);
//                 hasMorePosts = false;
//                 hideLoadingIndicator();
//                 showEndIndicator();
//             },
//             complete: function() {
//                 isLoading = false;
//             }
//         });
//     }
    
//     // Load posts phía trên (scroll lên)
//     function loadMorePostsUp() {
//         console.log('loadMorePostsUp called - isLoading:', isLoading, 'hasMorePostsUp:', hasMorePostsUp);
//         if (isLoading || !hasMorePostsUp) return;
        
//         isLoading = true;
//         currentPageUp++;
//         console.log('Loading page up:', currentPageUp, 'Category:', getCurrentCategory(), 'Post ID:', getCurrentPostId());
        
//         // Hiển thị loading indicator
//         showLoadingIndicator();
        
//         $.ajax({
//             url: ajax_object.ajax_url,
//             type: 'POST',
//             data: {
//                 action: 'load_more_novels_up',
//                 page: currentPageUp,
//                 category: getCurrentCategory(),
//                 current_post_id: getCurrentPostId(),
//                 loaded_post_ids: getAllLoadedPostIds(),
//                 nonce: ajax_object.nonce
//             },
//             success: function(response) {
//                 console.log('AJAX Response Up:', response);
//                 if (response.success && response.data.html) {
//                     console.log('HTML received up:', response.data.html.length, 'characters');
//                     console.log('Has more up:', response.data.has_more);
                    
//                     // Thêm posts mới vào đầu container
//                     const $newPosts = $(response.data.html);
//                     $('#infinite-chapters-container').prepend($newPosts);
                    
//                     // Thêm animation class cho posts mới
//                     $newPosts.addClass('new-post');
                    
//                     // Cập nhật URL cho chương mới được load (chương đầu tiên)
//                     updateURLForNewChapter($newPosts.first());
                    
//                     // Cập nhật current post ID (chương đầu tiên)
//                     const $firstChapter = $newPosts.first();
//                     const firstPostId = $firstChapter.attr('id');
//                     if (firstPostId) {
//                         updateCurrentPostId(firstPostId.replace('post-', ''));
//                     }
                    
//                     // Cập nhật danh sách loaded post IDs
//                     loadedPostIds = getAllLoadedPostIds();
//                     console.log('Loaded post IDs up:', loadedPostIds);
                    
//                     // Ẩn loading indicator
//                     hideLoadingIndicator();
                    
//                     // Kiểm tra xem còn posts không
//                     if (!response.data.has_more) {
//                         console.log('No more posts available up');
//                         hasMorePostsUp = false;
//                     }
//                 } else {
//                     console.log('No HTML received or error up:', response);
//                     hasMorePostsUp = false;
//                     hideLoadingIndicator();
//                 }
//             },
//             error: function(xhr, status, error) {
//                 console.log('AJAX Error up:', status, error);
//                 console.log('Response up:', xhr.responseText);
//                 hasMorePostsUp = false;
//                 hideLoadingIndicator();
//             },
//             complete: function() {
//                 isLoading = false;
//             }
//         });
//     }
    
//     // Hiển thị loading indicator
//     function showLoadingIndicator() {
      
//     }
    
//     // Ẩn loading indicator
//     function hideLoadingIndicator() {
//         $('#infinite-loading').remove();
//     }
    
//     // Hiển thị indicator khi hết posts
//     function showEndIndicator() {
        
//     }
    
//     // Event listener cho scroll
//     $(window).on('scroll', function() {
//         // Throttle scroll events để tránh gọi quá nhiều lần
//         if (scrollTimeout) {
//             clearTimeout(scrollTimeout);
//         }
        
//         scrollTimeout = setTimeout(function() {
//             console.log('Scroll event triggered');
            
//             // Cập nhật URL khi scroll (không load lại chương)
//             updateURLOnScroll();
            
//             if (checkIfShouldLoad()) {
//                 console.log('Should load more posts (down)');
//                 loadMorePosts();
//             } else if (checkIfShouldAutoLoad()) {
//                 console.log('Auto-loading next chapter');
//                 loadMorePosts();
//             }
//             // Tắt scroll lên - chỉ load chương tiếp theo
//             // } else if (checkIfShouldLoadUp()) {
//             //     console.log('Should load more posts (up)');
//             //     loadMorePostsUp();
//             // }
//         }, 100); // Throttle 100ms
//     });
    
//     // Xử lý khi người dùng sử dụng nút Back/Forward
//     window.addEventListener('popstate', function(event) {
//         // Khi URL thay đổi, có thể cần load lại nội dung
//         console.log('URL changed via browser navigation:', window.location.href);
//         // Có thể thêm logic để load nội dung tương ứng với URL mới
//     });
    
//     // Khởi tạo
//     currentCategory = getCurrentCategory();
//     loadedPostIds = getAllLoadedPostIds();
//     lastScrollTop = $(window).scrollTop(); // Khởi tạo vị trí scroll ban đầu
    
//     // Khởi tạo current post ID từ post gốc
//     const initialArticle = $('.novel-single-container > article').first();
//     if (initialArticle.length) {
//         const initialId = initialArticle.attr('id');
//         if (initialId) {
//             updateCurrentPostId(initialId.replace('post-', ''));
//         }
//     }
    
//     console.log('Initial loaded post IDs:', loadedPostIds);
//     console.log('Initial current post ID:', currentPostId);
//     console.log('Initial scroll position:', lastScrollTop);
    
//     // Kiểm tra xem có cần auto-load sau khi load xong
//     setTimeout(function() {
//         if (checkIfShouldAutoLoad()) {
//             console.log('Auto-loading next chapter on page load');
//             loadMorePosts();
//         } else {
//             // Nếu không auto-load nhưng vẫn có thể load thêm, hiển thị nút
//             showLoadMoreButton();
//         }
//     }, 1000); // Delay 1 giây để đảm bảo trang đã load xong
    
//     // Hiển thị nút "Load Next Chapter" khi cần thiết
//     function showLoadMoreButton() {
//         if (hasMorePosts && !$('#load-more-button').length) {
//             const $button = $('<button id="load-more-button" class="load-more-btn">Load Next Chapter</button>');
//             $('#infinite-chapters-container').after($button);
            
//             $button.on('click', function() {
//                 $(this).remove();
//                 loadMorePosts();
//             });
//         }
//     }
    
//     // Ẩn nút khi đã load xong
//     function hideLoadMoreButton() {
//         $('#load-more-button').remove();
//     }
// });
