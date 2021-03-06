<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

/*
 * @param $followers comma-separated list of twitter user id's
 */
function collect_user_profiles($user_list) {
  
  $conn = get_connection();
  $conn->request(
    'GET',
    $conn->url('1.1/users/lookup'),
    array('user_id' => $user_list)
  ); // end request

  $response_code = $conn->response['code'];

  if ($response_code <> 200) {
    echo "Error: $response_code\n";
    echo $conn->response['response'];
    return $response_code;
  } else {
    $results = json_decode($conn->response['response'], false);

    foreach ($results as $user) {
      // collect all user account values we want to record
      $user_id           = $user->id;
      $name              = $user->name;
      $screen_name       = $user->screen_name;
      $profile_image_url = $user->profile_image_url;
      $location          = $user->location;
      $description       = $user->description;
      $url               = $user->url;
      $user_created_at   = 
        DB::instance()->date($user->created_at);
      $friends_count     = $user->friends_count;
      $followers_count   = $user->followers_count;
      $statuses_count    = $user->statuses_count;
      $listed_count      = $user->listed_count;
      $lang              = $user->lang;

      $protected = (empty($user->protected)) ? 0 : 1;

      $last_tweet_date = (isset($user->status)) ? 
        DB::instance()->date($user->status->created_at) : '0000-00-00';

      $data = array(
        'user_id' => $user_id,
        'name' => $name,
        'screen_name' => $screen_name,
        'profile_image_url' => $profile_image_url,
        'location' => $location,
        'description' => $description,
        'url' => $url,
        'created_at' => $user_created_at,
        'friends_count' => $friends_count,
        'followers_count' => $followers_count,
        'statuses_count' => $statuses_count,
        'listed_count' => $listed_count,
        'lang' => $lang,
        'protected' => $protected,
        'last_tweet_date' => $last_tweet_date
      );

      // insert user into db
      if (!DB::instance()->in_table('tc_user', "user_id = $user_id")) {
        DB::instance()->insert('tc_user', $data);
        echo "\tInserted $user_id\r\n";
      } else {
        DB::instance()->update_row('tc_user', $data, 
          "where user_id = $user_id");
        echo "Updated $user_id" . PHP_EOL;
      }

    } // end foreach

  } // endelse
  return $response_code;
}
