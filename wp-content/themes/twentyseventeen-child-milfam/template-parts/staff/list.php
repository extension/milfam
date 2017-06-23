<style type="text/css">
  .person-directory-wrapper {border:1px solid #666; padding:10px; margin-bottom:10px;}
</style>

<div class="person-directory-wrapper">

  <article>
    <header class="entry-header">
      <?php the_title(); ?>
    </header>

	<div class="entry-content">
    <?php the_content(); ?>
    <?php echo do_shortcode("[email]"); ?>
	</div>
</article>

</div>
