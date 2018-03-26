<?php


function ssb_format_fbshare_response( $response ) {

	$formatted_response = json_decode( $response , true );
	$likes              = isset( $formatted_response['og_object'] ) ? $formatted_response['og_object']['likes']['summary']['total_count'] : 0;
	$comments           = $formatted_response['share']['comment_count'];
	$shares             = $formatted_response['share']['share_count'];
	$total              = $likes + $comments + $shares;
	return $total;
}

function ssb_fbshare_generate_link( $url ) {
	$link = 'https://graph.facebook.com/?fields=og_object{likes.summary(true).limit(0)},share&id=' . $url;
	return $link;
}
