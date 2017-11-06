<?php

function ssb_pinterest_generate_link( $url ) {
	$request_url = 'https://api.pinterest.com/v1/urls/count.json?url=' . $url ;

	return $request_url;
}


function ssb_format_pinterest_response( $response ) {

	$response = preg_replace( '/^receiveCount\((.*)\)$/', "\\1", $response );
	$response = json_decode( $response,true );
	return isset( $response['count'] ) ? intval( $response['count'] ) : 0;
}
