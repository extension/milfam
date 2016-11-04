<?php

function remove_footer_admin () {
  echo 'Thank you for creating with <a href="https://wordpress.org/">WordPress</a> | <a href="http://www.extension.org/main/termsofuse" target="_blank">Terms of Use</a>';
}
add_filter('admin_footer_text', 'remove_footer_admin');
