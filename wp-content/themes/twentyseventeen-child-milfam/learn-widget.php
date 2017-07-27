<!-- shortcode format: [learn_widget key="exlw-XXXXXXXX" tags="tag, another tag" limit=3 match_all_tags=true event_type="upcoming"] -->

<!-- Learn -->
<script type="text/javascript">
(function() {
  function async_load(){
    var ex_learn = document.createElement('script'); ex_learn.type = 'text/javascript'; ex_learn.async = true;
    ex_learn.src = "https://learn.extension.org/widgets/events.js?widget_key=<?php echo $a['key'] ?>&limit=<?php echo $a['limit'] ?>&tags=<?php echo $a['tags'] ?>&operator=<?php echo $a['operator'] ?>&event_type=<?php echo $a['event_type'] ?>";
    var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(ex_learn, s);
  }
  if (window.attachEvent)
    window.attachEvent('onload', async_load);
    else
      window.addEventListener('load', async_load, false);
    })();
</script>
<div id="<?php echo $a['key'] ?>"></div>
<!-- Learn -->
