<div class="individual-person">
  <a href="<?php echo get_permalink( get_the_ID() ); ?>" class="gallery-link">
  <div class="individual-person-thumb">
  <?php if (has_post_thumbnail( $post->ID ) ){
    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );
  ?>
    <img src="<?php echo $image[0]; ?>" alt="profile image">
  <?php } ?>
  </div>
  <p class="person-name"><?php the_title(); ?> <?php echo do_shortcode("[credentials]"); ?></p>
  </a>
</div>
