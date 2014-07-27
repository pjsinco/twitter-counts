<?php
// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

require 'collect_user_profiles.php';

// get all engagement accts
$q = "
  SELECT user_id, screen_name
  FROM tc_engagement_account
";

$q_results = DB::instance()->select_rows($q);

foreach ($q_results as $user) {

  echo 'Working on ' . $user['screen_name'] . "\r\n";

  $q = "
    SELECT user_id
    FROM tc_friend
    WHERE friend_of = " . $user['user_id'] . "
    AND user_id NOT IN (
      select user_id
      from tc_user
    )
    LIMIT 15000
  ";

  $q_results = DB::instance()->select_rows($q);
  if (!$q_results) {
    echo 'All friends user accounts have been collected!';
    exit;
  }

  while (true) {

    if (count($q_results) == 0) {
      return;
    }

    // put 100 user ids in comma delim list
    $user_list = '';
    
    for ($i = 0; $i < 100; $i++) {
      $user_list_arr = array_pop($q_results);
      $user_list .= $user_list_arr['user_id'] . ',';
    }

    // snip off last comma
    $user_list = substr($user_list, 0, -1);

    $response_code = 

  } // endwhile

} //endforeach
