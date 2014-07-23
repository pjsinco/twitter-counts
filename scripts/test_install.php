<?php 

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';

$users = unserialize(TWITTER_USERS);
$conn = get_connection();

// make the api request
$conn->request( 'GET', $conn->url('1.1/users/show'), 
  array('user_id' => $users[1]['user_id']));

// get the http response code for the api request
$response_code = $conn->response['code'];

//echo '<pre>'; var_dump($conn->response); echo '</pre>'; // debug


if ($response_code != 200) {
  echo 'Error: ' . $response_code . PHP_EOL;
  echo $conn->response['response'];
} else {
  $user = json_decode($conn->response['response'], true);
  
  echo '<pre>'; var_dump($user); echo '</pre>'; // debug


  // collect all the user data
  $user_id = $user['id'];
  $name = ($user['name']);
  $screen_name = ($user['screen_name']);
  $profile_image_url = ($user['profile_image_url']);
  $location       = ($user['location']);
  $url            = ($user['url']);
  $description    = ($user['description']);
  $created_at     = DB::instance()->date($user['created_at']);
  $followers_count= $user['followers_count'];
  $friends_count  = $user['friends_count'];
  $statuses_count = $user['statuses_count'];
  $listed_count   = $user['listed_count'];
  $lang           = ($user['lang']);
  $last_tweet_date= DB::instance()->
    date($user['status']['created_at']);

  // protected element is blank if user is not protected
  if (empty($user['protected'])) {
    $protected = 0;
  } else {
    $protected = 1;
  }
    
  $data = array(
    'user_id' => $user_id,
    'screen_name' => $screen_name,
    'name' => $name,
    'profile_image_url' => $profile_image_url,
    'location' => $location,
    'url' => $url,
    'description' => $description,
    'created_at' => $created_at,
    'followers_count' => $followers_count,
    'friends_count' => $friends_count,
    'statuses_count' => $statuses_count,
    'listed_count' => $listed_count,
    'protected' => $protected,
    'lang' => $lang,
    'last_tweet_date' => $last_tweet_date
  );
  
  // insert the user if now already in the db
  if (!DB::instance()->in_table('tc_user', "user_id = $user_id")) {
    DB::instance()->insert('tc_user', $data);
  } else {
    DB::instance()->
      update_row('tc_user', $data, 'WHERE user_id = ' . $user_id);
  }

  // gather data from user's last tweet
  $tweet_id = $user['status']['id'];
  $tweet_text = $user['status']['text'];
  $retweet_count = $user['status']['retweet_count'];

  // todo
  // insert tweet if not already in the db
//  if (!DB::instance()->
//    in_table('tc_tweet', 'tweet_id = ' . $tweet_id)) {
//      DB::instance()->insert('tc_tweet', array(
//        'tweet_id' => $tweet_id,
//        'tweet_text' => $tweet_text,
//        'created_at' => $last_tweet_date,
//        'user_id' => $user_id,
//        'retweet_count' => $retweet_count,
//        'favorite_count' => $favorite_count,
//        'is_rt' => (empty($user['status']['retweeted_status']) ? 0 : 1)
//      );
//  } else {
//    DB::instance()->update('tc_tweet', 
//  }



    
}

