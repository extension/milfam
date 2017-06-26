<div class="individual-person">
  <div class="col_one">
    <?php if (has_post_thumbnail( $post->ID ) ){
      $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );
    ?>
      <img src="<?php echo $image[0]; ?>" alt="profile image">
    <?php } ?>

    <h3 class="person-name"><?php the_title(); ?><?php echo do_shortcode("[credentials]"); ?></h3>
  </div>
  <div class="col_two">
    <?php the_content(); ?>
  </div>
</div>
