<?php
// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

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
    FROM tc_follower
    WHERE follower_of = " . $user['user_id'] . "
    AND user_id NOT IN (
      select user_id
      from tc_user
    )
    LIMIT 5000 
  "; // normal limit is 15000, but we're processing 3 accts

  $q_results = DB::instance()->select_rows($q);
  if (!$q_results) {
    echo 'All followers user accounts have been collected!';
    continue;
  }

  while (true) {

    if (count($q_results) == 0) {
      continue;
    }

    // put 100 user ids in comma delim list
    $user_list = '';
    
    $list_count = (count($q_results) > 99 ? 100 : count($q_results));
    for ($i = 0; $i < $list_count; $i++) {
      $user_list_arr = array_pop($q_results);
      $user_list .= $user_list_arr['user_id'] . ',';
    }

    // snip off last comma
    $user_list = substr($user_list, 0, -1);

    $response_code = collect_user_profiles($user_list);

    if ($response_code != 200) {
      break;
    }

  } // endwhile

} //endforeach

