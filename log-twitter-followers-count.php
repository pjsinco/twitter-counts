#!/usr/bin/env php

<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once('config/config.php');
require_once('inc/TwitterAPIExchange.php');


/*
 * Set up Twitter API calls
 */
$settings = array(
  'oauth_access_token' => TWITTER_OAUTH_ACCESS_TOKEN,
  'oauth_access_token_secret' => TWITTER_OAUTH_ACCESS_TOKEN_SECRET,
  'consumer_key' => TWITTER_CONSUMER_KEY,
  'consumer_secret' => TWITTER_CONSUMER_SECRET
);

// get followers
$url = 'https://api.twitter.com/1.1/followers/ids.json';

$request_method = 'GET';
$get_field = '?screen_name=TheDOmagazine';
$t_api_call = new TwitterAPIExchange($settings);

$t_api_resp = json_decode($t_api_call->setGetfield($get_field)
  ->buildOauth($url, $request_method)
  ->performRequest(), true);

/*
 * Prepare to log to db
 */

$followers_count = count($t_api_resp['ids']);

date_default_timezone_set('America/Chicago');
$date = date('Y-m-d');


/*
 * Log to db
 */
try {
  $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' .  DB_NAME . 
    ';port=' . DB_PORT, DB_USER, DB_PASS);

  // for debugging
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 

  // for production
  //$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  $error = 'Connection failed: ' . $e->getMessage();
}

if (isset($error)) {
  echo $error;
}

$q = "
  insert into tc_followers_count(count_date, count)
  values ('$date', $followers_count)
";

$rows = $db->exec($q);
echo $rows . PHP_EOL;
