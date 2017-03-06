<?php if ( is_active_sidebar( 'sidebar-ca' ) ) : ?>
	<div id="tertiary" class="sidebar-container" role="complementary">
		<div class="sidebar-inner">
			<div class="widget-area">
        <?php echo do_shortcode("[learn_widget key='exlw-ca295c9d' tags='tag, another tag' limit=3 match_all_tags=true]"); ?>
				<?php dynamic_sidebar( 'sidebar-ca' ); ?>
			</div><!-- .widget-area -->
		</div><!-- .sidebar-inner -->
	</div><!-- #tertiary -->
<?php endif; ?>
