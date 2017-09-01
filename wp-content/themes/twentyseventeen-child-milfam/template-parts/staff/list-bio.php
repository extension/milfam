<div class="individual-person">
  <div class="col_one">
    <div class="individual-person-thumb-wrapper">
    <div class="individual-person-thumb">
      <?php if (has_post_thumbnail( $post->ID ) ){
        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ) );
      ?>
        <img src="<?php echo $image[0]; ?>" alt="profile image">
      <?php } ?>
    </div>
    </div>
    <h3 class="person-name"><?php the_title(); ?><?php echo do_shortcode("[credentials]"); ?></h3>
  </div>
  <div class="col_two">
    <?php the_content(); ?>
    <ul>


      <?php if ( get_post_meta( get_the_ID(), 'position', true ) ): ?>
          <li class="person-position">
            <?php echo nl2br(get_post_meta( get_the_ID(), 'position', true )); ?>
          </li>
      <?php endif; ?>

      <?php if ( get_post_meta( get_the_ID(), 'address', true ) ): ?>
          <li class="person-address">
            <?php echo nl2br(get_post_meta( get_the_ID(), 'address', true )); ?>
          </li>
      <?php endif; ?>

      <?php if ( get_post_meta( get_the_ID(), 'email', true ) ): ?>
          <li class="person-email">
            <a href="mailto:<?php echo sanitize_email(get_post_meta( get_the_ID(), 'email', true )); ?>"><?php echo get_post_meta( get_the_ID(), 'email', true ); ?></a>
          </li>
      <?php endif; ?>

      <?php if ( get_post_meta( get_the_ID(), 'phone_number', true ) ): ?>
          <li class="person-phone_number">
            <?php echo get_post_meta( get_the_ID(), 'phone_number', true ); ?>
          </li>
      <?php endif; ?>
    </ul>
  </div>
</div>
