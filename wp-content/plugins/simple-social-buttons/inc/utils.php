<?php


/**
* Crul to fetch stats.
*
* @since 2.0
*/
function ssb_fetch_shares_via_curl_multi( $data, $options = array() ) {

  // array of curl handles
  $curly = array();
  // data to be returned
  $result = array();

  // multi handle
  $mh = curl_multi_init();

  // loop through $data and create curl handles
  // then add them to the multi-handle
  if ( is_array( $data ) ) :
    foreach ( $data as $id => $d ) :

      if ( $d !== 0 || $id == 'googleplus' ) :

        $curly[ $id ] = curl_init();

        if ( $id == 'googleplus' ) :

          curl_setopt( $curly[ $id ], CURLOPT_URL, 'https://clients6.google.com/rpc' );
          curl_setopt( $curly[ $id ], CURLOPT_POST, true );
          curl_setopt( $curly[ $id ], CURLOPT_SSL_VERIFYPEER, false );
          curl_setopt( $curly[ $id ], CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . rawurldecode( $d ) . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]' );
          curl_setopt( $curly[ $id ], CURLOPT_RETURNTRANSFER, true );
          curl_setopt( $curly[ $id ], CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) );

          else :

            $url = (is_array( $d ) && ! empty( $d['url'] )) ? $d['url'] : $d;
            curl_setopt( $curly[ $id ], CURLOPT_URL,            $url );
            curl_setopt( $curly[ $id ], CURLOPT_HEADER,         0 );
            curl_setopt( $curly[ $id ], CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curly[ $id ], CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
            curl_setopt( $curly[ $id ], CURLOPT_FAILONERROR, 0 );
            curl_setopt( $curly[ $id ], CURLOPT_FOLLOWLOCATION, 0 );
            curl_setopt( $curly[ $id ], CURLOPT_RETURNTRANSFER,1 );
            curl_setopt( $curly[ $id ], CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $curly[ $id ], CURLOPT_SSL_VERIFYHOST, false );
            curl_setopt( $curly[ $id ], CURLOPT_TIMEOUT, 5 );
            curl_setopt( $curly[ $id ], CURLOPT_CONNECTTIMEOUT, 5 );
            curl_setopt( $curly[ $id ], CURLOPT_NOSIGNAL, 1 );
            curl_setopt( $curly[ $id ], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
            // curl_setopt($curly[$id], CURLOPT_SSLVERSION, CURL_SSLVERSION_SSLv3);
          endif;

          // extra options?
          if ( ! empty( $options ) ) {
            curl_setopt_array( $curly[ $id ], $options );
          }

          curl_multi_add_handle( $mh, $curly[ $id ] );

        endif;
      endforeach;
    endif;

    // execute the handles
    $running = null;
    do {
      curl_multi_exec( $mh, $running );
    } while ( $running > 0 );

    // get content and remove handles
    foreach ( $curly as $id => $c ) {
      $result[ $id ] = curl_multi_getcontent( $c );
      curl_multi_remove_handle( $mh, $c );
    }

    // all done
    curl_multi_close( $mh );

    return $result;
  }



  /**
  * Return false if to fetch the new counts.
  *
  * @return bool
  * @since 2.0
  */
  function ssb_is_cache_fresh( $post_id, $output = false, $ajax = false ) {
    // global $swp_user_options;
    // Bail early if it's a crawl bot. If so, ONLY SERVE CACHED RESULTS FOR MAXIMUM SPEED.
    if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/bot|crawl|slurp|spider/i',  wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) {
      return true;
    }

    // $options = $swp_user_options;
    $fresh_cache = false;

    if( isset( $_POST['ssb_cache'] ) && 'rebuild' === $_POST['ssb_cache'] ) {
      return false;
    }
    // Always be TRUE if we're not on a single.php otherwise we could end up
    // Rebuilding multiple page caches which will cost a lot of time.
    // if ( ! is_singular() && ! $ajax ) {
    //   return true;
    // }

    $post_age = floor( date( 'U' ) - get_post_time( 'U' , false , $post_id ) );

    if ( $post_age < ( 21 * 86400 ) ) {
      $hours = 1;
    } elseif ( $post_age < ( 60 * 86400 ) ) {
      $hours = 4;
    } else {
      $hours = 12;
    }

    $time = floor( ( ( date( 'U' ) / 60 ) / 60 ) );
    $last_checked = get_post_meta( $post_id, 'ssb_cache_timestamp', true );

    if ( $last_checked > ( $time - $hours ) && $last_checked > 390000 ) {
      $fresh_cache = true;
    } else {
      $fresh_cache = false;
    }

    return $fresh_cache;
  }


  /**
  * Fetch fresh counts and cached them.
  *
  * @param  Array  $stats
  * @param  String $post_id
  * @return Array Simple array with counts.
  * @since 2.0
  */
  function ssb_fetch_fresh_counts( $stats, $post_id ,$alt_share_link) {

    $stats_result = array();
    $total = 0;

	  // special case if post id not exist for example short code run on widget out side the loop in archive page
	  if( 0 !== $post_id ){
		  $networks = get_post_meta( $post_id, 'ssb_old_counts', true );
	  }else{
		  $networks = get_option(  'ssb_not_exist_post_old_counts' );
	  }

	  if( ! $networks ){
		  $_result = ssb_fetch_shares_via_curl_multi( array_filter( $alt_share_link ) );
		  ssb_fetch_http_or_https_counts( $_result, $post_id );
		  // special case if post id not exist for example short code run on widget out side the loop in archive page
		  if( 0 !== $post_id ){
			  $networks = get_post_meta( $post_id, 'ssb_old_counts', true );
		  }else{
			  $networks = get_option(  'ssb_not_exist_post_old_counts' );

		  }

	  }

    foreach ( $stats as $social_name => $counts ) {
      if ( 'totalshare' == $social_name || 'viber' == $social_name || 'fblike' == $social_name || 'whatsapp' == $social_name || 'print' == $social_name || 'email' == $social_name || 'messenger' == $social_name )
      { continue; }
      $stats_counts  = call_user_func( 'ssb_format_' . $social_name . '_response', $counts );
	    $new_counts = $stats_counts + $networks[ $social_name];

      $old_counts = get_post_meta( $post_id, 'ssb_' . $social_name . '_counts', true );
      
      // this will solve if new plugin install.
      $old_counts = $old_counts ? $old_counts : 0;
      // if old counts less than new. Return old.
      if ( $new_counts > $old_counts ) {
        $stats_result[ $social_name ] = $new_counts;
      } else {
        $stats_result[ $social_name ] = $old_counts;
      }

	    // special case if post id not exist for example short code run on widget out side the loop in archive page
      if( 0 !== $post_id ) {
        if ( $new_counts > $old_counts ) {
          update_post_meta( $post_id, 'ssb_' . $social_name . '_counts', $new_counts );
        } else {
          // set new counts = old counts for total calculation.
          $new_counts = $old_counts;
        }
      } else {
        update_option( 'ssb_not_exist_post_'. $social_name .'_counts', $new_counts );
      }

	  $total +=  $new_counts;
    }

    $stats_result['total'] = $total;
	  // special case if post id not exist for example short code run on widget out side the loop in archive page
	  if( 0 !== $post_id ){
        update_post_meta( $post_id, 'ssb_total_counts', $total );
	  }else{
		update_option( 'ssb_not_exist_post_total_counts', $total );
	  }

    return $stats_result;
  }
	/**
	 * Fetch counts + http or https resolve .
	 *
	 * @param  Array  $stats
	 * @param  String $post_id
	 * @return Array Simple array with counts.
	 * @since 2.0.12
	 */
	function  ssb_fetch_http_or_https_counts( $stats, $post_id ){
		$stats_result = array();
		$networks = array();
		foreach ( $stats as $social_name => $counts ) {
      if ( 'totalshare' == $social_name || 'viber' == $social_name || 'fblike' == $social_name || 'whatsapp' == $social_name || 'print' == $social_name || 'email' == $social_name || 'messenger' == $social_name )
         { continue; }
			$stats_counts  = call_user_func( 'ssb_format_' . $social_name . '_response', $counts );
			 $networks[ $social_name] = $stats_counts;
		}
		// special case if post id not exist for example short code run on widget out side the loop in archive page
		if( 0 !== $post_id ){
			update_post_meta( $post_id, 'ssb_old_counts', $networks );
		}else{
			update_option( 'ssb_not_exist_post_old_counts', $networks );
		}

	}

  /**
  * Get the cahced counts.
  *
  * @param  Array  $network_name
  * @param  String $post_id
  * @return Array Counts of each network.
  * @since 2.0
  */
  function ssb_fetch_cached_counts( $network_name, $post_id ) {
    $network_name[] = 'total';
    $result = array();
    foreach ( $network_name as $social_name ) {
	    // special case if post id not exist for example short code run on widget out side the loop in archive page
	    if( 0 !== $post_id ){
		    $result[ $social_name ] = get_post_meta( $post_id, 'ssb_' . $social_name . '_counts', true );
	    }else{
		    $result[ $social_name ] = get_option( 'ssb_not_exist_post_'. $social_name .'_counts'  );
	    }
    }
    return $result;
  }
