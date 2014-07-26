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
  SELECT user_id
  FROM tc_user
  WHERE last_updated < subdate(now(), interval 48 hour)
  limit 150
";

$q_results = DB::instance()->select_rows($q);

foreach ($q_results as $row) {

  $user_id = $row['user_id'];

  // see if this user can be found without an error
  $conn->request(
    'GET',
    $conn->url('1.1/users/show'),
    array(
      'user_id' => $user_id
    )
  );

  krumo($conn->response['code']); exit;
  

} // end foreach
