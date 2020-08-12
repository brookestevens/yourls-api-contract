<?php
/*
Plugin Name: API contract custom action
Plugin URI: http://yourls.org/
Description: Define API action 'contract' to check if a URL has been shortened, but not create one
Version: 1.0
Author: Brooke Stevens
Author URI: https://github.com/brookestevens
*/

// Define custom action "delete"
yourls_add_filter( 'api_action_contract', 'contract_url' );

function get_long_url_stats($results){
	$i = 1;
	$links = [];
	foreach ( (array)$results as $res ) {
		$links['links']['link_'.$i++] = [
			'shorturl' => YOURLS_SITE .'/'. $res->keyword,
			'url'      => $res->url,
			'title'    => $res->title,
			'timestamp'=> $res->timestamp,
			'ip'       => $res->ip,
			'clicks'   => $res->clicks,
		];
	}
	return $links['links'];
}

function contract_url() {
	// Need 'url' parameter
	if( !isset( $_REQUEST['url'] ) ) {
		return array(
			'statusCode' => 400,
			'simple'     => "Need a 'url' parameter",
			'message'    => 'error: missing param',
		);	
	}

	$url = $_REQUEST['url']; // parameter from request
	$url = yourls_encodeURI( $url );
    $url = yourls_sanitize_url( $url );
    if ( !$url || $url == 'http://' || $url == 'https://' ) {
        $return['status']    = 'fail';
        $return['code']      = 'error:nourl';
        $return['message']   = yourls__( 'Missing or malformed URL' );
        $return['errorCode'] = '400';
    }

	global $ydb;
    $table = YOURLS_DB_TABLE_URL;
    $url_exists = $ydb->fetchObjects("SELECT * FROM `$table` WHERE `url` = :url", array('url'=>$url));
    if ($url_exists === false) {
        $url_exists = NULL;
	}
	
	if(!$url_exists){
		return array(
			'statusCode' => 200, // HTTP-like status code
			'simple'     => "This URL has not been shortened",
			'message'    => 'success',
			'url_exists' => false
		);
	}
	else{
		// the URL has been shortened already
		// $keywords = yourls_get_longurl_keywords($url);
		$links = get_long_url_stats($url_exists);

		return array(
			'statusCode' => 200, // HTTP-like status code
			'simple'     => "This URL has been shortened",
			'message'    => 'success',
			'url_exists' => true,
			'links' => $links
		);
	}

}
