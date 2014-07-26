<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

// prep for our api call
$conn = get_connection();

$q = "
  SELECT user_id
  FROM tc_user
  WHERE last_updated < subdate(now(), interval 48 hour)
  limit 150
";

$q_results = DB::instance()->select_rows($q);

foreach ($q_results as $row) {

  $user_id = $row['user_id'];

  // see if this user can be found without an error
  $conn->request(
    'GET',
    $conn->url('1.1/users/show'),
    array(
      'user_id' => $user_id
    )
  );
  $response_code = $conn->response['code'];
  
  //  404 says this account has been deleted
  if ($response_code == 404) {
    
    // delete the user and all related data
    DB::instance()->delete_row('tc_user', 'where user_id = ' . $user_id);
    DB::instance()->
      delete_row('tc_user_tag', 'where user_id = ' . $user_id);
    DB::instance()->
      delete_row('tc_tweet', 'where user_id = ' . $user_id);
    DB::instance()->
      delete_row('tc_tweet_mention', 
        'where source_user_id = ' . $user_id . 'or target_user_id = ' .
        $user_id);
    DB::instance()->
      delete_row('tc_tweet_retweet', 
        'where source_user_id = ' . $user_id . 'or target_user_id = ' .
        $user_id);
    DB::instance()->
      delete_row('tc_tweet_tag', 'where user_id = ' . $user_id); 
    DB::instance()->
      delete_row('tc_tweet_url', 'where user_id = ' . $user_id); 

  } elseif ($response_code == 403) {
    DB::instance()->update_row(
      'tc_user', 
      array(
        'suspended' => 1
      ),
      'WHERE user_id = ' . $user_id
    );
  }

} // end foreach
