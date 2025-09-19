<<<<<<< HEAD
# XBookCN-Theme
copy theme XBookCN Theme
=======
# XBookCN WordPress Theme

Theme WordPress tùy chỉnh được thiết kế dựa trên layout 2 cột với sidebar bên phải và nội dung chính bên trái.

## Tính năng

- **Layout 2 cột**: Nội dung chính bên trái, sidebar bên phải
- **Responsive**: Tự động điều chỉnh trên các thiết bị di động
- **Featured Posts**: Hiển thị bài viết nổi bật trên trang chủ
- **Custom Post Meta**: Hỗ trợ đánh dấu bài viết nổi bật
- **Widget Areas**: Sidebar và footer widget areas
- **Navigation Menus**: Menu chính và menu footer
- **Search Functionality**: Trang tìm kiếm tùy chỉnh
- **404 Page**: Trang lỗi 404 thân thiện
- **Comments Support**: Hỗ trợ bình luận

## Cài đặt

1. Upload thư mục theme vào `/wp-content/themes/`
2. Kích hoạt theme trong WordPress Admin > Appearance > Themes
3. Cấu hình menu trong Appearance > Menus
4. Thêm widget vào sidebar trong Appearance > Widgets

## Cấu trúc file

```
xbookcn/
├── style.css          # CSS chính và khai báo theme
├── index.php          # Template trang chủ
├── single.php         # Template chi tiết bài viết
├── archive.php        # Template danh mục/archive
├── page.php           # Template trang tĩnh
├── search.php         # Template tìm kiếm
├── 404.php            # Template lỗi 404
├── header.php         # Header template
├── footer.php         # Footer template
├── sidebar.php        # Sidebar template
├── functions.php      # Functions và hooks
└── README.md          # Hướng dẫn sử dụng
```

## Customization

### Thay đổi màu sắc

Chỉnh sửa các biến màu trong `style.css`:

```css
/* Màu chính */
#2c3e50  /* Màu text chính */
#3498db  /* Màu link và button */
#f8f9fa  /* Màu background */
```

### Thêm custom CSS

Thêm CSS tùy chỉnh vào `style.css` hoặc sử dụng WordPress Customizer.

### Widget Areas

Theme hỗ trợ 2 widget areas:
- **Main Sidebar**: Sidebar bên phải
- **Footer Widget Area**: Footer

### Menu Locations

Theme hỗ trợ 2 menu locations:
- **Primary Menu**: Menu chính
- **Footer Menu**: Menu footer

## Hooks và Filters

### Actions
- `setup`: Theme setup
- `enqueue_scripts`: Enqueue scripts và styles
- `widgets_init`: Register widget areas

### Filters
- `excerpt_length`: Độ dài excerpt
- `excerpt_more`: Text "read more"
- `body_classes`: Custom body classes

## Functions

### Custom Functions
- `get_featured_posts($limit)`: Lấy bài viết nổi bật
- `get_recent_posts($limit)`: Lấy bài viết mới nhất
- `get_popular_posts($limit)`: Lấy bài viết phổ biến
- `pagination()`: Hiển thị phân trang

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Version

- **Version**: 1.0
- **WordPress**: 5.0+
- **PHP**: 7.4+

## License

GPL v2 or later

## Support

Để được hỗ trợ, vui lòng liên hệ qua:
- Email: support@xbookcn.com
- Website: https://xbookcn.com
>>>>>>> 73e0b06 (finish)
