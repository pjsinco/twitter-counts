<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

collect_user_profiles('19262807,273614983');

/*
 * @param $followers comma-separated list of twitter user id's
 */
function collect_user_profiles($user_list) {
  
  require_once('../libraries/DB.php');
  require_once('../inc/oauth_lib.php');
  //require_once('../config/config.php');
  //require_once('../inc/TwitterAPIExchange.php');
  
  $conn = get_connection();
  $conn->request(
    'GET',
    $conn->url('1.1/users/lookup'),
    array('user_id' => $user_list)
  ); // end request

  $response_code = $conn->response['code'];
  //echo '<pre>'; var_dump($conn->response); echo '</pre>';exit;

  if ($response_code <> 200) {
    echo "Error: $response_code\n";
    echo $conn->response['response'];
    return $response_code;
  } else {
    $response_data = json_decode($conn->response['response'], true);

    foreach ($response_data as $user) {
      echo '<pre>';
      var_dump($user);

      $user_id = $user['id'];
      $name = DB::instance()->escape($user['name']);
      $screen_name = DB::instance()->escape($user['screen_name']);
      $profile_image_url = DB::instance()->escape($user['screen_name']);
      $location       = DB::instance()->escape($user['location']);
      $url            = DB::instance()->escape($user['url']);
      $description    = DB::instance()->escape($user['description']);
      $created_at     = DB::instance()->escape($user['created_at']);
      $followers_count= $user['followers_count'];
      $friends_count  = $user['friends_count'];
      $statuses_count = $user['statuses_count'];
      $listed_count   = $user['listed_count'];
      $suspended      = DB::instance()->escape($user['location']);
      $lang           = DB::instance()->escape($user['lang']);
      $last_tweet_date= DB::instance()->escape($user['location']);
      // todo finish this cond'l stmt
      if (empty($user['protected'])) {
        $protected = 0;
      }
        $protected      = DB::instance()->escape($user['location']);

      
      
      echo '</pre>';
      //$user_id = $user['user_id'];
      //$user_id = $user['user_id'];
      //$user_id = $user['user_id'];
      //$user_id = $user['user_id'];
      //$user_id = $user['user_id'];
    } // end foreach

  }

}
