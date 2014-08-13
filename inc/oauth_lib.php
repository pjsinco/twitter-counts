<?php
// Copyright (c) 2013 Adam Green. All rights reserved.
// See http://140dev.com/download/license.txt for licensing of this code.
// mods by PJS

// Create an OAuth connection
function get_connection() {

	// Get OAuth tokens for engagement account
	require_once('../config/config.php');
	require_once('../vendor/tmhOAuth.php');
	
	// Create the connection
	// The OAuth tokens are kept in config.php
	$connection = new tmhOAuth(array(
		  'consumer_key'    => TWITTER_CONSUMER_KEY,
		  'consumer_secret' => TWITTER_CONSUMER_SECRET,
		  'user_token'      => TWITTER_OAUTH_ACCESS_TOKEN,
		  'user_secret'     => TWITTER_OAUTH_ACCESS_TOKEN_SECRET
	));
			
	return $connection;
}

?>
