<?php
add_action( 'init', 'ssb_upgrade_routine_2' );

/**
 * Upgrade Routine for V 2.0
 *
 * @since 2.0.0
 */
function ssb_upgrade_routine_2() {

	if ( get_option( 'run_ssb_update_routine_2' ) || get_option( 'ssb_networks' ) ) {
		return;
	}

	// Store Icon Order.
	if ( get_option( 'ssb_icons_order' ) ) {
		$_default = array(
			'icon_selection' => get_option( 'ssb_icons_order' ),
		);
		update_option( 'ssb_networks', $_default );
		delete_option( 'ssb_icons_order' );
	} else {
		$_default = array(
			'icon_selection' => 'fbshare,twitter,googleplus,linkedin',
		);
		update_option( 'ssb_networks', $_default );
	}

	// If settings avaliable.
	if ( get_option( 'ssb_pr_settings' ) ) {

		$_old_value = get_option( 'ssb_pr_settings' );

		// Set Position of Inline Icons.
		$before_post = isset( $_old_value['beforepost'] ) && $_old_value['beforepost'] == '1' ? true : false;
		$after_post = isset( $_old_value['afterpost'] ) && $_old_value['afterpost'] == '1' ? true : false;

		if ( $before_post && $after_post ) {
			$inline_location = 'above_below';
		} elseif ( $before_post ) {
			$inline_location = 'above';
		} else {
			$inline_location = 'below';
		}

		// Page.
		$before_page = isset( $_old_value['beforepage'] ) && $_old_value['beforepage'] == '1' ? true : false;
		$after_page  = isset( $_old_value['afterpage'] ) && $_old_value['afterpage'] == '1' ? true : false;

		$inline_posts = array(
			'post' => 'post',
		);

		if ( $before_page || $after_page ) {
			$inline_posts['page'] = 'page';
		}

		$_default_inline = array(
			'location' => $inline_location,
			'posts'    => $inline_posts,
		);

		$on_archive  = isset( $_old_value['showarchive'] ) && $_old_value['showarchive'] == '1' ? true : false;
		$on_tag      = isset( $_old_value['showtag'] ) && $_old_value['showtag'] == '1' ? true : false;
		$on_category = isset( $_old_value['showcategory'] ) && $_old_value['showcategory'] == '1' ? true : false;

		if ( $on_archive ) {
			$_default_inline['show_on_archive'] = 1;
		}
		if ( $on_tag ) {
			$_default_inline['show_on_tag'] = 1;
		}
		if ( $on_category ) {
			$_default_inline['show_on_category'] = 1;
		}
		update_option( 'ssb_inline', $_default_inline );
		// End of Inline Icons.

		$_default_postion = array(
			'position' => array(
				'inline' => 'inline',
			),
		);
		update_option( 'ssb_positions', $_default_postion );

		$_default_theme = array(
			'icon_style' => 'sm-round',
		);
		  update_option( 'ssb_themes', $_default_theme );

		// Set Extra tab settings.
		if ( isset( $_old_value['twitterusername'] ) ) {
			update_option(
				'ssb_extra', array(
					'twitter_handle' => $_old_value['twitterusername'],
				)
			);
		}

		delete_option( 'ssb_pr_settings' );
	}

	update_option( 'run_ssb_update_routine_2', 'yes' );
}

?>
