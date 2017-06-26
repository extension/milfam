<?php

function remove_footer_admin () {
  echo 'Thank you for creating with <a href="https://wordpress.org/">WordPress</a> | <a href="https://extension.org/terms-of-use/" target="_blank">Terms of Use</a>';
}
add_filter('admin_footer_text', 'remove_footer_admin');

add_filter( 'body_class','my_body_classes' );
function my_body_classes( $classes ) {
    $classes[] = 'has-sidebar';
    return $classes;
}


function show_learn_widget( $atts ) {
  $a = shortcode_atts( array(
    'key' => '',
    'tags' => '',
    'limit' => '5',
    'match_all_tags' => false,
  ), $atts );
  $a['operator'] = ($a['match_all_tags'] == "true" ? "and" : '');
  ob_start();
  include(locate_template('learn-widget.php'));
  return ob_get_clean();
}

add_shortcode( 'learn_widget', 'show_learn_widget' );

add_action( 'widgets_init', 'register_custom_sidebars' );
function  register_custom_sidebars() {
	// $title_tag = pinboard_get_option( 'widget_title_tag' );

	register_sidebar(
		array(
      'name' => 'CA Landing Page Sidebar ',
      'id' => 'sidebar-ca',
			'description' => 'Displays in in the sidebar on the CA landing page.',
      'before_widget' => '<div id="%1$s" class="widget %2$s">',
      'after_widget' => '</div>',
      'before_title' => '<h2 class="widget-title">',
      'after_title' => '</h2>'
		)
	);
}

add_action( 'init', 'categorize_page_settings' );
function categorize_page_settings() {
  // Add category metabox to page
  register_taxonomy_for_object_type('category', 'page');
}

add_action( 'init', 'my_add_excerpts_to_pages' );
function my_add_excerpts_to_pages() {
  add_post_type_support( 'page', 'excerpt' );
}

add_action( 'init', 'ssp_add_categories_to_podcast' );
function ssp_add_categories_to_podcast () {
  register_taxonomy_for_object_type( 'category', 'podcast' );
}


add_filter('pre_get_posts', 'query_post_type');
function query_post_type($query) {
  if( is_category() ) {
    $post_type = get_query_var('post_type');
    if($post_type)
        $post_type = $post_type;
    else
        $post_type = array('post', 'podcast');
    $query->set('post_type',$post_type);
    return $query;
    }
}


function twentyseventeen_entry_footer() {

	/* translators: used between list items, there is a space after the comma */
	$separate_meta = __( ', ', 'twentyseventeen' );

	// Get Categories for posts.
	$categories_list = get_the_category_list( $separate_meta );

	// Get Tags for posts.
	$tags_list = get_the_tag_list( '', $separate_meta );

	// We don't want to output .entry-footer if it will be empty, so make sure its not.
	if ( ( ( twentyseventeen_categorized_blog() && $categories_list ) || $tags_list ) || get_edit_post_link() ) {

		echo '<footer class="entry-footer">';

			if ( 'post' === get_post_type() || 'podcast' === get_post_type() ) {
				if ( ( $categories_list && twentyseventeen_categorized_blog() ) || $tags_list ) {
					echo '<span class="cat-tags-links">';

						// Make sure there's more than one category before displaying.
						if ( $categories_list && twentyseventeen_categorized_blog() ) {
							echo '<span class="cat-links">' . twentyseventeen_get_svg( array( 'icon' => 'folder-open' ) ) . '<span class="screen-reader-text">' . __( 'Categories', 'twentyseventeen' ) . '</span>' . $categories_list . '</span>';
						}

						if ( $tags_list ) {
							echo '<span class="tags-links">' . twentyseventeen_get_svg( array( 'icon' => 'hashtag' ) ) . '<span class="screen-reader-text">' . __( 'Tags', 'twentyseventeen' ) . '</span>' . $tags_list . '</span>';
						}

					echo '</span>';
				}
			}

			twentyseventeen_edit_link();

		echo '</footer> <!-- .entry-footer -->';
	}
}



/**
 * Create a shortcode to insert content of a page of specified slug
 */
function insertPersonProfile($atts, $content = null) {
  // Default output if no pageid given
  $output = NULL;

  // extract atts and assign to array
  extract(shortcode_atts(array(
    "template" => 'list', // default value could be placed here
    "name" => '',
    "category_name" => ''
  ), $atts));


  // make sure we aren't calling both id and cat at the same time
		if ( isset( $name ) && $name != '' && isset( $category_name ) && $category_name != '' ) {
			return "<p>People Directory error: You cannot set both a single person's name and a category name. Please choose one or the other.</p>";
		}

		$query_args = array(
			'post_type'      => 'staff',
			'posts_per_page' => - 1
		);

		// check if it's a single staff member first, since single members won't be ordered
		if ( ( isset( $name ) && $name != '' ) && ( ! isset( $category_name ) || $category_name == '' ) ) {
			$query_args['name'] = $name;
		}
		// ends single staff

		// check if we're returning a staff category
		if ( ( isset( $category_name ) && $category_name != '' ) && ( ! isset( $name ) || $name == '' ) ) {
            $cats_query = array();

            $cats = explode( ',', $category_name );

            if (count($cats) > 1) {
                $cats_query['relation'] = $params['cat_relation'];
            }

            foreach ($cats as $cat) {
                $cats_query[] = array(
                    'taxonomy' => 'staff_category',
                    'terms'    => $cat,
                    'field'    => "slug"
                );
            }

            $query_args['tax_query'] = $cats_query;
		}

		if ( isset( $orderby ) && $orderby != '' ) {
			$query_args['orderby'] = $orderby;
		}
		if ( isset( $order ) && $order != '' ) {
			$query_args['order'] = $order;
		}

    // echo "<p>" . print_r($query_args) . "</p>";

    $pageContent = new WP_query( $query_args );
    // echo "<p>" . $pageContent->request . "</p>";

    $output .= "<div class='people_directory-" . $template . "'>";
    ob_start();
    while ($pageContent->have_posts()) : $pageContent->the_post();
      get_template_part( 'template-parts/staff/' . $template );
      // $output = get_the_content();
    endwhile;
    $output .= ob_get_clean();
    $output .= "</div>";
    return $output;
}
add_shortcode('people_directory', 'insertPersonProfile');


add_shortcode("mutliline", "convert_multiline");
function convert_multiline($atts) {
    $field = $atts["field"];
    $newContent = "";

    if(!empty($field)) {
        $newContent = nl2br($field);
    }

    return $newContent;
}
