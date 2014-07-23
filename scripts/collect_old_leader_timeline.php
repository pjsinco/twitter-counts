<?php 

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';

// prep for our api call
$conn = get_connection();

$q = "
  SELECT user_id
  FROM tc_leader
  WHERE old_timeline_collected = '0000-00-00'
";

$results = DB::instance()->select_rows($q);

if (!$results) {
  echo 'All the older leader timelines have already been collected.';
  exit;
}

for ($i = 0; $i < count($results); $i++) {
  $user_id = $results[$i]['user_id'];

  $params = array(
    'user_id' => $user_id,
    'include_entities' => 'true',
    'include_rts' => 'true',
    'exclude_replies' => 'false',
    'trim_user' => 'true',
    'count' => 100
  );
  
  $max_id = 0;
  while (true) {

    if ($max_id != 0) {
      $max_id--;
      $params['max_id'] = $max_id;
    } 

    $conn->request( 'GET', $conn->url('1.1/statuses/user_timeline'), $params);

    // no more tweets returned for this account
    if ($conn->response['response'] == '[]') {
      break;
    }

    // api reqest failed
    if ($conn->response['code'] != 200) {
      break;
    }
  
    $results = json_decode($conn->response['response']);

    foreach ($results as $tweet) {
      $tweet_id = $tweet->id;
      $max_id = $tweet_id;

      // api sometimes returns duplicate tweets
      // ignore this one if it's already in the db
      if (DB::instance()->in_table('tc_tweet', 'tweet_id = ' . $tweet_id)) {
        continue;
      }

      // prep data for inserting into db
      $tweet_text = $tweet->text;
      $created_at = DB::instance()->date($tweet->created_at);
      $user_id = $tweet->user->id;
      $retweet_count = $tweet->retweet_count;
      $favorite_count = $tweet->favorite_count;
    
      if (isset($tweet->retweeted_status)) {
      
        // this is a retweet, so get the text and entities from the orig. tweet
        $is_rt = 1;
        $tweet_text = $tweet->retweeted_status->text;
        $retweet_count = 0;
        $retweet_user_id = $tweet->retweeted_status->user->id;
        $entities = $tweet->retweeted_status->entities;
      }

      // record the tweet
      // todo

      // record retweets
      // todo

      // extract the hashtags from the entities object and record them
      // todo

      // extract the @mentions from the entities object and record them
      // todo

      // extract the urls from the entities object adn record them
      // todo
    }
  }

  // record the fact that the old tweets for this leader account 
  // have been collected
  // todo

}

