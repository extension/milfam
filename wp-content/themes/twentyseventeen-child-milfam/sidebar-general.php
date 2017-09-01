<?php
/**
 * The sidebar for the CA template
 */

if ( ! is_active_sidebar( 'sidebar-general' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area sidebar-general" role="complementary">
	<?php dynamic_sidebar( 'sidebar-general' ); ?>
</aside><!-- #secondary -->
