<?php

/**
 *
 */
class Ssb_Settings {

	private $settings_api;

	function __construct() {

		include_once  SSB_PLUGIN_DIR . '/classes/ssb-settings-strucutre.php';
		$this->settings_api = new Ssb_Settings_Structure();
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_ssb_help', array( $this, 'download_help' ) );
		add_action( 'wp_ajax_ssb_export', array( $this, 'export' ) );
		add_action( 'wp_ajax_ssb_import', array( $this, 'import' ) );

	}

	function admin_menu() {

		if ( current_user_can( 'activate_plugins' ) ) {
			add_menu_page( 'Simple Social Buttons ', 'Social Buttons ', 'activate_plugins', 'simple-social-buttons', array( $this, 'plugin_page' ), 'dashicons-share', 100 );

			add_submenu_page( 'simple-social-buttons', 'Settings', 'Settings', 'manage_options', 'simple-social-buttons' );
			do_action( 'ssb_add_pro_submenu' );

			add_submenu_page( 'simple-social-buttons', __( 'Help', 'simple-social-buttons' ), __( 'Help', 'simple-social-buttons' ), 'manage_options', 'ssb-help', array( $this, 'help_page' ) );

			add_submenu_page( 'simple-social-buttons', __( 'Import and export settings', 'simple-social-buttons' ), __( 'Import / Export', 'simple-social-buttons' ), 'manage_options', 'ssb-import-export', array( $this, 'import_export_page' ) );

		}

	}

	function get_settings_sections() {
			$sections = array(
				array(
					'id'       => 'ssb_networks',
					'title'    => __( 'Social Buttons', 'simple-social-buttons' ),
					'priority' => '10',
				),
				array(
					'id'       => 'ssb_themes',
					'title'    => __( 'Social Buttons Designs', 'simple-social-buttons' ),
					'priority' => '15',
				),
				array(
					'id'       => 'ssb_positions',
					'title'    => __( 'Social Buttons Postions', 'simple-social-buttons' ),
					'priority' => '20',
				),
				array(
					'id'       => 'ssb_sidebar',
					'title'    => __( 'Sidebar', 'simple-social-buttons' ),
					'priority' => '25',
				),
				array(
					'id'       => 'ssb_media',
					'title'    => __( 'On Media', 'simple-social-buttons' ),
					'priority' => '40',
				),
				array(
					'id'       => 'ssb_popup',
					'title'    => __( 'Popup', 'simple-social-buttons' ),
					'priority' => '45',
				),
				array(
					'id'       => 'ssb_flyin',
					'title'    => __( 'Fly In', 'simple-social-buttons' ),
					'priority' => '50',
				),
				array(
					'id'       => 'ssb_inline',
					'title'    => __( 'InLine', 'simple-social-buttons' ),
					'priority' => '30',
				),
				array(
					'id'       => 'ssb_advanced',
					'title'    => __( 'Additional features', 'simple-social-buttons' ),
					'priority' => '99',
				),
			);

			$setting_section = apply_filters( 'ssb_settings_panel', $sections );

			usort( $setting_section, array( $this, 'sort_array' ) );

			return $setting_section;
	}

	public function sort_array( $a, $b ){
		return $a['priority'] - $b['priority'];
	}

