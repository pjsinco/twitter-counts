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
krumo($q_results); exit;
