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
  function ssb_fetch_fresh_counts( $stats, $post_id ) {

    $stats_result = array();
    $total = 0;

    foreach ( $stats as $social_name => $counts ) {
      if ( 'totalshare' == $social_name  || 'fblike' == $social_name || 'viber' == $social_name || 'whatsapp' == $social_name ) { continue; }
      $stats_counts  = call_user_func( 'ssb_format_' . $social_name . '_response', $counts );
      $stats_result[ $social_name ] = $stats_counts;
      update_post_meta( $post_id, 'ssb_' . $social_name . '_counts', $stats_counts );
      $total += $stats_counts;
    }
    $stats_result['total'] = $total;
    update_post_meta( $post_id, 'ssb_total_counts', $total );
    return $stats_result;
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
      $result[ $social_name ] = get_post_meta( $post_id, 'ssb_' . $social_name . '_counts', true );
    }
    return $result;
  }
