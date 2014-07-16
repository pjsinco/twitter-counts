<?php 

require_once('../vendor/simpletest/autorun.php');
require_once('../libraries/DB.php');


class TestOfDB extends UnitTestCase
{
  //public $db; 

  function __construct() {
    //$this->db = new DB();
  }


  function test_escape() {
    $this->assertEqual("'Hooligan'", DB::instance()->escape('Hooligan'));
  } 

  function test_in_table() {

    $this->assertTrue(DB::instance()->in_table('tc_user', "screen_name='TheDOmagazine'"));
    $this->assertTrue(DB::instance()->in_table('tc_user', "twitter_user_id='19262807'"));
    $this->assertFalse(DB::instance()->in_table('tc_user', "screen_name='justinbieber'"));

  }

  function test_select_rows() {
    $results = DB::instance()->select_rows('select * from tc_user');
    $this->assertIsA($results, 'array');
    //$this->expectError($this->db->select('select * from tcuser'));
  }

  function test_insert() {
    $random_id = rand(1000, 20000);
    $data = array(
      'twitter_user_id' => $random_id,
      'screen_name' => 'Tester'
    );
    $rows = DB::instance()->insert('tc_user', $data);
    $this->assertTrue($rows);
  }

  function test_update() {
    $data = array(
      'screen_name' => 'TesterChanged',
      'twitter_user_id' => '65000',
    );

    $where_condition = 'WHERE tc_user_id = 152';
    
  }



}

