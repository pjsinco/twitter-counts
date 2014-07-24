<?php

# We're on the local environment so toggle IN_PRODUCTION off
if (!defined('IN_PRODUCTION')) {
  define('IN_PRODUCTION', FALSE);
}

# Toggle this based on whether you want to connect to your local DB or your live DB
if (!defined('REMOTE_DB')) {
  define('REMOTE_DB', FALSE);
}

if (!defined('DB_NAME')) {
  define('DB_NAME', 'rempatri_twitter_counts');
}

if (REMOTE_DB) {
	define('DB_HOST', 'localhost');
	define('DB_USER', 'rempatri_tc');
	define('DB_PASS', '142eontario');
	define('DB_PORT', '3306');
} else {
  define('DB_HOST', '127.0.0.1');
  define('DB_USER', 'root');
  define('DB_PASS', 'root');	
	define('DB_PORT', '8889');
}

/*
 * for twitter_counts
 */
DEFINE('TWITTER_OAUTH_ACCESS_TOKEN', 
  '19262807-Nw0e7Ebkf4W0128ky5wDk0Xy6120gWGLZABkLWwk');
DEFINE('TWITTER_OAUTH_ACCESS_TOKEN_SECRET', 
  'grJnluISt7v4typc9AN977o0u1G0n1pkXVKuNTLVR3M');
DEFINE('TWITTER_CONSUMER_KEY', 'MFBFsugvo2smQr8tJQZ2hw');
DEFINE('TWITTER_CONSUMER_SECRET', 
  'OsLrNzVERKTVPFvhOFmW6xYzkhrND5mlAFp2245GgR0');

DEFINE('TWITTER_USERS', serialize(array(
  array(
    'screen_name' => 'TheDOmagazine', 
    'user_id' => '19262807'
  ),
  array(
    'screen_name' => 'AOAforDOs', 
    'user_id' => '273614983'
  )
)));