	public function get_current_post_types() {

		$post_types_list = array(
			'home' => 'Home'
		);

		$args = array(
			'public' => true,
		);

		$post_types = get_post_types( $args );

		foreach ( $post_types as $post_type ) {
			$post_types_list[ $post_type ] = ucfirst( $post_type );
		}

		return $post_types_list;
	}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
	function get_settings_fields() {
			$post_types = $this->get_current_post_types();
			$ssb_positions_options = apply_filters( 'ssb_positions_options',  array(
				'sidebar' => 'Sidebar',
				'inline'  => 'Inline',
				'media'   => 'Media',
				'popup'   => 'Popup',
				'flyin'   => 'Fly In',
			) ) ;

			$ssb_sidebar = array(
				array(
					'name'    => 'orientation',
					'label'   => __( 'Sidebar Orientation', 'simple-social-buttons' ),
					'desc'    => __( '<h4>Display Settings</h4>', 'simple-social-buttons' ),
					'type'    => 'ssb_select',
					'default' => 'left',
					'options' => array(
						'left'  => 'Left',
						'right' => 'Right',
					),
					'priority' => '5',
				),
				array(
					'name'    => 'animation',
					'label'   => __( 'Intro Animation', 'simple-social-buttons' ),
					'type'    => 'ssb_select',
					'default' => 'no-animation',
					'options' => array(
						'no-animation'     => 'No',
						'right-in' => 'From Right',
						'top-in' => 'From Top',
						'bottom-in' => 'From Bottom',
						'left-in' => 'From Left',
						'fade-in' => 'FadeIn',
					),
					'priority' => '10',
				),
				array(
					'name'  => 'share_counts',
					'label' => __( 'Display Share Counts', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '15',
				),
				array(
					'name'  => 'total_share',
					'label' => __( 'Display Total Shares', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '20',
				),
				array(
					'name'  => 'icon_space',
					'label' => __( 'Add Icon Spacing', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '25',
				),
				array(
					'name'              => 'icon_space_value',
					'type'              => 'ssb_text',
					'label'             => 'Enter the Space in Pixel',
					'sanitize_callback' => 'sanitize_text_field',
					'priority' => '30',
				),
				array(
					'name'  => 'hide_mobile',
					'label' => __( 'Hide On Mobile Devices', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '35',
				),
				array(
					'name'    => 'posts',
					'label'   => __( 'Post type Settings', 'simple-social-buttons' ),
					'desc'    => __( 'Multi checkbox description', 'simple-social-buttons' ),
					'type'    => 'ssb_post_types',
				  'default' => array('post' => 'post', 'page' => 'page'),
					'options' => $post_types,
					'priority' => '99',
				),
				array(
					'name'		=>  'go_pro',
					'type'    => 'ssb_go_pro',
					'label'   => __( 'Want even more fine tuned control over your Sidebar Social Buttons Styling?', 'simple-social-buttons' ),
					'desc'    => __( 'By upgrading to Simple Social Buttons Pro, you get access to Styling Social buttons of your own choice that matches with your website color schemes. These social buttons will help in driving more engagement and traffic to your site. Some of the Pro features include: Show Social media buttons on Images/Photos, Social Popups on exit/intent, Social Flyin slides and so much more!', 'simple-social-buttons' ),
					'link'		=> 'http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=settings-sidebar&utm_campaign=pro-upgrade'
				),
			);

			$ssb_sidebar =  apply_filters( 'ssb_sidebar_fields', $ssb_sidebar );

			$ssb_inline = array(
				array(
					'name'    => 'location',
					'label'   => __( 'Icon Position', 'simple-social-buttons' ),
					'desc'    => __( '<h4>Display Settings</h4>', 'simple-social-buttons' ),
					'type'    => 'ssb_select',
					'default' => 'above',
					'options' => array(
						'above'       => 'Above The Content',
						'below'       => 'Below The Content',
						'above_below' => 'Above + Below The Content',
					),
					'priority' => '5',
				),
				array(
					'name'    => 'icon_alignment',
					'label'   => __( 'Icon Alignment', 'simple-social-buttons' ),
					'type'    => 'ssb_select',
					'default' => 'left',
					'options' => array(
						'left'     => 'Left',
						'centered' => 'Centered',
						'right'    => 'Right',
					),
					'priority' => '10',
				),
				array(
					'name'    => 'animation',
					'label'   => __( 'Animation', 'simple-social-buttons' ),
					'type'    => 'ssb_select',
					'default' => 'no-animation',
					'options' => array(
						'no-animation' => 'No',
						'bottom-in'    => 'From bottom',
						'top-in'       => 'From top',
						'left-in'      => 'From left',
						'right-in'     => 'From right',
						'fade-in'      => 'Fade In',
					),
					'priority' => '15',
				),
				array(
					'name'  => 'share_counts',
					'label' => __( 'Display Share Counts', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '20',
				),
				array(
					'name'  => 'total_share',
					'label' => __( 'Display Total Shares', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '25',
				),
				array(
					'name'  => 'icon_space',
					'label' => __( 'Add Icon Spacing', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '30',
				),
				array(
					'name'              => 'icon_space_value',
					'type'              => 'ssb_text',
					'label'             => 'Enter the Space in Pixel',
					'sanitize_callback' => 'sanitize_text_field',
					'priority' => '35',
				),
				array(
					'name'  => 'hide_mobile',
					'label' => __( 'Hide On Mobile Devices', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '40',
				),
				array(
					'name'  => 'show_on_category',
					'label' => __( 'Show at Category pages', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '45',
				),
				array(
					'name'  => 'show_on_archive',
					'label' => __( 'Show at Archive pages', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '50',

				),
				array(
					'name'  => 'show_on_tag',
					'label' => __( 'Show at Tag pages', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '55',
				),
				array(
					'name'  => 'show_on_search',
					'label' => __( 'Show at Search pages', 'simple-social-buttons' ),
					'type'  => 'ssb_checkbox',
					'priority' => '56',
				),
				array(
					'name'  => 'share_title',
					'label' => __( 'Share Title', 'simple-social-buttons' ),
					'type'  => 'ssb_text',
					'priority' => '57',
				),
				array(
					'name'    => 'posts',
					'label'   => __( 'Post type Settings', 'simple-social-buttons' ),
					'desc'    => __( 'Multi checkbox description', 'simple-social-buttons' ),
					'type'    => 'ssb_post_types',
					'default' => array('post' => 'post', 'page' => 'page'),
					'options' => $post_types,
					'priority' => '99',
				),
				array(
					'name'		=>  'go_pro',
					'type'    => 'ssb_go_pro',
					'label'   => __( 'Want to style the Inline Social buttons matches your theme colors?', 'simple-social-buttons' ),
					'desc'    => __( 'By upgrading to Simple Social Buttons Pro, you get access to Styling Social buttons of your own choice that matches with your website color schemes. These social buttons will help in driving more engagement and traffic to your site. Some of the Pro features include: Show Social media buttons on Images/Photos, Social Popups on exit/intent, Social Flyin slides and so much more!', 'simple-social-buttons' ),
					'link'		=> 'http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=settings-inline&utm_campaign=pro-upgrade'
				),
			);

			$ssb_inline = apply_filters( 'ssb_inline_fields', $ssb_inline );

			$settings_fields = array(
				'ssb_networks' => array(
					array(
						'name' => 'icon_selection',
						'type' => 'ssb_icon_selection',
					)
				),
				'ssb_themes' => array(
					array(
						'name'    => 'icon_style',
						'label'   => __( 'Icon Style', 'simple-social-buttons' ),
						'type'    => 'icon_style',
						'options' => array(
							'sm-round'           => 'sm-round',
							'simple-round'       => 'simple-round',
							'round-txt'          => 'round-txt',
							'round-btm-border'   => 'round-btm-border',
							'flat-button-border' => 'flat-button-border',
							'round-icon'         => 'round-icon',
							'simple-icons'       => 'simple-icons',
						),
					),
				),
				'ssb_positions' => array(
					array(
						'name'    => 'position',
						'label'   => __( 'Postions', 'simple-social-buttons' ),
						'desc'    => __( 'Multi checkbox description', 'simple-social-buttons' ),
						'type'    => 'position',
						'default' => 'inline',
						'options' => $ssb_positions_options,
					),

				),
				'ssb_sidebar' => $ssb_sidebar,
				'ssb_inline'  => $ssb_inline,
				'ssb_media' => array(
					array(
						'name' 		=> 'go_pro',
						'type'    => 'ssb_go_pro',
						'label'   => __( 'Show Social Sharing buttons on images or photos in your posts/pages.', 'simple-social-buttons' ),
						'desc'    => __( 'By upgrading to Simple Social Buttons Pro, you get access to Styling Social buttons of your own choice that matches with your website color schemes. These social buttons will help in driving more engagement and traffic to your site. Some of the Pro features include: Show Social media buttons on Images/Photos, Social Popups on exit/intent, Social Flyin slides and so much more!', 'simple-social-buttons' ),
						'link'		=> 'http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=settings-media&utm_campaign=pro-upgrade'
					),
				),
				'ssb_popup' => array(
					array(
						'name' 		=> 'go_pro',
						'type'    => 'ssb_go_pro',
						'label'   => __( 'Get more engagement and shares on scrolling and exit intent to visitors.', 'simple-social-buttons' ),
						'desc'    => __( 'By upgrading to Simple Social Buttons Pro, you get access to Styling Social buttons of your own choice that matches with your website color schemes. These social buttons will help in driving more engagement and traffic to your site. Some of the Pro features include: Show Social media buttons on Images/Photos, Social Popups on exit/intent, Social Flyin slides and so much more!', 'simple-social-buttons' ),
						'link'		=> 'http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=settings-popup&utm_campaign=pro-upgrade'
					),
				),
				'ssb_flyin' => array(
					array(
						'name' 		=> 'go_pro',
						'type'    => 'ssb_go_pro',
						'label'   => __( 'Advanced Fly ins with animations to have more Social shares.', 'simple-social-buttons' ),
						'desc'    => __( 'By upgrading to Simple Social Buttons Pro, you get access to Styling Social buttons of your own choice that matches with your website color schemes. These social buttons will help in driving more engagement and traffic to your site. Some of the Pro features include: Show Social media buttons on Images/Photos, Social Popups on exit/intent, Social Flyin slides and so much more!', 'simple-social-buttons' ),
						'link'		=> 'http://www.WPBrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=settings-flyin&utm_campaign=pro-upgrade'
					),
				),
				'ssb_advanced' => array(
					array(
						'name'              => 'twitter_handle',
						'type'              => 'ssb_text',
						'label'             => __( 'Twitter @username:', 'simple-social-buttons' ),
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			);

			$settings_fields = apply_filters( 'ssb_setting_fields' , $settings_fields, $post_types );

			return $settings_fields;
	}

	function plugin_page() {
		echo '<div class="wrap">';
			$this->settings_api->settings_header();
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();
			$this->settings_api->settings_sidebar();
		echo '</div>';
	}

		/**
		 * Get all the pages
		 *
		 * @return array page names with key value pairs
		 */
	function get_pages() {
			$pages = get_pages();
			$pages_options = array();
		if ( $pages ) {
			foreach ( $pages as $page ) {
					$pages_options[ $page->ID ] = $page->post_title;
			}
		}

			return $pages_options;
	}

	function admin_init() {

				// set the settings
				$this->settings_api->set_sections( $this->get_settings_sections() );
				$this->settings_api->set_fields( $this->get_settings_fields() );

				// initialize settings
				$this->settings_api->admin_init();
	}

	function help_page() {

		include SSB_PLUGIN_DIR . 'classes/ssb-logs.php';

			$html = '<div class="simple-social-buttons-help-page">';
			$html .= '<h2>Help & Troubleshooting</h2>';
			$html .= sprintf( __( 'Free support is available on the %1$s plugin support forums%2$s.', 'simple-social-buttons' ), '<a href="https://wordpress.org/support/plugin/simple-social-buttons" target="_blank">', '</a>' );
			$html .="<br /><br />";
			$html .= sprintf( __( 'For premium features, add-ons and priority email support, %1$s upgrade to pro%2$s.', 'simple-social-buttons' ), '<a href="https://wpbrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=help-page&utm_campaign=pro-upgrade" target="_blank">', '</a>' );
			$html .="<br /><br />";
			$html .= 'Found a bug or have a feature request? Please submit an issue <a href="https://wpbrigade.com/contact/" target="_blank">here</a>!';
			$html .= '<pre><textarea rows="25" cols="75" readonly="readonly">';
			$html .= Ssb_Logs_Info::get_sysinfo();
			$html .= '</textarea></pre>';
			$html .= '<input type="button" class="button simple-social-buttons-log-file" value="' . __( 'Download Log File', 'simple-social-buttons' ) . '"/>';
			$html .= '<span class="ssb-log-file-sniper"><img src="'. admin_url( 'images/wpspin_light.gif' ) .'" /></span>';
			$html .= '<span class="ssb-log-file-text">Simple Social Buttons Log File Downloaded Successfully!</span>';
			$html .= '</div>';
			echo $html;
	}

	public function download_help() {

		include SSB_PLUGIN_DIR . 'classes/ssb-logs.php';

		echo Ssb_Logs_Info::get_sysinfo();
		wp_die();
	}

	/**
	 * Include Import/Export Page.
	 *
	 * @since 2.0.4
	 */
	public function import_export_page() {
		include_once SSB_PLUGIN_DIR . '/inc/ssb-import-export.php';
	}

	/**
	 * Export Settings
	 *
	 * @since 2.0.4
	 */
	public function export() {

		$sections = $this->get_settings_sections();
		$settings = array();

		foreach ( $sections as $section ) {
			$result                       = get_option( $section ['id'] );
			$settings [ $section ['id'] ] = $result;
		}

		echo json_encode( $settings );
		wp_die();
	}

	/**
	 * Import Settings.
	 *
	 * @since 2.0.4
	 */
	public function  import(){

		$ssb_imp_tmp_name =  $_FILES['file']['tmp_name'];
		$ssb_file_content = file_get_contents( $ssb_imp_tmp_name );
		$ssb_json = json_decode( $ssb_file_content, true );

		if ( json_last_error() == JSON_ERROR_NONE ) {
			foreach ( $ssb_json as $id => $array ) {
				update_option( $id, $array );
			}
		} else {
			echo "error";
		}
		wp_die();
	}


}

new Ssb_Settings();
