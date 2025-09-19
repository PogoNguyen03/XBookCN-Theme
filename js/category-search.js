(function($){
    function debounce(fn, wait){
        let t; return function(){
            const ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function(){ fn.apply(ctx, args); }, wait);
        };
    }

    function performGoogleSearch(query){
        if (!query || query.trim() === ''){
            alert('请输入搜索关键词');
            return;
        }
        
        // Lấy domain hiện tại (vd: book.xbookcn.net, example.com)
        const currentDomain = window.location.hostname;

        // Tạo Google search URL với site:domain hiện tại
        const searchQuery = encodeURIComponent(query.trim() + ' site:' + currentDomain);
        const googleSearchUrl = 'https://www.google.com/search?q=' + searchQuery;
        
        // Mở Google search ở tab mới
        window.open(googleSearchUrl, '_blank');
    }

    // 页面加载时显示提示
    $(document).ready(function(){
        showSearchHint();
    });

    $(document).on('focus', '#category-search-input', function(){
        showSearchHint();
    });

    $(document).on('click', '#category-search-button', function(){
        const query = $('#category-search-input').val();
        performGoogleSearch(query);
    });

    $(document).on('keypress', '#category-search-input', function(e){
        if (e.which === 13) {
            e.preventDefault();
            const query = $(this).val();
            performGoogleSearch(query);
        }
    });

    $(document).on('input', '#category-search-input', debounce(function(){
        const query = $(this).val();
        if (query.trim() === ''){
            showSearchHint();
        }
    }, 300));
})(jQuery);
