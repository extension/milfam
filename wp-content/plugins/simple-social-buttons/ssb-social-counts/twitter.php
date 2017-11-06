<?php

function ssb_twitter_generate_link( $url ) {

	// Return the correct Twitter JSON endpoint URL
	$request_url = 'http://public.newsharecounts.com/count.json?url=' . $url;
	return $request_url;
}


function ssb_format_twitter_response( $response ) {
	// Parse the response to get the actual number
	$response = json_decode( $response, true );
	return isset( $response['count'] ) ? intval( $response['count'] ) : 0;
}
