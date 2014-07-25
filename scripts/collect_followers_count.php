<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

// get all engagement acct users
$q = "
  SELECT user_id
  FROM tc_engagement_account
";

$q_results = DB::instance()->select_rows($q);

$conn = get_connection();
$params = array(
  'count' => 5000
);

foreach ($q_results as $user):

  $params['user_id'] = $user['user_id'];
  $conn->request(
    'GET',
    $conn->url('1.1/followers/ids'),
    $params
  );

  $results = json_decode($conn->response['response']);
  $followers_count = count($results->ids);

  DB::instance()->insert(
    'tc_followers_count',
    array(
      'user_id' => $user['user_id'],
      'count_date' => date('Y-m-d'),
      'count' => $followers_count
    ) 
  );

endforeach;

