<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

// get all engagement accts that have had 
// their old search results collected
$q = "
  SELECT user_id, screen_name, search_since_id
  FROM tc_engagement_account
  WHERE old_search_collected != '0000-00-00'
";

$results = DB::instance()->select_rows($q);

// iterate over each account in result set
foreach ($results as $user) {
  $engagement_user_id = $user['user_id'];
  $screen_name = $user['screen_name'];
  $search_since_id = $user['search_since_id'];

  file_tweets($engagement_user_id, $screen_name, $search_since_id);

}

function file_tweets($engagement_user_id, $engagement_screen_name, 
  $since_id) {

  // prep for our api call
  $conn = get_connection();
  $params = array(
    'q' => $engagement_screen_name,
    'result_type' => 'recent',
    'lang' => 'en',
    'include_entities' => 'true',
    'count' => 100,
    'since_id' => $since_id
  );

  while (true) {

    $conn->request(
      'GET',
      $conn->url('1.1/search/tweets'),
      $params
    );

    // api reqest failed
    if ($conn->response['code'] != 200) {
      break;
    }
    
    // track the number of tweets returned
    $tweets_found = 0;

    $results = json_decode($conn->response['response']);
    $tweets = $results->statuses;

    foreach ($tweets as $tweet) {
      $tweets_found++;

      $tweet_id = $tweet->id_str;
      $since_id = $tweet->id_str;

      // check to make sure tweet's not already in the db
      if (DB::instance()->
        in_table('tc_tweet', 'tweet_id = ' .  $tweet_id)) {
        continue;
      }
      
      // prep data for inserting into db
      $tweet_text = $tweet->text;
      $tweet_created_at = DB::instance()->date($tweet->created_at);
      $retweet_count = $tweet->retweet_count;
      $user_id = $tweet->user->id_str;
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
          'created_at' => $tweet_created_at,
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
            'created_at' => $tweet_created_at,
            'source_user_id' => $user_id,
            'target_user_id' => $retweet_user_id
          )
        );
      }

      // collect all user account values we want to record
      $name              = $tweet->user->name;
      $screen_name       = $tweet->user->screen_name;
      $profile_image_url = $tweet->user->profile_image_url;
      $location          = $tweet->user->location;
      $description       = $tweet->user->description;
      $url               = $tweet->user->url;
      $user_created_at   = 
        DB::instance()->date($tweet->user->created_at);
      $friends_count     = $tweet->user->friends_count;
      $followers_count   = $tweet->user->followers_count;
      $statuses_count    = $tweet->user->statuses_count;
      $listed_count      = $tweet->user->listed_count;
      $lang              = $tweet->user->lang;

      // we know this user can't be protected because his/her tweet
      // was returned by the search API
      $protected = 0;

      $data = array(
        'user_id' => $user_id,
        'name' => $name,
        'screen_name' => $screen_name,
        'profile_image_url' => $profile_image_url,
        'location' => $location,
        'description' => $description,
        'url' => $url,
        'created_at' => $user_created_at,
        'friends_count' => $friends_count,
        'followers_count' => $followers_count,
        'statuses_count' => $statuses_count,
        'listed_count' => $listed_count,
        'lang' => $lang,
        'protected' => $protected,
        'last_tweet_date' => $tweet_created_at
      );

      // make sure user isn't already in the db ...
      if (!DB::instance()->in_table('tc_user', "user_id = $user_id")) {
        DB::instance()->insert('tc_user', $data);
      } else {
        // otherwise, update the user's info and timestamp last_updated
        DB::instance()->update_row(
          'tc_user',
          $data,
          'where tc_user = ' . $user_id,
          'last_updated'
        );
      }

      // file this tweet's hashtags ...
      if ($entities->hashtags) {
        foreach ($entities->hashtags as $hashtag) {
          $tag = $hashtag->text;
          DB::instance()->insert(
            'tc_tweet_tag',
            array(
              'tweet_id' => $tweet_id,
              'user_id' => $user_id,
              'tag' => $tag,
              'created_at' => $tweet_created_at
            )
          );
        }
      } // end hashtags
      
      // ... and mentions ...
      if ($entities->user_mentions) {
        foreach ($entities->user_mentions as $user_mention) {
          $target_user_id = $user_mention->id_str;
          DB::instance()->insert(
            'tc_tweet_mention',
            array(
              'tweet_id' => $tweet_id,
              'created_at' => $tweet_created_at,
              'source_user_id' => $user_id,
              'target_user_id' => $target_user_id
            )
          );
        }
      } // end mentions

      // ... and urls
      if ($entities->urls) {
        foreach ($entities->urls as $url) {
          $url = $url->expanded_url;
          DB::instance()->insert(
            'tc_tweet_url',
            array(
              'tweet_id' => $tweet_id,
              'user_id' => $user_id,
              'url' => $url,
              'created_at' => $tweet_created_at 
            )
          );
        }
      }
    } // end foreach

    if ($tweets_found == 0) {
      break;
    }
      
  } // endwhile

  // timestamp old_search_collected for this account
  DB::instance()->update_row(
    'tc_engagement_account',
    array(
      'search_since_id' => $since_id
    ),
    'WHERE user_id = ' . $engagement_user_id,
    'new_search_collected'
  );

} // end file_tweets
