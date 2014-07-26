<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

// fine out if any friends have already been collected
$q = "
  SELECT count(*) as count
  FROM tc_friend
";
$results = DB::instance()->select_rows($q);


if (!$results[0]['count']) {
  $first_collection = 1;
} else {
  // if friends have been collected, set `current` = 0
  $first_collection =  0;
  DB::instance()->update_row(
    'tc_friend',
    array(
      'current' => 0  
    )
  );

