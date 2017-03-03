<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?>

		</div><!-- #content -->

    <div class="partner-icons">
      <div class="wrap">
        <div class="partner-icon"><img src="<?php echo bloginfo('stylesheet_directory');?>/images/usdod-400x200.png" /></div><div class="partner-icon"><img src="<?php echo bloginfo('stylesheet_directory');?>/images/usda-400x200.png" /></div><div class="partner-icon"><img src="<?php echo bloginfo('stylesheet_directory');?>/images/ex_i-three_w_tagline_500x275.png" /></div>
      </div>
    </div>

		<footer id="colophon" class="site-footer" role="contentinfo">
			<div class="wrap">
				<?php
				get_template_part( 'template-parts/footer/footer', 'widgets' );

				if ( has_nav_menu( 'social' ) ) : ?>
					<nav class="social-navigation" role="navigation" aria-label="<?php _e( 'Footer Social Links Menu', 'twentyseventeen' ); ?>">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'social',
								'menu_class'     => 'social-links-menu',
								'depth'          => 1,
								'link_before'    => '<span class="screen-reader-text">',
								'link_after'     => '</span>' . twentyseventeen_get_svg( array( 'icon' => 'chain' ) ),
							) );
						?>
					</nav><!-- .social-navigation -->

        <?php endif; ?>

        <div class="extension-meta-footer">
          <ul>
            <li><a href="https://extension.org/membership/current/">eXtension Members</a></li>
            <li><a href="https://extension.org/privacy/">Privacy</a></li>
            <li><a href="https://extension.org/contact/">Contact Us</a></li>
            <li><a href="https://extension.org/terms-of-use/">Terms of Use</a></li>
          </ul>
          <p>&nbsp;© <?php echo date('Y'); ?> eXtension. All rights reserved.</p
        </div>
			</div><!-- .wrap -->
		</footer><!-- #colophon -->
	</div><!-- .site-content-contain -->
</div><!-- #page -->
<?php wp_footer(); ?>

</body>
</html>
