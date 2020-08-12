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
    $url_exists = $ydb->fetchObject("SELECT * FROM `$table` WHERE `url` = :url", array('url'=>$url));
    if ($url_exists === false) {
        $url_exists = NULL;
	}
	
	if(!$url_exists){
		return array(
			'statusCode' => 200, // HTTP-like status code
			'simple'     => "This URL has not been shortened",
			'message'    => 'success',
			'your_action' => array( 
				'shortened' => false
			),
		);
	}
	else{
		// the URL has been shortened already
		$keywords = yourls_get_longurl_keywords($url);
		foreach($keywords as &$i){
			$i = YOURLS_SITE . $i; 
		}
		return array(
			'statusCode' => 200, // HTTP-like status code
			'simple'     => "This URL has been shortened",
			'message'    => 'success',
			'your_action' => array( 
				'shortened' => true,
				'shorturl' => $keywords
			),
		);
	}

}
