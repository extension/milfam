<?php
/**
 * The sidebar for the CA template
 */

if ( ! is_active_sidebar( 'sidebar-ca' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area" role="complementary">
	<?php dynamic_sidebar( 'sidebar-ca' ); ?>
</aside><!-- #secondary -->
