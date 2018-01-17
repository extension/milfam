<?php


/**
 * Admin class
 *
 * Gets only initiated if this plugin is called inside the admin section ;)
 */
if ( ! class_exists( 'SimpleSocialButtonsPR_Admin' ) ) :

	class SimpleSocialButtonsPR_Admin extends SimpleSocialButtonsPR {

		function __construct() {
			parent::__construct();

			include_once  SSB_PLUGIN_DIR . '/classes/ssb-settings.php';

			add_action( 'add_meta_boxes', array( $this, 'ssb_meta_box' ) );
			add_action( 'save_post', array( $this, 'ssb_save_meta' ), 10, 2 );

			add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

			add_action( 'admin_footer', array( $this, 'add_deactive_modal' ) );
			add_action( 'wp_ajax_ssb_deactivate', array( $this, 'ssb_deactivate' ) );
			add_action( 'admin_init', array( $this, 'review_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'in_admin_header', array( $this, 'skip_notices' ), 100000 );

		}

		/**
		 * Add Settings links in plugins.php
		 *
		 * @since 1.9
		 */
		public function plugin_action_links( $links, $file ) {
			static $this_plugin;

			if ( ! $this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="' . admin_url( 'admin.php?page=simple-social-buttons' ) . '">' . __( 'Settings', 'simple-social-buttons' ) . '</a>';
				array_unshift( $links, $settings_link );
			}

			return $links;
		}

		function admin_enqueue_scripts( $page ) {

			if ( 'toplevel_page_simple-social-buttons' == $page  || 'social-buttons_page_ssb-help' == $page || 'widgets.php' == $page ) {
				wp_enqueue_style( 'ssb-admin-cs', plugins_url( 'assets/css/admin.css',plugin_dir_path( __FILE__ ) ), false, SSB_VERSION );
				wp_enqueue_script( 'ssb-admin-js', plugins_url( 'assets/js/admin.js',plugin_dir_path( __FILE__ ) ), array( 'jquery', 'jquery-ui-sortable' ), SSB_VERSION );
			}
		}

		/**
		 * Register meta box to hide/show SSB plugin on single post or page
		 */
		public function ssb_meta_box() {
			$postId = isset( $_GET['post'] ) ? $_GET['post'] : false;
			$postType = get_post_type( $postId );

			if ( $postType != 'page' && $postType != 'post' ) {
				return false;
			}

			$currentSsbHide = get_post_custom_values( $this->hideCustomMetaKey, $postId );

			if ( $currentSsbHide[0] == 'true' ) {
				$checked = true;
			} else {
				$checked = false;
			}

			// Rendering meta box
			if ( ! function_exists( 'add_meta_box' ) ) {
				include( 'includes/template.php' );
			}
			add_meta_box(
				'ssb_meta_box', __( 'SSB Settings', 'simple-social-buttons' ), array( $this, 'render_ssb_meta_box' ), $postType, 'side', 'default', array(
					'type' => $postType,
					'checked' => $checked,
				)
			);
		}

		/**
		 * Showing custom meta field
		 */
		public function render_ssb_meta_box( $post, $metabox ) {
			wp_nonce_field( plugin_basename( __FILE__ ), 'ssb_noncename' );
			?>

		  <label for="<?php echo $this->hideCustomMetaKey; ?>"><input type="checkbox" id="<?php echo $this->hideCustomMetaKey; ?>" name="<?php echo $this->hideCustomMetaKey; ?>" value="true"
				<?php if ( $metabox['args']['checked'] ) : ?>
				 checked="checked"
        <?php endif; ?>/>
      &nbsp;<?php echo __( 'Hide Simple Social Buttons', 'simple-social-buttons' ); ?></label>
		<?php
		}


		/**
		 * Saving custom meta value
		 */
		public function ssb_save_meta( $post_id, $post ) {
			$postId = (int) $post_id;
			// Verify if this is an auto save routine.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			if ( ! isset( $_POST['ssb_noncename'] ) ) {
				return;
			}

			// Verify this came from the our screen and with proper authorization
			if ( ! wp_verify_nonce( $_POST['ssb_noncename'], plugin_basename( __FILE__ ) ) ) {
				return;
			}

			// Check permissions
			if ( 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				}
			} else {
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}

			// Saving data
			$newValue = (isset( $_POST[ $this->hideCustomMetaKey ] )) ? $_POST[ $this->hideCustomMetaKey ] : 'false';

			update_post_meta( $postId, $this->hideCustomMetaKey, $newValue );
		}


		/**
		 * Show the popup on pluing deactivate
		 *
		 * @since 1.9.0
		 */
		function add_deactive_modal() {
			global $pagenow;

			if ( 'plugins.php' !== $pagenow ) {
				return;
			}

			include SSB_PLUGIN_DIR . 'inc/ssb-deactivate-form.php';
		}

		/**
		 * Send the user responce to api.
		 *
		 * @since 1.9.0
		 */
		function ssb_deactivate() {
			$email         = get_option( 'admin_email' );
			$_reason       = sanitize_text_field( wp_unslash( $_POST['reason'] ) );
			$reason_detail = sanitize_text_field( wp_unslash( $_POST['reason_detail'] ) );
			$reason        = '';

			if ( $_reason == '1' ) {
				$reason = 'I only needed the plugin for a short period';
			} elseif ( $_reason == '2' ) {
				$reason = 'I found a better plugin';
			} elseif ( $_reason == '3' ) {
				$reason = 'The plugin broke my site';
			} elseif ( $_reason == '4' ) {
				$reason = 'The plugin suddenly stopped working';
			} elseif ( $_reason == '5' ) {
				$reason = 'I no longer need the plugin';
			} elseif ( $_reason == '6' ) {
				$reason = 'It\'s a temporary deactivation. I\'m just debugging an issue.';
			} elseif ( $_reason == '7' ) {
				$reason = 'Other';
			}
			$fields = array(
				'email'             => $email,
				'website'           => get_site_url(),
				'action'            => 'Deactivate',
				'reason'            => $reason,
				'reason_detail'     => $reason_detail,
				'blog_language'     => get_bloginfo( 'language' ),
				'wordpress_version' => get_bloginfo( 'version' ),
				'plugin_version'    => SSB_VERSION,
				'php_version'				=> PHP_VERSION,
				'plugin_name'       => 'Simple Social Buttons',
			);

			$response = wp_remote_post(
				SSB_FEEDBACK_SERVER, array(
					'method'      => 'POST',
					'timeout'     => 5,
					'httpversion' => '1.0',
					'blocking'    => false,
					'headers'     => array(),
					'body'        => $fields,
				)
			);

			wp_die();
		}

		/**
		 * Check either to show notice or not.
		 *
		 * @since 1.9.0
		 */
		public function review_notice() {

			$this->review_dismissal();
			$this->review_prending();

			$review_dismissal   = get_site_option( 'ssb_review_dismiss' );
			if ( 'yes' == $review_dismissal ) {
				return;
			}

			$activation_time    = get_site_option( 'ssb_active_time' );
			if ( ! $activation_time ) {

				$activation_time = time();
				add_site_option( 'ssb_active_time', $activation_time );
			}

			// 1296000 = 15 Days in seconds.
			if ( time() - $activation_time > 1296000 ) {
				add_action( 'admin_notices' , array( $this, 'review_notice_message' ) );
			}

		}

		/**
		 * Show review Message After 15 days.
		 *
		 * @since 1.9.0
		 */
		public function review_notice_message() {

			$scheme      = ( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_QUERY ) ) ? '&' : '?';
			$url         = $_SERVER['REQUEST_URI'] . $scheme . 'ssb_review_dismiss=yes';
			$dismiss_url = wp_nonce_url( $url, 'ssb-review-nonce' );

			$_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'ssb_review_later=yes';
			$later_url   = wp_nonce_url( $_later_link, 'ssb-review-nonce' );

			?>
		  <style media="screen">
		  .ssb-review-notice { padding: 15px 15px 15px 0; background-color: #fff; border-radius: 3px; margin: 20px 20px 0 0; border-left: 4px solid transparent; } .ssb-review-notice:after { content: ''; display: table; clear: both; }
		  .ssb-review-thumbnail { width: 114px; float: left; line-height: 80px; text-align: center; border-right: 4px solid transparent; }
		  .ssb-review-thumbnail img { width: 74px; vertical-align: middle; }
		  .ssb-review-text { overflow: hidden; }
		  .ssb-review-text h3 { font-size: 24px; margin: 0 0 5px; font-weight: 400; line-height: 1.3; }
		  .ssb-review-text p { font-size: 13px; margin: 0 0 5px; }
		  .ssb-review-ul { margin: 0; padding: 0; }
		  .ssb-review-ul li { display: inline-block; margin-right: 15px; }
		  .ssb-review-ul li a { display: inline-block; color: #10738B; text-decoration: none; padding-left: 26px; position: relative; }
		  .ssb-review-ul li a span { position: absolute; left: 0; top: -2px; }
		  </style>
		  <div class="ssb-review-notice">
			<div class="ssb-review-thumbnail">
		  <img src="<?php echo plugins_url( '../assets/images/ssb_grey_logo.png', __FILE__ ); ?>" alt="">
		</div>
		<div class="ssb-review-text">
		<h3><?php _e( 'Leave A Review?', 'simple-social-buttons' ); ?></h3>
		<p><?php _e( 'We hope you\'ve enjoyed using Simple Social Buttons! Would you consider leaving us a review on WordPress.org?', 'simple-social-buttons' ); ?></p>
		<ul class="ssb-review-ul"><li><a href="https://wordpress.org/support/plugin/simple-social-buttons/reviews/?filter=5" target="_blank"><span class="dashicons dashicons-external"></span><?php _e( 'Sure! I\'d love to!', 'simple-social-buttons' ); ?></a></li>
		  <li><a href="<?php echo $dismiss_url; ?>"><span class="dashicons dashicons-smiley"></span><?php _e( 'I\'ve already left a review', 'simple-social-buttons' ); ?></a></li>
		  <li><a href="<?php echo $later_url; ?>"><span class="dashicons dashicons-calendar-alt"></span><?php _e( 'Maybe Later', 'simple-social-buttons' ); ?></a></li>
		  <li><a href="<?php echo $dismiss_url; ?>"><span class="dashicons dashicons-dismiss"></span><?php _e( 'Never show again', 'simple-social-buttons' ); ?></a></li></ul>
		</div>
		</div>
		<?php
		}

		/**
		 * Set time to current so review notice will popup after 15 days
		 *
		 * @since 1.9.0
		 */
		function review_prending() {

			// delete_site_option( 'ssb_review_dismiss' );
			if ( ! is_admin() ||
			! current_user_can( 'manage_options' ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'ssb-review-nonce' ) ||
			! isset( $_GET['ssb_review_later'] ) ) {

				return;
			}

			// Reset Time to current time.
			update_site_option( 'ssb_active_time', time() );

		}

		/**
		 *   Check and Dismiss review message.
		 *
		 *   @since 1.9.0
		 */
		private function review_dismissal() {

			// delete_site_option( 'ssb_review_dismiss' );
			if ( ! is_admin() ||
			! current_user_can( 'manage_options' ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'ssb-review-nonce' ) ||
			! isset( $_GET['ssb_review_dismiss'] ) ) {

				return;
			}

			add_site_option( 'ssb_review_dismiss', 'yes' );
		}


		/**
		 * Skip the all the notice from settings page.
		 *
		 * @since 1.9.0
		 */
		function skip_notices() {

			if ( 'toplevel_page_simple-social-buttons' === get_current_screen()->id ) {

				global $wp_filter;

				if ( is_network_admin() and isset( $wp_filter['network_admin_notices'] ) ) {
					unset( $wp_filter['network_admin_notices'] );
				} elseif ( is_user_admin() and isset( $wp_filter['user_admin_notices'] ) ) {
					unset( $wp_filter['user_admin_notices'] );
				} else {
					if ( isset( $wp_filter['admin_notices'] ) ) {
						unset( $wp_filter['admin_notices'] );
					}
				}

				if ( isset( $wp_filter['all_admin_notices'] ) ) {
					unset( $wp_filter['all_admin_notices'] );
				}
			}

		}


	} // end SimpleSocialButtonsPR_Admin

endif;
?>
