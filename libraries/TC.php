<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

class TC
{

  const FRIENDS = 'friend';
  const FOLLOWERS = 'follower';
  private $tc;
  private $conn;
  public $users;
  private static $instance;

  public function __construct() {

    // get engagement acct users
    $q = "
      SELECT user_id, screen_name
      FROM tc_engagement_account
    ";
    $this->users = DB::instance()->select_rows($q);

    // set up api connection
    $this->conn = get_connection();
  }

  // singleton pattern
  // use existing instance if one is available
  public static function instance() {
  
    if (!isset(self::$instance)) {
      self::$instance = new TC();
    }
    return self::$instance;
  }

  // magic method
  // ex: TC::instance()->users
  public function __get($field) {
    if (isset($this->field)) {
      return $this->field;
    }
    return null;
  }

  // collect engagement followers, friends
  // @param $type
  //    TC::FRIENDS, TC::FOLLOWERS
  public function collect_engagement($type) {

    if ($type != self::FRIENDS && $type != self::FOLLOWERS) {
      return false;
    }

    $table = 'tc_' . $type;
    $field = $type . '_of';
    $event = $type;


    foreach ($this->users as $user) {
    
      $user_id = $user['user_id'];
    
      $q = "
        SELECT count(*) as count
        FROM $table
        where $field = $user_id;
      ";
    
      $q_results = DB::instance()->select_rows($q);

      if (!$q_results[0]['count']) {
        $first_collection = true;
      } else {
        // if friends/followers have been collected, 
        // set `current`=0 for all rows
        $first_collection =  false;
        $q_update = DB::instance()->update_row(
          $table , array('current' => 0)
        );
      }
      
      // loop through all friends/followers
      // start with a cursor value of -1, because this is our 
      // 1st collection request (loc6933)
      $cursor = -1;
      
      // $cursor will be 0 when there are no more friends/followers 
      // to request
      // https://dev.twitter.com/docs/api/1.1/get/followers/ids
      // https://dev.twitter.com/docs/api/1.1/get/friends/ids
      while ($cursor != 0) { 
      
        $this->conn->request(
          'GET',
          $this->conn->url('1.1/' . $type . 's/ids'),
          array(
            'user_id' => $user_id,
            'cursor' => $cursor
          )
        );

        $response_code = $this->conn->response['code'];
        if ($response_code == 200) {
    
          $data = json_decode($this->conn->response['response'], false);
      
          // get list of IDs
          $ids = $data->ids;
    
          // get the cursor value for the next request
          $cursor = $data->next_cursor_str;
    
          // step through each friend/follower
          if (count($ids)) {
            foreach ($ids as $other_user_id) {
    
              // if this friend/follower is already in table, 
              // update `current` = 1
              if (DB::instance()->
                in_table($table, "user_id = $other_user_id")) {
                
                DB::instance()->update_row(
                  $table,
                  array('current' => 1),
                  "where user_id = $other_user_id"
                );
      
              } else {
                // this is a new friend/follower, 
                // so insert with `current` = 1
                DB::instance()->insert(
                  $table,
                  array(
                    'user_id' => $other_user_id,
                    'current' => 1,
                    $field => $user_id
                  )
                );
    
                // if this is not the first time friends/followers 
                // have been collected, record this new f/f event 
                // in the follow_log table 
                if ($first_collection) {
                  DB::instance()->insert(
                    'tc_follow_log',
                    array(
                      'user_id' => $other_user_id,
                      'event' => $event,
                      'event_for' => $user_id
                    )
                  );
                } 
              } //endelse
            } // endforeach
          } else {
            break; // stop collecting if no more f/f are found
          }
        } else {
          // an error occurred, so try again later
          echo 'Error' . PHP_EOL;
          exit;
        }
      } // endwhile
    
      // find any f/f who were collected in the past but not this time
      $q = "
        SELECT user_id
        FROM $table
        WHERE current = 0
          and $field = " . $user_id;
    
      $results = DB::instance()->select_rows($q);
    
      foreach ($results as $row) {
        
        // record the event; this means the engagement account
        // was unfollowed/unfriended by this user
        DB::instance()->insert(
          'tc_follow_log',
          array(
            'event_for' => $user_id,
            'event' => 'un' . $event,
            'user_id' => $other_user_id
          )
        );
    
        // delete any f/f who are not found during collection
        DB::instance()->delete_row(
          $table,
          'where user_id = ' . $other_user_id
        );
      }
    } // end foreach ($q_results as $user)


    return true; 
  } // end collect_engagement

} // eoc
