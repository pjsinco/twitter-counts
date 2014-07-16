<?php 

require_once('../vendor/simpletest/autorun.php');
require_once('oauth_lib.php');
require_once('../config/config.php');

class TestConnection extends UnitTestCase
{

  public $conn;
  public $users;

  function __construct() {
    $this->conn = get_connection();
    $this->users = unserialize(TWITTER_USERS);
  }

  function test_connection() {

    $this->conn->request(
      'GET',
      $this->conn->url('1.1/users/show'),
      array(
        'user_id' => $this->users[0]['user_id']
      )
    );

    $this->assertNotNull(isset($this->conn));
    $this->assertEqual($this->conn->response['code'], '200');
  }

  function test_unserialization_of_twitter_users() {

    $this->assertTrue(count($this->users) > 0);
    $this->assertTrue($this->users[0]['user_id'] == '19262807');
    
  }


} // eoc

