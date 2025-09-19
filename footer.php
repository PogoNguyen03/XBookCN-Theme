<?php
/**
 * The template for displaying the footer
 */
?>
<style>
    .footer-bottom{
        display: flex; 
        justify-content: center;
    }
    @media (max-width: 768px) {
        .footer-bottom{
            font-size: 12px;   
        }
    }
</style>

<footer id="site-footer" class="site-footer">
    <div class="footer-widgets">
        <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
            <div class="footer-widget-area">
                <?php dynamic_sidebar( 'footer-1' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
            <div class="footer-widget-area">
                <?php dynamic_sidebar( 'footer-2' ); ?>
            </div>
        <?php endif; ?>

        <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
            <div class="footer-widget-area">
                <?php dynamic_sidebar( 'footer-3' ); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="footer-bottom" >
        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
