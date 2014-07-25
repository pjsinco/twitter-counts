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
  SELECT user_id, screen_name
  FROM tc_leader
";

$q_results = DB::instance()->select_rows($q);

foreach ($q_results as $row) {

  $conn->request(
    'GET',
    $conn->url('1.1/users/show'),
    array(
      'user_id' => $row['user_id']
    )
  );

  $response_code = $conn->response['code'];

  // api reqest failed
  if ($conn->response['code'] != 200) {
    echo 'Error for @' . $row['user_id'] . ': ' . $response_code . 
      PHP_EOL;
    echo $conn->response['response'];
  } else {

    $user = json_decode($conn->response['response']);

    // collect all user account values we want to record
    $name              = $tweet->user->name;
    $screen_name       = $tweet->user->screen_name;
    $profile_image_url = $tweet->user->profile_image_url;
    $location          = $tweet->user->location;
    $description       = $tweet->user->description;
    $url               = $tweet->user->url;
    $created_at        = 
      DB::instance()->date($tweet->user->created_at);
    $friends_count     = $tweet->user->friends_count;
    $followers_count   = $tweet->user->followers_count;
    $statuses_count    = $tweet->user->statuses_count;
    $listed_count      = $tweet->user->listed_count;
    $lang              = $tweet->user->lang;

    if (empty($user->protected) {
      // if an acct is not protected,
      // $user->protected is blank
      $protected = 0;
    } else {
      $protected = 1;
    }

    $last_tweet_date = DB::instance()->date($user->status->created_at);
  }
  
} // end foreach
