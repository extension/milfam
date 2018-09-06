<?php

function ssb_tumblr_generate_link( $url ) {
	$request_url = 'https://api.tumblr.com/v2/share/stats?url=' . $url;
	return $request_url;
}


function ssb_format_tumblr_response( $response ) {

	$counts   = 0;
 $response = json_decode( $response, true );
  // Check is valid api response
	if ( $response['meta']['status'] == 200 ) {
		$counts = $response['response']['note_count'];
	}

	return $counts;
}
