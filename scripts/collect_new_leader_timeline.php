<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';


// get all leader accts that have had their old tweets collected
$q = "
  SELECT user_id
  FROM tc_leader
  WHERE old_timeline_collected != '0000-00-00'
";

$results = DB::instance()->select_rows($q);

// iterate over each leader in result set
foreach ($results as $user) {
  $user_id = $user['user_id'];

  file_tweets($user_id);

  // timestamp this leader in db
  DB::instance()->update_row(
    'tc_leader',
    array(),
    'where user_id = ' . $user_id,
    'new_timeline_collected'
  );
}

// get all new tweets for this user
function file_tweets($user_id) {
  $q = "
    select max(tweet_id) as since_id
    from tc_tweet
    where user_id = $user_id
  ";

  $q_results = DB::instance()->select_rows($q);
  $since_id = $q_results[0]['since_id'];

  // prep for our api call
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

  while (true) {

    $conn->request(
      'GET',
      $conn->url('1.1/statuses/user_timeline'),
      $params
    );

    // no more tweets returned for this account
    if ($conn->response['response'] == '[]') {
      return;
    }

    // api reqest failed
    if ($conn->response['code'] != 200) {
      return;
    }

    $results = json_decode($conn->response['response']);
    
    foreach ($results as $tweet) {
      
      $tweet_id = $tweet->id_str;
      $since_id = $tweet_id;

      // api sometimes returns duplicate tweets
      // ignore this one if it's already in the db
      if (DB::instance()->
        in_table('tc_tweet', 'tweet_id = ' .  $tweet_id)) {
        continue;
      }

      // prep data for inserting into db
      $tweet_text = $tweet->text;
      $created_at = DB::instance()->date($tweet->created_at);
      $user_id = $tweet->user->id_str;
      $retweet_count = $tweet->retweet_count;
      $favorite_count = $tweet->favorite_count;

      if (isset($tweet->retweeted_status)) {
        // this is a retweet, so get the text and entities 
        // from the orig. tweet
        $is_rt = 1;
        $tweet_text = $tweet->retweeted_status->text;
        $retweet_count = 0;
        $retweet_user_id = $tweet->retweeted_status->user->id_str;
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
  
      // extract the @mentions from the entities object and record them
      if ($entities->user_mentions) {
        foreach ($entities->user_mentions as $user_mention) {
          $target_user_id = $user_mention->id_str;
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
    } // end foreach
  } // end while
} // end collect_tweets
