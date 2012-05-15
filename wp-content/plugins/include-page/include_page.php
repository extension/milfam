<?php
/*
Plugin Name: Include Page
Version: 1.2
Plugin URI: http://beetle.cbtlsl.com/archives/category/wordpress-plugins/
Author: Brent Loertscher
Author URI: http://beetle.cbtlsl.com
Description: This plugin adds an include_page() function that allows you to
include the contents of a static page in a template.

Installation:

1.  Download the file http://beetle.cbtlsl.com/wp-plugins/include_page.phps
2.  Rename the file to include_page.php and put it in your wp-content/plugins/
    directory.
3.  Activate the plugin from your WordPress admin 'Plugins' page.
4.  Make use of the function in your template.

function include_page ($post_id,$filter=FALSE) - include page with corresponding post_id

Change Log

1.1: Added the ability to apply filters to the page before it is echoed.
1.0: Initial Release.
*/

/*
Copyright (c) 2005, Brent Loertscher
Released under the GPL license
All rights reserved.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT
NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL
THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

function include_page ($post_id, $filter=FALSE) {
   global $wpdb;

   $pages = $wpdb->get_results ("SELECT " .
      "post_content " .
      "FROM $wpdb->posts " .
      "WHERE ID = " . $post_id);

   if ($pages):
      foreach ($pages as $page):
         if ($filter):
            $page->post_content = apply_filters('the_content',stripslashes($page->post_content));
         else:
            $page->post_content = stripslashes($page->post_content);
         endif;
         echo $page->post_content;
      endforeach;
   endif;
}

?>
