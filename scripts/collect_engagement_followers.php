<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

$conn = get_connection();

$q = "
  SELECT user_id, screen_name
  FROM tc_engagement_account
";

$q_results = DB::instance()->select_rows($q);

foreach ($q_results as $user) {

  // set up engagement user id
  $user_id = $user['user_id'];

  $q = "
    SELECT count(*) as count
    FROM tc_follower
    where follower_of = $user_id;
  ";

  $q_results = DB::instance()->select_rows($q);

  if (!$q_results[0]['count']) {
    $first_collection = true;
  } else {
    // if followers have been collected, set `current`=0 for all rows
    $first_collection =  false;
    $q_update = DB::instance()->update_row(
      'tc_follow', array('current' => 0)
    );
  }
  
  // loop through all followers
  // start with a cursor value of -1, because this is our 
  // 1st collection request (loc6933)
  $cursor = -1;
  
  // $cursor will be 0 when there are no more followers to request
  // https://dev.twitter.com/docs/api/1.1/get/followers/ids
  while ($cursor != 0) { 
  
    $conn->request(
      'GET',
      $conn->url('1.1/followers/ids'),
      array(
        'user_id' => $user['user_id'],
        'cursor' => $cursor
      )
    );

    $response_code = $conn->response['code'];
    if ($response_code == 200) {

      $data = json_decode($conn->response['response'], false);
  
      // get list of IDs
      $ids = $data->ids;

      // get the cursor value for the next request
      $cursor = $data->next_cursor_str;

      // step through each follower
      if (count($ids)) {
        foreach ($ids as $follower_user_id) {

          // if this follower is already in table, update `current` = 1
          if (DB::instance()->
            in_table('tc_follower', "user_id = $follower_user_id")) {
            
            DB::instance()->update_row(
              'tc_follower',
              array('current' => 1),
              "where user_id = $follower_user_id"
            );
  
          } else {
            // this is a new follower, so insert with `current` = 1
            DB::instance()->insert(
              'tc_follower',
              array(
                'user_id' => $follower_user_id,
                'current' => 1,
                'follower_of' => $user_id
              )
            );

            // if this is not the first time followers have been collected,
            // record this new follower event in the follow_log table 
            if ($first_collection) {
              DB::instance()->insert(
                'tc_follow_log',
                array(
                  'user_id' => $follower_user_id,
                  'event' => 'follow',
                  'event_for' => $user_id
                )
              );
            } 
          } //endelse
        } // endforeach
      } else {
        break; // stop collecting if no more followers are found
      }
    } else {
      // an error occurred, so try again later
      echo 'Error' . PHP_EOL;
      exit;
    }
  } // endwhile

  // find any followers who were collected in the past but not this time
  $q = "
    SELECT user_id
    FROM tc_follower
    WHERE current = 0
      and follower_of = " . $user_id;

  $results = DB::instance()->select_rows($q);

  foreach ($results as $row) {
    
    // record the unfollow event; this means the engagement account
    // was unfollowed by this user
    DB::instance()->insert(
      'tc_follow_log',
      array(
        'event_for' => $user_id,
        'event' => 'unfollow',
        'user_id' => $follower_user_id
      )
    );

    // delete any followers who are not found during collection
    DB::instance()->delete_row(
      'tc_follower',
      'where user_id = ' . $follower_user_id
    );
  }
} // end foreach ($q_results as $user)
