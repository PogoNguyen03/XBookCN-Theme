        </main>
        
        <?php get_sidebar(); ?>
    </div>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-widgets">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                        <?php dynamic_sidebar('footer-1'); ?>
                    <?php endif; ?>
                </div>
                
                <div class="footer-info">
                    <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All rights reserved.', 'xbookcn'); ?></p>
                    <p><?php _e('Powered by', 'xbookcn'); ?> <a href="https://wordpress.org/">WordPress</a> <?php _e('and', 'xbookcn'); ?> <a href="#">XBookCN Theme</a></p>
                </div>
                
                <?php if (has_nav_menu('footer')) : ?>
                    <nav class="footer-navigation">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'footer',
                            'menu_id'        => 'footer-menu',
                            'menu_class'     => 'footer-nav-menu',
                            'container'      => false,
                        ));
                        ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>
