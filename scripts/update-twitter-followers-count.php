#!/usr/bin/env php

<?php

// PDO tutorial
// http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers
//   #Running_Simple_INSERT.2C_UPDATE.2C_or_DELETE_statements

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once('../config/config.php');
require_once('../vendor/TwitterAPIExchange.php');
require_once('../libraries/DB.php');

/*
 * execute twitter api calls
 */
$url = 'https://api.twitter.com/1.1/followers/ids.json';
$request_method = 'GET';
$users = unserialize(TWITTER_USERS);


foreach ($users as $user) {
  $get_field = '?screen_name=' . $user['screen_name'];
  $t_api_call = new TwitterAPIExchange($settings);
  
  $t_api_resp = json_decode($t_api_call->setGetfield($get_field)
    ->buildOauth($url, $request_method)
    ->performRequest(), true);
  
  $followers_count = count($t_api_resp['ids']);

  //Log to db
  $q = "
    insert into tc_followers_count(count_date, count, user_id)
    values ('$date', $followers_count, '" . $user['user_id'] . "')";
  
  //$rows = DB::instance()->update('tc_followers_count', 
  //echo $rows . PHP_EOL;

}

