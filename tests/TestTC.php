<?php 

require_once('../vendor/simpletest/autorun.php');
require_once('../libraries/DB.php');
require_once('../libraries/TC.php');
require_once('../vendor/krumo/class.krumo.php');


class TestTC extends UnitTestCase
{

  function test_get_users() {

    $users = TC::instance()->users;
    $this->assertNotNull($users);
    $this->assertIsA($users, 'array');
    
  }

  function test_collect_engagement_with_friend() {
    $this->assertTrue(
      TC::instance()->collect_engagement(TC::FRIENDS) 
    );
  }

  function test_collect_engagement_with_follower() {
    $this->assertTrue(
      TC::instance()->collect_engagement(TC::FOLLOWERS) 
    );
  }
} // eoc
