<!-- shortcode format:
[aweber_signup key="1899348052"]
-->

<div class="aweber-wrapper">
<div class="AW-Form-<?php echo $a['key'] ?>"></div>
<script type="text/javascript">(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//forms.aweber.com/form/52/<?php echo $a['key'] ?>.js";
    fjs.parentNode.insertBefore(js, fjs);
    }(document, "script", "aweber-wjs-<?php echo $a['key'] ?>"));
</script>
</div>
