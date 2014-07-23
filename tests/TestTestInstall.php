<?php 

require_once '../vendor/simpletest/autorun.php';
require_once '../libraries/DB.php';
require_once '../inc/oauth_lib.php';

class TestTestInstall extends UnitTestCase
{

  public $conn;
  public $users;

  function __construct() {
    $this->conn = get_connection();
    $this->users = unserialize(TWITTER_USERS);
  }

  function test_connection() {
    $this->assertNotNull($this->conn);
  }

  function test_request() {
    $method = 'GET';
    $url = $this->conn->url('1.1/users/show');
    $params = array(
      'user_id' => $this->users[0]['user_id']
    );

    $this->conn->request($method, $url, $params);

    $response_code = $this->conn->response['code'];

    $this->assertEqual($response_code, 200);
  }


}

