<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */

get_header(); ?>

  <div id="content" role="main">

    <div id="flash_mess">
      <div class="slideshow">
        <?php populate_image_slider("Image Slider Home"); ?>
      </div>
    </div>

<div id="home_container">
  <div class="col_1">
    <h2 class="clock">Community Capacity Building</h2>
    <h3>Community Capacity Building in support of military families.</h3>


    <?php $my_query = new WP_Query('category_name=community-capacity-building&posts_per_page=1');
      while ($my_query->have_posts()) : $my_query->the_post();?>
      <?php if ( get_post_meta($post->ID, 'index_image', true) ) : ?>
          <p><?php echo get_post_meta($post->ID, 'index_image', true) ?></p>
      <?php endif; ?>
        <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
  				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

  				<div class="entry">
  					<?php the_content('Read the rest of this entry &raquo;'); ?>
  				</div>

  			</div>
      <?php endwhile; ?>

    <p class="more"><a href="<?php bloginfo('wpurl'); ?>/category/community-capacity-building/" class="more">more programs</a></p>


    <img width="280" height="153" src="<?php bloginfo('template_url'); ?>/images/eX_logo_280px.png">

    <?php query_posts(array('showposts' => 1, 'pagename' => 'extension', 'post_type' => 'page'));

    while (have_posts()) { the_post(); ?>
      <?php if ( get_post_meta($post->ID, 'index_image', true) ) : ?>
          <p><?php echo get_post_meta($post->ID, 'index_image', true) ?></p>
      <?php endif; ?>
        <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
  				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

  				<div class="entry">
  					<?php the_content('Read the rest of this entry &raquo;'); ?>
  				</div>

  			</div>
    <?php }

     wp_reset_query();  // Restore global post data ?>

     <div id="home_left_column_image">
     <?php single_image("Image for home left column"); ?>
     </div>
  </div>

  <div class="col_1">
    <h2 class="shield">Workforce Development</h2>
    <h3>Look what we're doing to increase the work force of qualified child care professionals!</h3>

    <?php $my_query = new WP_Query('category_name=workforce-development&posts_per_page=1');
      while ($my_query->have_posts()) : $my_query->the_post();?>
      <?php if ( get_post_meta($post->ID, 'index_image', true) ) : ?>
          <p><?php echo get_post_meta($post->ID, 'index_image', true) ?></p>
      <?php endif; ?>
        <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
  				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

  				<div class="entry">
  					<?php the_content('Read the rest of this entry &raquo;'); ?>
  				</div>

  			</div>
      <?php endwhile; ?>

      <p class="more"><a href="<?php bloginfo('wpurl'); ?>/category/workforce-development/" class="more">more programs</a></p>



      <?php query_posts(array('showposts' => 1, 'pagename' => 'news-and-upcoming-events', 'post_type' => 'page'));

      while (have_posts()) { the_post(); ?>
        <?php if ( get_post_meta($post->ID, 'index_image', true) ) : ?>
            <p><?php echo get_post_meta($post->ID, 'index_image', true) ?></p>
        <?php endif; ?>
          <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
    				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

    				<div class="entry">
    					<?php the_content('Read the rest of this entry &raquo;'); ?>
    				</div>

    			</div>
      <?php }

       wp_reset_query();  // Restore global post data ?>

       <?php display_quicklinks(); // if there any links categorized as quicklinks, show on homepage ?>
  </div>

  <div class="col_2">
    <h2 class="lightbulb">Strengthening Family, Child Care &amp; Youth Development Programs</h2>
    <h3>Children, Youth, &amp; Family Programs Professional Development &amp; Technical Assistance.</h3>
    <?php $my_query = new WP_Query('category_name=strengthening-families&posts_per_page=1');

      while ($my_query->have_posts()) : $my_query->the_post();?>

      <?php if ( get_post_meta($post->ID, 'index_image', true) ) : ?>
          <p><?php echo get_post_meta($post->ID, 'index_image', true) ?></p>
      <?php endif; ?>


        <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
  				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

  				<div class="entry">
  					<?php the_content('Read the rest of this entry &raquo;'); ?>
  				</div>

  			</div>
      <?php endwhile; ?>

      <p class="more"><a href="<?php bloginfo('wpurl'); ?>/category/strengthening-families/" class="more">more programs</a></p>

      <div id="contacts">
      <?php query_posts(array('showposts' => 1, 'pagename' => 'contacts', 'post_type' => 'page'));

      while (have_posts()) { the_post(); ?>
        <?php if ( get_post_meta($post->ID, 'index_image', true) ) : ?>
            <p><?php echo get_post_meta($post->ID, 'index_image', true) ?></p>
        <?php endif; ?>
          <div <?php post_class() ?> id="post-<?php the_ID(); ?>">
    				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

    				<div class="entry">
    					<?php the_content('Read the rest of this entry &raquo;'); ?>
    				</div>

    			</div>
      <?php }

       wp_reset_query();  // Restore global post data ?>
      </div>
      <p class="more"><a href="<?php bloginfo('wpurl'); ?>/contacts" class="more">View Full List</a></p>
  </div>

</div>


	</div>

<?php get_footer(); ?>
