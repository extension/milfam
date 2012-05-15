<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
?>



<div id="logogroupfooter">
  <img src="<?php bloginfo('template_url'); ?>/images/logobartop.jpg" />
	<div style="padding-left:0px" class="bottomlogo"><img src="<?php bloginfo('template_url'); ?>/images/dod_logo.jpg" alt="" /></div>
	<!-- Substitute your program's logo here, instead of replacelogo.jpg -->
	<div class="bottomlogo center">
	  <?php single_image("Footer Logo"); ?>
	</div>
	<div style="padding-right:0px"  class="bottomlogo"><img src="<?php bloginfo('template_url'); ?>/images/smaller-USDA_logo.svg.png" alt="" /></div>
  <img src="<?php bloginfo('template_url'); ?>/images/logobarbottom.jpg" />
</div>

<div id="footer">
  <?php footer_links("Footer"); ?>
  <p>
	  <?php bloginfo('name'); ?> is proudly powered by <a href="http://wordpress.org/">WordPress</a>. <a href="<?php bloginfo('rss2_url'); ?>">Entries (RSS)</a> and <a href="<?php bloginfo('comments_rss2_url'); ?>">Comments (RSS)</a>
  </p>
</div>



		<?php wp_footer(); ?>
		
		
		<script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-155321-35']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
</body>
</html>






