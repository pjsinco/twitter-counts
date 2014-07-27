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

// step through each engagement acct
foreach ($q_results as $user) {

  echo 'Adding friends for ' . $user['screen_name'] . PHP_EOL;

  // fine out if any friends have already been collected
  $q = "
    SELECT count(*) as count
    FROM tc_friend
    WHERE friend_of = " . $user['user_id'];
  $results = DB::instance()->select_rows($q);
  
  if (!$results[0]['count']) {
    $first_collection = 1;
  } else {
    // if friends have been collected, set `current`=0 for all rows
    $first_collection =  0;
    $q_update = DB::instance()->update_row(
      'tc_friend',
      array(
        'current' => 0  
      )
    );
  }
  
  // loop through all friends
  // start with a cursor value of -1, because this is our 
  // 1st collection request (loc6933)
  $cursor = -1;
  
  // $cursor will be 0 when there are no more friends to request
  // https://dev.twitter.com/docs/api/1.1/get/friends/ids
  while ($cursor != 0) { 
  
    $conn->request(
      'GET',
      $conn->url('1.1/friends/ids'),
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

      // step through each friend
      if (count($ids)) {
        foreach ($ids as $friend_user_id) {

          // if this friend is already in table, update `current` = 1
          if (DB::instance()->
            in_table('tc_friend', "user_id = $friend_user_id")) {
            
            DB::instance()->update_row(
              'tc_friend',
              array('current' => 1),
              "where user_id = $friend_user_id"
            );
  
          } else {
            // this is a new friend, so insert with `current` = 1
            DB::instance()->insert(
              'tc_friend',
              array(
                'user_id' => $friend_user_id,
                'current' => 1,
                'friend_of' => $user['user_id']
              )
            );

            // if this is not the first time friends have been collected,
            // record this new friend event in the follow_log table 
            if ($first_collection) {
              DB::instance()->insert(
                'tc_follow_log',
                array(
                  'user_id' => $friend_user_id,
                  'event' => 'friend',
                  'event_for' => $user['user_id']
                )
              );
            } 
          } //endelse
        } // endforeach
      } else {
        break; // stop collecting if no more friends are found
      }
    } else {
      // an error occurred, so try again later
      echo 'Error' . PHP_EOL;
      exit;
    }
  } // endwhile

  // find any friends who were collected in the past but not this time
  $q = "
    SELECT user_id
    FROM tc_friend
    WHERE current = 0
      and friend_of = " . $user['user_id'];

  $results = DB::instance()->select_rows($q);

  foreach ($results as $row) {
    
    // record the unfriend event; this means the engagement account
    // unfollowed this friend
    DB::instance()->insert(
      'tc_follow_log',
      array(
        'event_for' => $user['user_id'],
        'event' => 'unfriend',
        'user_id' => $friend_user_id
      )
    );

    // delete any friends who are not found during collection
    DB::instance()->delete_row(
      'tc_friend',
      'where user_id = ' . $friend_user_id
    );
  }
} // end foreach ($q_results as $user)
