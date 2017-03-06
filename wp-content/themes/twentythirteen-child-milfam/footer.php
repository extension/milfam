<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */
?>

		</div><!-- #main -->

    <div class="logo-group">
      <div class="logo-group-logo"><img src="<?php echo bloginfo('stylesheet_directory');?>/images/usdod-400x200.png" /></div>
      <div class="logo-group-logo"><img src="<?php echo bloginfo('stylesheet_directory');?>/images/usda-400x200.png" /></div>
      <div class="logo-group-logo"><img src="<?php echo bloginfo('stylesheet_directory');?>/images/eX_ARLN_logo_400x200.png" /></div>
    </div>


		<footer id="colophon" class="site-footer" role="contentinfo">
			<?php get_sidebar( 'main' ); ?>
			<span id="extension_icon_512" class="pull-left noprint"><img id="extension_logo" src="<?php echo get_stylesheet_directory_uri(); ?>/images/extension_logo.jpg" alt="eXtension" width="58" height="58"></span>
        <ul class="inline noprint">
          <li><a href="http://www.extension.org/main/partners">Institutional Partners</a></li>
          <li><a href="http://www.extension.org/main/privacy">Privacy</a></li>
          <li><a href="http://www.extension.org/main/contact_us">Contact Us</a></li>
          <li><a href="http://www.extension.org/main/disclaimer">Disclaimer</a></li>
          <li class="last"><a href="http://www.extension.org/main/termsofuse">Terms of Use</a></li>
        </ul>
        <p>&nbsp;© <?php echo date('Y'); ?> eXtension. All rights reserved.</p>
			<div class="site-info">
				<?php do_action( 'twentythirteen_credits' ); ?>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	</div><!-- #page -->

	<?php wp_footer(); ?>
</body>
</html>
