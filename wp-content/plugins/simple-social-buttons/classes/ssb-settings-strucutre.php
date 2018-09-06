<?php


if ( ! class_exists( 'Ssb_Settings_Structure' ) ) :
  class Ssb_Settings_Structure {

    /**
    * settings sections array
    *
    * @var array
    */
    protected $settings_sections = array();

    /**
    * Settings fields array
    *
    * @var array
    */
    protected $settings_fields = array();

    public function __construct() {
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    }

    /**
    * Enqueue scripts and styles
    */
    function admin_enqueue_scripts() {
      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_script( 'wp-color-picker' );
      wp_enqueue_script( 'jquery' );
    }

    /**
    * Set settings sections
    *
    * @param array   $sections setting sections array
    */
    function set_sections( $sections ) {
      $this->settings_sections = $sections;

      return $this;
    }

    /**
    * Add a single section
    *
    * @param array   $section
    */
    function add_section( $section ) {
      $this->settings_sections[] = $section;

      return $this;
    }

    /**
    * Set settings fields
    *
    * @param array   $fields settings fields array
    */
    function set_fields( $fields ) {
      $this->settings_fields = $fields;

      return $this;
    }

    function add_field( $section, $field ) {
      $defaults = array(
        'name'  => '',
        'label' => '',
        'desc'  => '',
        'type'  => 'text'
      );

      $arg = wp_parse_args( $field, $defaults );
      $this->settings_fields[$section][] = $arg;

      return $this;
    }

    /**
    * Initialize and registers the settings sections and fileds to WordPress
    *
    * Usually this should be called at `admin_init` hook.
    *
    * This function gets the initiated settings sections and fields. Then
    * registers them to WordPress and ready for use.
    */
    function admin_init() {
      //register settings sections
      foreach ( $this->settings_sections as $section ) {
        if ( false == get_option( $section['id'] ) ) {
          add_option( $section['id'] );
        }

        if ( isset($section['desc']) && !empty($section['desc']) ) {
          $section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
          $callback = create_function('', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";');
        } else if ( isset( $section['callback'] ) ) {
          $callback = $section['callback'];
        } else {
          $callback = null;
        }
        if ( 'ssb_advanced' == $section['id'] ) {
          add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
        } else {
          add_settings_section( $section['id'], $section['title'], $callback, 'ssb_networks' );
        }
      }
      //register settings fields
      foreach ( $this->settings_fields as $section => $field ) {
        foreach ( $field as $index => $option ) {

              $name = $option['name'];
              $type = isset( $option['type'] ) ? $option['type'] : 'text';
              $label = isset( $option['label'] ) ? $option['label'] : '';
              $callback = isset( $option['callback'] ) ? $option['callback'] : array( $this, 'callback_' . $type );

              $args = array(
                'id'                => $name,
                'class'             => isset( $option['class'] ) ? $option['class'] : $name,
                'label_for'         => "{$section}[{$name}]",
                'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
                'name'              => $label,
                'section'           => $section,
                'size'              => isset( $option['size'] ) ? $option['size'] : null,
                'options'           => isset( $option['options'] ) ? $option['options'] : '',
                'std'               => isset( $option['default'] ) ? $option['default'] : '',
                'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                'type'              => $type,
                'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
                'min'               => isset( $option['min'] ) ? $option['min'] : '',
                'max'               => isset( $option['max'] ) ? $option['max'] : '',
                'step'              => isset( $option['step'] ) ? $option['step'] : '',
                'link'              => isset( $option['link'] ) ? $option['link'] : '',
              );

              if ( 'ssb_advanced' == $section ) {
                add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
              } else {
                add_settings_field( "{$section}[{$name}]", $label, $callback, 'ssb_networks', $section, $args );
              }

        }
      }

      // creates our settings in the options table
      foreach ( $this->settings_sections as $section ) {
        if ( 'ssb_advanced' == $section['id'] ) {
          register_setting( $section['id'], $section['id'], array( $this, 'sanitize_options' ) );
        } else {
          register_setting( 'ssb_networks', $section['id'], array( $this, 'sanitize_options' ) );
        }

      }
    }

    /**
    * Get field description for display
    *
    * @param array   $args settings field args
    */
    public function get_field_description( $args ) {
      if ( ! empty( $args['desc'] ) ) {
        $desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
      } else {
        $desc = '';
      }

      return $desc;
    }

    function callback_icon_style( $args ) {

      $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
      ?>

      <!-- <div class="postbox">
        <div class="inside"> -->
          <?php foreach ($args['options'] as $key => $label ): ?>
            <div class="simplesocialbuttons-style-outer">
              <div class="simplesocialbuttons-style">
                <label>
                  <!-- <input type="radio" name="simplesocialbuttons" value="test" <?php echo checked( $value, $key, false ) ?>> -->
                  <?php
                  printf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
                   ?>
                  <span class="radio"><span class="shadow"></span></span>
                </label>
                <div class="simplesocialbuttons-nav <?php echo $key ?>">
                  <?php if ( 'simple-icons' == $key ) :?>
                  <ul>
                    <li><a href="#" class="simplesocial-fb-share"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="_1pbq" color="#ffffff"><path fill="#ffffff" fill-rule="evenodd" class="icon" d="M8 14H3.667C2.733 13.9 2 13.167 2 12.233V3.667A1.65 1.65 0 0 1 3.667 2h8.666A1.65 1.65 0 0 1 14 3.667v8.566c0 .934-.733 1.667-1.667 1.767H10v-3.967h1.3l.7-2.066h-2V6.933c0-.466.167-.9.867-.9H12v-1.8c.033 0-.933-.266-1.533-.266-1.267 0-2.434.7-2.467 2.133v1.867H6v2.066h2V14z"></path></svg></span><span class="simplesocial-hidden-text">Share</span></a></li>
                    <li><a href="#" class="simplesocial-twt-share"><span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 72 72"><path fill="none" d="M0 0h72v72H0z"></path><path class="icon" fill="#fff" d="M68.812 15.14c-2.348 1.04-4.87 1.744-7.52 2.06 2.704-1.62 4.78-4.186 5.757-7.243-2.53 1.5-5.33 2.592-8.314 3.176C56.35 10.59 52.948 9 49.182 9c-7.23 0-13.092 5.86-13.092 13.093 0 1.026.118 2.02.338 2.98C25.543 24.527 15.9 19.318 9.44 11.396c-1.125 1.936-1.77 4.184-1.77 6.58 0 4.543 2.312 8.552 5.824 10.9-2.146-.07-4.165-.658-5.93-1.64-.002.056-.002.11-.002.163 0 6.345 4.513 11.638 10.504 12.84-1.1.298-2.256.457-3.45.457-.845 0-1.666-.078-2.464-.23 1.667 5.2 6.5 8.985 12.23 9.09-4.482 3.51-10.13 5.605-16.26 5.605-1.055 0-2.096-.06-3.122-.184 5.794 3.717 12.676 5.882 20.067 5.882 24.083 0 37.25-19.95 37.25-37.25 0-.565-.013-1.133-.038-1.693 2.558-1.847 4.778-4.15 6.532-6.774z"></path></svg></span><span class="simplesocial-hidden-text">Tweet</span></a></li>
                    <li><a href="#" class="simplesocial-gplus-share"><span class="icon"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet" width="30px" height="18px" viewBox="-10 -6 60 36" class="ozWidgetRioButtonSvg_ ozWidgetRioButtonPlusOne_"><path d="M30 7h-3v4h-4v3h4v4h3v-4h4v-3h-4V7z"></path><path d="M11 9.9v4h5.4C16 16.3 14 18 11 18c-3.3 0-5.9-2.8-5.9-6S7.7 6 11 6c1.5 0 2.8.5 3.8 1.5l2.9-2.9C15.9 3 13.7 2 11 2 5.5 2 1 6.5 1 12s4.5 10 10 10c5.8 0 9.6-4.1 9.6-9.8 0-.7-.1-1.5-.2-2.2H11z"></path></svg></span></a></li>
                    <li><span style="line-height: 20px; vertical-align: top; font-weight: bold;display: inline-block;">Official Buttons</span></li>
                  </ul>
                  <?php else: ?>
                  <ul>
                    <li><a href="#" class="simplesocial-fb-share"><span class="simplesocial-hidden-text">Facebook</span></a></li>
                    <li><a href="#" class="simplesocial-twt-share"><span class="simplesocial-hidden-text">Twitter</span></a></li>
                    <li><a href="#" class="simplesocial-gplus-share"><span class="simplesocial-hidden-text">Google</span></a></li>
                  </ul>
                <?php endif; ?>
                </div> <!--  .simplesocialbuttons-nav -->
              </div> <!--  .simplesocialbuttons-style -->
            </div> <!--  .simplesocialbuttons-style-outer -->
          <?php endforeach; ?>
        <!-- </div>
      </div> -->

      <?php

    }

    function callback_position( $args ) {

      $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
      ?>
          <div class="simplesocial-postion-outer-wrapper">
            <?php printf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] ) ?>
            <?php foreach ( $args['options'] as $key => $label ): ?>
              <?php  $checked = isset( $value[$key] ) ? $value[$key] : '0'; ?>
              <div class="simplesocial-postion-outer">
                <label class="simplesocial-postion-box simplesocial-<?php echo $key ?>">
                  <span class="simplesocial-fb-sharehd-line"><?php  echo $label ?></span>
                  <span class="simplesocial-blue-box">
                    <span class="simplesocial-highlight">
                    </span>
                  </span>
                  <?php printf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) ); ?>
                  <span class="checkbox"><span class="shadow"></span></span>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
      <?php
    }

    function callback_ssb_select( $args ) {
      echo isset( $args['desc'] ) ?  $args['desc'] : '';
      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      ?>
      <div class="simplesocial-form-section">
        <h5><?php echo $args['name'] ?></h5>
        <?php
        printf( '<select class="%1$s ssb_select" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
          printf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
        }
        printf( '</select>' );
        ?>
      </div>
      <?php
    }

    function callback_ssb_checkbox( $args ) {
      echo isset( $args['desc'] ) ? $args['desc'] : '';
      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      ?>
      <div class="simplesocial-form-section">
        <h5><?php echo $args['name'] ?></h5>
        <?php
        printf( '<input type="hidden" name="%1$s[%2$s]" value="0" />', $args['section'], $args['id'] );
        printf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s />', $args['section'], $args['id'], checked( $value, '1', false ) );
        printf( '<label class="simplesocial-switch" for="%1$s[%2$s]"></label>', $args['section'], $args['id'] );
        ?>

      </div>
      <?php
    }

    function callback_ssb_color( $args ) {

      echo isset( $args['desc'] ) ? $args['desc'] : '';
      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      ?>
      <div class="simplesocial-form-section">
        <h5><?php echo $args['name'] ?></h5>
        <div class="selection-color">
          <?php
           printf( '<input type="text" class="%1$s-text ssb_settings_color_picker" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
          ?>
        </div>
      </div>
      <?php
    }

    function callback_ssb_post_types( $args ) {

      $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
      $html  = '<fieldset>';
      ?>
      <h4>Post type Settings</h4>
      <div class="simplesocial-inline-form-section">
        <?php printf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] ); ?>
        <?php foreach ( $args['options'] as $key => $label ):

          $checked = isset( $value[$key] ) ? $value[$key] : '0';
           printf( '<label for="%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
           printf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
           printf('<span class="checkbox"><span class="shadow"></span></span>');
           printf( '%1$s</label>',  $label );

        endforeach; ?>

      </div> <!--  .form-section -->

      <?php

    }

    function callback_ssb_text( $args ) {

      $value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      $type        = isset( $args['type'] ) ? $args['type'] : 'text';
      $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
      echo $args['desc']
      ?>
      <div class="<?php printf( 'simplesocial-form-section container-%1$s[%2$s]', $args['section'], $args['id'] ) ?>">
        <?php  ?>
        <h5><?php echo $args['name'] ?></h5>
        <div class="simplesocial-input">
          <?php printf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder ) ?>
          <span class="highlight"></span>
          <span class="bar"></span>

        </div>
      </div>
      <?php
    }

    function callback_ssb_textarea( $args ) {

      $value       = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="'.$args['placeholder'].'"';

      ?>
      <div class="simplesocial-form-section">
        <h5><?php echo $args['name'] ?></h5>
        <div class="simplesocial-input">
          <?php printf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value ) ?>
          <span class="highlight"></span>
          <span class="bar"></span>
        </div>
      </div>
      <?php

    }


    function callback_ssb_icon_selection( $args ) {

      $save_value = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $settings   = array_flip( array_merge( array(0), explode( ',', $save_value ) ) );

      ?>

         <div class="inside">
           <p>Drag & Drop to activate and order your share buttons:</p>
           <div class="ssb_settings_box">
             <h2>Active</h2>
             <ul id="ssb_active_icons" class="items" style="min-height:35px">
               <?php
               $ssb_icons_order = array();
               $arrKnownButtons = array( 'googleplus', 'twitter', 'pinterest', 'fbshare', 'linkedin', 'reddit', 'whatsapp', 'viber', 'fblike', 'messenger' , 'email', 'print', 'tumblr' );
               foreach ($arrKnownButtons as $button_name) {
                   $ssb_icons_order[$button_name] = isset( $settings[$button_name] ) ? $settings[$button_name] : '' ;
               }

               asort( $ssb_icons_order );
                ?>
               <?php foreach ($ssb_icons_order as $key => $value): ?>
                 <?php if ( $value != 0): ?>
                   <li data-id="<?php echo $key ?>" class="list"><img src="<?php echo plugins_url( 'assets/images/'.$key.'.svg', plugin_dir_path( __FILE__ )  ) ?>" /></li>
                 <?php endif; ?>
               <?php endforeach; ?>
             </ul>
           </div>
           <div class="ssb_settings_box">
           <h2>InActive</h2>

           <ul id="ssb_inactive_icons" class="items" style="min-height:35px">
             <?php foreach ( $ssb_icons_order as $key => $value): ?>
               <?php if ( $value == 0): ?>
                 <li data-id="<?php echo $key ?>" class="list" ><img src="<?php echo plugins_url( 'assets/images/'.$key.'.svg', plugin_dir_path( __FILE__ ) ) ?>" /></li>
               <?php endif; ?>
             <?php endforeach; ?>
           </ul>
         </div>

          <?php printf( '<input type="hidden" id="%1$s[%2$s]" name="%1$s[%2$s]" value="%3$s" />', $args['section'], $args['id'], $save_value ) ?>

         </div>

      <?php
    }

    function callback_ssb_go_pro( $args ) {
      ?>
        <div class="ssb_goto_pro_section">
          <h4><?php echo $args['name'] ?></h4>
          <p><?php echo $args['desc'] ?></p>
          <a href="<?php echo $args['link'] ?>" class="ssb_goto_pro_button">Click here to Upgrade</a>
        </div>
      <?php
    }

    function settings_header() {
      ?>
      <div class="ssb-top-bar">
        <a href="https://wpbrigade.com/"><img src="<?php echo plugins_url( 'assets/images/ssb_icon.png', plugin_dir_path( __FILE__ ) ) ?>" alt="Simple Social Buttons"></a>
        <div class="ssb-top-bar-content">
          <h2>Simple Social Buttons -->> <?php _e('makes Social Sharing easy for everyone'); ?></h2>
          <p><?php _e('<strong>Simple Social Buttons</strong> by <strong><a href="https://wpbrigade.com/?utm_source=simple-social-buttons-lite&utm_medium=link-header&utm_campaign=pro-upgrade">WPBrigade</a></strong> adds an advanced set of social media sharing buttons to your WordPress sites, such as: <strong>Google +1</strong>, <strong>Facebook</strong>, <strong>WhatsApp</strong>, <strong>Viber</strong>, <strong>Twitter</strong>, <strong>Reddit</strong>, <strong>LinkedIn</strong> and <strong>Pinterest</strong>. This makes it the most flexible social sharing plugin ever for Everyone.', 'simplesocialbuttons'); ?></p>
        </div>
      </div>
      <?php
    }

  function settings_sidebar() {
    ?>
    <div class="postbox-container ssb_right_sidebar">
      <div id="poststuff">
        <div class="postbox ssb_social_links_wrapper">
          <div class="sidebar postbox">
            <h2><?php _e( 'Spread the Word', 'simple-social-buttons' ) ?></h2>
            <ul class="ssb_social_links">
              <li>
                <a href="http://twitter.com/share?text=Check out this (FREE) Amazing Social Share Plugin for WordPress&amp;url=https://wordpress.org/plugins/simple-social-buttons/" data-count="none" class="button twitter" target="_blank" title="Post to Twitter Now"><?php _e( 'Share on Twitter', 'simple-social-buttons' ) ?><span class="dashicons dashicons-twitter"></span></a>
              </li>
              <li>
                <a href="https://www.facebook.com/sharer/sharer.php?u=https://wordpress.org/plugins/simple-social-buttons/" class="button facebook" target="_blank" title="Check out this (FREE) Amazing Social Share Plugin for WordPress"><?php _e( 'Share on Facebook', 'simple-social-buttons' ) ?><span class="dashicons dashicons-facebook"></span>
                </a>
              </li>
              <li>
                <a href="https://wordpress.org/plugins/simple-social-buttons/?filter=5" class="button wordpress" target="_blank" title="Rate on WordPress.org"><?php _e( 'Rate on WordPress.org', 'simple-social-buttons' ) ?><span class="dashicons dashicons-wordpress"></span>
                </a>
              </li>
            </ul>
          </div>
        </div>

        <div class="postbox ssb_social_links_wrapper">
          <div class="sidebar postbox">
            <h2><?php _e( 'Subscribe Newsletter', 'simple-social-buttons' ) ?></h2>
            <ul>
              <li>
                <label for=""><?php _e( 'Email', 'simple-social-buttons' ) ?></label>
                <input type="email" name="subscriber_mail" value="<?php echo get_option( 'admin_email' ) ?>" id="ssb_subscribe_mail">
                <p class="ssb_subscribe_warning"></p>
              </li>
              <li>
                <label for=""><?php _e( 'Name', 'simple-social-buttons' ) ?></label>
                <input type="text" name="subscriber_name" id="ssb_subscribe_name" value="<?php echo wp_get_current_user()->display_name ?>">
              </li>
              <li>
                <input type="submit" value="Subscribe Now" class="button button-primary button-big" id="ssb_subscribe_btn">
                <img src="<?php echo admin_url( 'images/spinner.gif' ) ?>" class="ssb_subscribe_loader" style="display:none">
              </li>
              <li>
                <p class="ssb_return_message"></p>
              </li>
            </ul>
          </div>
        </div>

        <div class="postbox ssb_social_links_wrapper">
          <div class="sidebar postbox">
            <h2><?php _e( 'Recommended Plugins', 'simple-social-buttons' ) ?></h2>
            <ul class="plugins_lists">
              <li>
                <a href="https://wpbrigade.com/wordpress/plugins/loginpress-pro/?utm_source=ssb-lite&amp;utm_medium=sidebar&amp;utm_campaign=pro-upgrade" target="_blank" title="Post to Twitter Now">LoginPress - Login Customizer</a>
              </li>
              <li>
                <a href="https://analytify.io/ref/73/?utm_source=ssb-lite&amp;utm_medium=sidebar&amp;utm_campaign=pro-upgrade" target="_blank" title="Share with your facebook friends about this awesome plugin.">Google Analytics by Analytify
                </a>
              </li>
              <li>
                <a href="https://wpbrigade.com/wordpress/plugins/related-posts/?utm_source=ssb-lite&amp;utm_medium=sidebar&amp;utm_campaign=pro-upgrade" target="_blank" title="Releated Posts Thumbnails">Releated Posts Thumbnails</a>
              </li>
              <li>
                <a href="http://wpbrigade.com/recommend/maintenance-mode" target="_blank" title="Under Construction &amp; Maintenance mode">Under Construction &amp; Maintenance mode
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?php
  }

    /**
    * Displays a text field for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_text( $args ) {

      $value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      $type        = isset( $args['type'] ) ? $args['type'] : 'text';
      $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';

      $html        = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
      $html       .= $this->get_field_description( $args );

      echo $html;
    }

    /**
    * Displays a url field for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_url( $args ) {
      $this->callback_text( $args );
    }

    /**
    * Displays a number field for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_number( $args ) {
      $value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      $type        = isset( $args['type'] ) ? $args['type'] : 'number';
      $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
      $min         = empty( $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
      $max         = empty( $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
      $step        = empty( $args['max'] ) ? '' : ' step="' . $args['step'] . '"';

      $html        = sprintf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
      $html       .= $this->get_field_description( $args );

      echo $html;
    }

    /**
    * Displays a checkbox for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_checkbox( $args ) {

      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

      $html  = '<fieldset>';
      $html  .= sprintf( '<label for="%1$s[%2$s]">', $args['section'], $args['id'] );
      $html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
      $html  .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ) );
      $html  .= sprintf( '%1$s</label>', $args['desc'] );
      $html  .= '</fieldset>';

      echo $html;
    }

    /**
    * Displays a multicheckbox for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_multicheck( $args ) {

      $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
      $html  = '<fieldset>';
      $html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
      foreach ( $args['options'] as $key => $label ) {
        $checked  = isset( $value[$key] ) ? $value[$key] : '0';
        $html    .= sprintf( '<label for="%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
        $html    .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
        $html    .= sprintf( '%1$s</label><br>',  $label );
      }

      $html .= $this->get_field_description( $args );
      $html .= '</fieldset>';

      echo $html;
    }

    /**
    * Displays a radio button for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_radio( $args ) {

      $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
      $html  = '<fieldset>';

      foreach ( $args['options'] as $key => $label ) {
        $html .= sprintf( '<label for="%1$s[%2$s][%3$s]">',  $args['section'], $args['id'], $key );
        $html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
        $html .= sprintf( '%1$s</label><br>', $label );
      }

      $html .= $this->get_field_description( $args );
      $html .= '</fieldset>';

      echo $html;
    }

    /**
    * Displays a selectbox for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_select( $args ) {

      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      $html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

      foreach ( $args['options'] as $key => $label ) {
        $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
      }

      $html .= sprintf( '</select>' );
      $html .= $this->get_field_description( $args );

      echo $html;
    }

    /**
    * Displays a textarea for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_textarea( $args ) {

      $value       = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size        = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="'.$args['placeholder'].'"';

      $html        = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value );
      $html        .= $this->get_field_description( $args );

      echo $html;
    }

    /**
    * Displays the html for a settings field
    *
    * @param array   $args settings field args
    * @return string
    */
    function callback_html( $args ) {
      echo $this->get_field_description( $args );
    }

    /**
    * Displays a rich text textarea for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_wysiwyg( $args ) {

      $value = $this->get_option( $args['id'], $args['section'], $args['std'] );
      $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : '500px';

      echo '<div style="max-width: ' . $size . ';">';

      $editor_settings = array(
        'teeny'         => true,
        'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
        'textarea_rows' => 10
      );

      if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
        $editor_settings = array_merge( $editor_settings, $args['options'] );
      }

      wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );

      echo '</div>';

      echo $this->get_field_description( $args );
    }

    /**
    * Displays a file upload field for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_file( $args ) {

      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';
      $id    = $args['section']  . '[' . $args['id'] . ']';
      $label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File' );

      $html  = sprintf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
      $html  .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
      $html  .= $this->get_field_description( $args );

      echo $html;
    }

    /**
    * Displays a password field for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_password( $args ) {

      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

      $html  = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
      $html  .= $this->get_field_description( $args );

      echo $html;
    }

    /**
    * Displays a color picker field for a settings field
    *
    * @param array   $args settings field args
    */
    function callback_color( $args ) {

      $value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
      $size  = isset( $args['size'] ) && !is_null( $args['size'] ) ? $args['size'] : 'regular';

      $html  = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
      $html  .= $this->get_field_description( $args );

      echo $html;
    }


    /**
    * Displays a select box for creating the pages select box
    *
    * @param array   $args settings field args
    */
    function callback_pages( $args ) {

      $dropdown_args = array(
        'selected' => esc_attr($this->get_option($args['id'], $args['section'], $args['std'] ) ),
        'name'     => $args['section'] . '[' . $args['id'] . ']',
        'id'       => $args['section'] . '[' . $args['id'] . ']',
        'echo'     => 0
      );
      $html = wp_dropdown_pages( $dropdown_args );
      echo $html;
    }

    /**
    * Sanitize callback for Settings API
    *
    * @return mixed
    */
    function sanitize_options( $options ) {

      if ( !$options ) {
        return $options;
      }

      foreach( $options as $option_slug => $option_value ) {
        $sanitize_callback = $this->get_sanitize_callback( $option_slug );

        // If callback is set, call it
        if ( $sanitize_callback ) {
          $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
          continue;
        }
      }

      return $options;
    }

    /**
    * Get sanitization callback for given option slug
    *
    * @param string $slug option slug
    *
    * @return mixed string or bool false
    */
    function get_sanitize_callback( $slug = '' ) {
      if ( empty( $slug ) ) {
        return false;
      }

      // Iterate over registered fields and see if we can find proper callback
      foreach( $this->settings_fields as $section => $options ) {
        foreach ( $options as $option ) {
          if ( $option['name'] != $slug ) {
            continue;
          }

          // Return the callback name
          return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
        }
      }

      return false;
    }

    /**
    * Get the value of a settings field
    *
    * @param string  $option  settings field name
    * @param string  $section the section name this field belongs to
    * @param string  $default default text if it's not found
    * @return string
    */
    function get_option( $option, $section, $default = '' ) {

      $options = get_option( $section );

      if ( isset( $options[$option] ) ) {
        return $options[$option];
      }

      return $default;
    }

    /**
    * Show navigations as tab
    *
    * Shows all the settings section labels as tab
    */
    function show_navigation() {
      $html = '<h2 class="nav-tab-wrapper">';

      $tabs = array(
        array(
          'id' => 'ssb_settings',
          'title' => '<span class="dashicons dashicons-admin-generic"></span>Settings',
        ),
        array(
          'id' => 'ssb_advanced',
          'title' => '<span class="dashicons dashicons-editor-code"></span>Advanced',
        ),
      );
      if ( ! class_exists( 'Simple_Social_Buttons_Pro' ) ) {
        $tabs[] =   array(
            'id' => 'ssb_go_pro',
            'title' => '<span class="dashicons dashicons-star-filled"></span>Upgrade To Pro For More Features',
            'link' => 'https://wpbrigade.com/wordpress/plugins/simple-social-buttons-pro/?utm_source=simple-social-buttons-lite&utm_medium=tab&utm_campaign=pro-upgrade'
          );
      }
      foreach ( $tabs as $tab ) {
        if ( isset( $tab['link'] ) ) {
          $html .= sprintf( '<a href="%3$s" class="nav-tab" id="%1$s-tab" target="_blank" >%2$s</a>', $tab['id'], $tab['title'], $tab['link'] );
        } else {
          $html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
        }

      }

      $html .= '</h2>';

      echo $html;
    }

    /**
    * Show the section settings forms
    *
    * This function displays every sections in a different form
    */

    function show_forms() {
			echo '<div class="ssb_settings_container ssb_settings-tab group" id="ssb_settings-tab-content">';
        echo '<div class="metabox-holder">';
          echo '<div id="poststuff">';
            echo '<form method="post" action="options.php">';
              $this->do_settings_sections( 'ssb_networks' );
              settings_fields( 'ssb_networks' );
              submit_button();
            echo '</form>';
          echo "</div>";
        echo "</div>";
  		echo '</div>';

      echo '<div class="ssb_settings_container ssb_advanced-tab group" id="ssb_advanced-tab-content">';
        echo '<div class="metabox-holder">';
          echo '<div id="poststuff">';
            echo '<form method="post" action="options.php">';
              $this->do_settings_sections( 'ssb_advanced' );
              settings_fields( 'ssb_advanced' );
              submit_button();
            echo '</form>';
          echo "</div>";
        echo "</div>";
      echo '</div>';


      $this->script();
    }

    /**
     * Prints out all settings sections added to a particular settings page
     *
     * Part of the Settings API. Use this in a settings page callback function
     * to output all the sections and fields that were added to that $page with
     * add_settings_section() and add_settings_field()
     *
     * @global $wp_settings_sections Storage array of all settings sections added to admin pages
     * @global $wp_settings_fields Storage array of settings fields and info about their pages/sections
     * @since 2.7.0
     *
     * @param string $page The slug name of the page whose settings sections you want to output
     */
     function  do_settings_sections( $page ) {
       global $wp_settings_sections, $wp_settings_fields;

       if ( ! isset( $wp_settings_sections[$page] ) )
       return;

       foreach ( (array) $wp_settings_sections[$page] as $section ) {

         if ( $section['callback'] )
         call_user_func( $section['callback'], $section );

         if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
         continue;

         $extra_class = in_array( $section['id'], array( 'ssb_sidebar','ssb_inline','ssb_media','ssb_popup','ssb_flyin' ) ) ?  'simpleshare-acordions'  : '' ;
         ?>
         <div class="postbox <?php echo $extra_class ?>" id='<?php echo  $section['id'] ?>' >
           <div class="inside">
             <h3 class="simpleshare-active"><?php echo $section['title'] ?></h3>
             <div class="postbox-content">
             <?php
             $this->do_settings_fields( $page, $section['id'] );
             ?>
           </div>
           </div>
         </div>
         <?php
       }
     }


    /**
     * Print out the settings fields for a particular settings section
     *
     * Part of the Settings API. Use this in a settings page to output
     * a specific section. Should normally be called by do_settings_sections()
     * rather than directly.
     *
     * @global $wp_settings_fields Storage array of settings fields and their pages/sections
     *
     * @since 2.7.0
     *
     * @param string $page Slug title of the admin page who's settings fields you want to show.
     * @param string $section Slug title of the settings section who's fields you want to show.
     */
    function  do_settings_fields($page, $section) {
    	global $wp_settings_fields;

    	if ( ! isset( $wp_settings_fields[$page][$section] ) )
    		return;

    	foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
    		$class = '';

    		if ( ! empty( $field['args']['class'] ) ) {
    			$class = ' class="' . esc_attr( $field['args']['class'] ) . '"';
    		}

    		// echo "<tr{$class}>";
        //
    		// if ( ! empty( $field['args']['label_for'] ) ) {
    		// 	echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
    		// } else {
    		// 	echo '<th scope="row">' . $field['title'] . '</th>';
    		// }

    		// echo '<td>';

    		call_user_func($field['callback'], $field['args']);
    		// echo '</td>';
    		// echo '</tr>';
    	}
    }


    /**
    * Tabbable JavaScript codes & Initiate Color Picker
    *
    * This code uses localstorage for displaying active tabs
    */
    function script() {
      ?>
      <script>
      jQuery(document).ready(function($) {
        //Initiate Color Picker
        $('.wp-color-picker-field').wpColorPicker();

        // Switches option sections
        $('.group').hide();
        var activetab = '';
        if (typeof(localStorage) != 'undefined' ) {
          activetab = localStorage.getItem("activetab");
        }

        //if url has section id as hash then set it as active or override the current local storage value
        if(window.location.hash){
          activetab = window.location.hash;
          if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", activetab);
          }
        }

        $(activetab+'-tab-content').fadeIn();
        if (activetab != '' && $(activetab).length ) {
          $(activetab).fadeIn();
        } else {
          $('.group:first').fadeIn();
        }
        $('.group .collapsed').each(function(){
          $(this).find('input:checked').parent().parent().parent().nextAll().each(
            function(){
              if ($(this).hasClass('last')) {
                $(this).removeClass('hidden');
                return false;
              }
              $(this).filter('.hidden').removeClass('hidden');
            });
          });

          if (activetab != '' && $(activetab + '-tab').length ) {
            $(activetab + '-tab').addClass('nav-tab-active');
          }
          else {
            $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
          }
          $('.nav-tab-wrapper a').click(function(evt) {

            if ('ssb_go_pro-tab' == $(this).attr('id')) { return; }
            $('.nav-tab-wrapper a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active').blur();
            var clicked_group = $(this).attr('href') + '-tab-content';
            if (typeof(localStorage) != 'undefined' ) {
              if ( $(this).attr('href').indexOf('#') > -1 ) {
                localStorage.setItem("activetab", $(this).attr('href'));
              }
            }
            $('.group').hide();
            $(clicked_group).fadeIn();
            evt.preventDefault();
          });

          $('.wpsa-browse').on('click', function (event) {
            event.preventDefault();

            var self = $(this);

            // Create the media frame.
            var file_frame = wp.media.frames.file_frame = wp.media({
              title: self.data('uploader_title'),
              button: {
                text: self.data('uploader_button_text'),
              },
              multiple: false
            });

            file_frame.on('select', function () {
              attachment = file_frame.state().get('selection').first().toJSON();
              self.prev('.wpsa-url').val(attachment.url).change();
            });

            // Finally, open the modal
            file_frame.open();
          });

          $('#ssb_subscribe_btn').on('click', function(event) {
            event.preventDefault();
            var subscriber_mail = $('#ssb_subscribe_mail').val();
            var name = $('#ssb_subscribe_name').val();
            if (!subscriber_mail) {
              $('.ssb_subscribe_warning').html('Please Enter Email');
              return;
            }
            $.ajax({
              url: 'https://wpbrigade.com/wp-json/wpbrigade/v1/subsribe-to-mailchimp',
              type: 'POST',
              data: {
                subscriber_mail : subscriber_mail,
                name : name,
                plugin_name : 'ssb'
              },
              beforeSend : function() {
                $('.ssb_subscribe_loader').show();
                $('#ssb_subscribe_btn').attr('disabled', 'disabled');
              }
            })
            .done(function(res) {
              $('.ssb_return_message').html(res);
              $('.ssb_subscribe_loader').hide();
            });
          });
          $('.simplesocialbuttons-style').on('click',function(){
            var el = $(this);
            $(this).addClass('social-active').parent().siblings().find('.simplesocialbuttons-style').removeClass('social-active');
            $(this).find('input[type="radio"]').prop('checked', true);
          });
          $('.simplesocial-postion-box').on('click',function(){
            var el = $(this);
            var target = $(this).children('input[type="checkbox"]').val();
            if($(this).children('input[type="checkbox"]').is(':checked')){
              $(this).addClass('social-active');
              $('#ssb_'+target).fadeIn();
            }else{
              $(this).removeClass('social-active');
              $('#ssb_'+target).fadeOut();
            }
            $(this).find('.shadow').addClass('animated');
            setTimeout(function(){ el.find('.shadow').removeClass('animated'); }, 400);
          });
          $('.simplesocial-postion-box').each(function(){
            var el = $(this);
            var target = $(this).children('input[type="checkbox"]').val();
            if($(this).children('input[type="checkbox"]').is(':checked')){
              $(this).addClass('social-active');
              $('#ssb_'+target).fadeIn();
            }else{
              $(this).removeClass('social-active');
              $('#ssb_'+target).fadeOut();
            }
          });
          $('.simplesocial-inline-form-section label').on('click',function(){
            var el = $(this);
            $(this).find('.shadow').addClass('animated');
            setTimeout(function(){ el.find('.shadow').removeClass('animated'); }, 400);
          });
          $('.simpleshare-acordions h3').on('click',function(){
            $(this).toggleClass('simpleshare-active');
            $(this).next('.postbox-content').slideToggle();
          });
          $('.ssb_select').each(function () {

                // Cache the number of options
                var $this = $(this),
                    numberOfOptions = $(this).children('option').length;

                // Hides the select element
                $this.addClass('s-hidden');

                // Wrap the select element in a div
                $this.wrap('<div class="select"></div>');

                // Insert a styled div to sit over the top of the hidden select element
                $this.after('<div class="styledSelect"></div>');

                // Cache the styled div
                var $styledSelect = $this.next('div.styledSelect');
                var getHTML = $this.children('option[value="'+$this.val()+'"]').text();
                // Show the first select option in the styled div
                $styledSelect.text(getHTML);

                // Insert an unordered list after the styled div and also cache the list
                var $list = $('<ul />', {
                    'class': 'options'
                }).insertAfter($styledSelect);

                // Insert a list item into the unordered list for each select option
                for (var i = 0; i < numberOfOptions; i++) {
                    $('<li />', {
                        text: $this.children('option').eq(i).text(),
                        rel: $this.children('option').eq(i).val()
                    }).appendTo($list);
                }

                // Cache the list items
                var $listItems = $list.children('li');

                // Show the unordered list when the styled div is clicked (also hides it if the div is clicked again)
                $styledSelect.click(function (e) {

                    // $(this).addClass('active').next('ul.options').slideDown();
                    if($(this).hasClass('active')){
                      $(this).removeClass('active').next('ul.options').slideUp();
                    }else{
                      $('div.styledSelect.active').each(function () {
                        $(this).removeClass('active').next('ul.options').slideUp();
                      });
                      $(this).addClass('active').next('ul.options').slideDown();
                    }
                    e.stopPropagation();
                });

                // Hides the unordered list when a list item is clicked and updates the styled div to show the selected list item
                // Updates the select element to have the value of the equivalent option
                $listItems.click(function (e) {
                    e.stopPropagation();
                    $styledSelect.text($(this).text()).removeClass('active');
                var value = $(this).attr('rel').toString();
                    $($this).val(value);
                    $($this).trigger('change');
                    $list.slideUp();
                    /* alert($this.val()); Uncomment this for demonstration! */
                });

                // Hides the unordered list when clicking outside of it
                $(document).click(function () {
                    $styledSelect.removeClass('active');
                    $list.slideUp();
                });

            });
        });
        </script>
        <?php
        $this->_style_fix();
      }

      function _style_fix() {
        global $wp_version;

        if (version_compare($wp_version, '3.8', '<=')):
          ?>
          <style type="text/css">
          /** WordPress 3.8 Fix **/
          .form-table th { padding: 20px 10px; }
          #wpbody-content .metabox-holder { padding-top: 5px; }
          </style>
          <?php
        endif;
      }

    }

  endif;
