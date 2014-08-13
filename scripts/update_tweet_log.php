<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

// get all engagement accounts
$q = "
  select e.user_id, u.screen_name
  from tc_engagement_account e inner join tc_user u
    on e.user_id = u.user_id
";

$results = DB::instance()->select_rows($q);

foreach ($results as $acct) {

  echo 'Updating tweet log for ' . $acct['screen_name'] . PHP_EOL;

  $user_id = $acct[ 'user_id' ];

  update_tweet_log($user_id);
}

function update_tweet_log($user_id) {

  $conn = get_connection();
  $params = array(
    'user_id' => $user_id,
    'include_entities' => 'true',
    'include_rts' => 'true',
    'exclude_replies' => 'false',
    'trim_user' => 'true',
    'count' => 100,
    'since_id' => $since_id
  );

  $conn->request(
    'GET',
    $conn->url('1.1/statuses/user_timeline')
    $params
  );

  $q = "
    select *
    from tc_tweet
    where user_id = $user_id
  ";

  $tweets = DB::instance()->select_rows($q);

  krumo($tweets); exit;
}

