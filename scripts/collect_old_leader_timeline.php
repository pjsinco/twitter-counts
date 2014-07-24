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
  FROM tc_leader
  WHERE old_timeline_collected = '0000-00-00'
";

$results = DB::instance()->select_rows($q);

if (!$results) {
  echo 'All the older leader timelines have already been collected.';
  exit;
}


// prep the api request
$user_id = $results[0]['user_id'];

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

  $conn->request( 'GET', $conn->url('1.1/statuses/user_timeline'), 
    $params);

  // no more tweets returned for this account
  if ($conn->response['response'] == '[]') {
    break;
  }

  // api reqest failed
  if ($conn->response['code'] != 200) {
    break;
  }

  $results = json_decode($conn->response['response']);

  // gather tweet info
  foreach ($results as $tweet) {

    $tweet_id = $tweet->id;
    $max_id = $tweet_id;

    // api sometimes returns duplicate tweets
    // ignore this one if it's already in the db
    if (DB::instance()->
      in_table('tc_tweet', 'tweet_id = ' .  $tweet_id)) {
      continue;
    }

    // prep data for inserting into db
    $tweet_text = $tweet->text;
    $created_at = DB::instance()->date($tweet->created_at);
    $user_id = $tweet->user->id;
    $retweet_count = $tweet->retweet_count;
    $favorite_count = $tweet->favorite_count;

    if (isset($tweet->retweeted_status)) {
    
      // this is a retweet, so get the text and entities 
      // from the orig. tweet
      $is_rt = 1;
      $tweet_text = $tweet->retweeted_status->text;
      $retweet_count = 0;
      $retweet_user_id = $tweet->retweeted_status->user->id;
      $entities = $tweet->retweeted_status->entities;
    } else {
      $is_rt = 0;
      $entities = $tweet->entities;
    }

    // record the tweet
    DB::instance()->insert(
      'tc_tweet', 
      array(
        'tweet_id' => $tweet_id,
        'tweet_text' => $tweet_text,
        'created_at' => $created_at,
        'user_id' => $user_id,
        'is_rt' => $is_rt,
        'retweet_count' => $retweet_count,
        'favorite_count' => $favorite_count
      )
    );

    // record any retweets
    if ($is_rt) {

      DB::instance()->insert(
        'tc_tweet_retweet',
        array(
          'tweet_id' => $tweet_id,
          'created_at' => $created_at,
          'source_user_id' => $user_id,
          'target_user_id' => $retweet_user_id
        )
      );

    }

    // extract the hashtags from the entities object and record them
    if ($entities->hashtags) {
      foreach ($entities->hashtags as $hashtag) {
        $tag = $hashtag->text;
        DB::instance()->insert(
          'tc_tweet_tag',
          array(
            'tweet_id' => $tweet_id,
            'user_id' => $user_id,
            'tag' => $tag,
            'created_at' => $created_at
          )
        );
      }
    }

    // extract the @mentions from the entities object 
    // and record them
    if ($entities->user_mentions) {
      foreach ($entities->user_mentions as $user_mention) {
        $target_user_id = $user_mention->id;
        DB::instance()->insert(
          'tc_tweet_mention',
          array(
            'tweet_id' => $tweet_id,
            'created_at' => $created_at,
            'source_user_id' => $user_id,
            'target_user_id' => $target_user_id
          )
        );
      }
    }

    // extract the urls from the entities object adn record them
    if ($entities->urls) {
      foreach ($entities->urls as $url) {
        $url = $url->expanded_url;
        DB::instance()->insert(
          'tc_tweet_url',
          array(
            'tweet_id' => $tweet_id,
            'user_id' => $user_id,
            'url' => $url,
            'created_at' => $created_at 
          )
        );
      }
    }
  }
} // end while

// record the fact that the old tweets for this leader account 
// have been collected
DB::instance()->update_row(
  'tc_leader',
  array(
    'old_timeline_collected' => 'NOW()',
    'user_id' => $user_id
  ),
  "WHERE user_id = $user_id",
  'old_timeline_collected'
);


