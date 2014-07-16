<?php 

require_once('../vendor/simpletest/autorun.php');
require_once('../inc/DB.php');


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

  function test_select_array() {
    $results = DB::instance()->select_array('select * from tc_user');
    $this->assertIsA($results, 'array');
    //$this->expectError($this->db->select('select * from tcuser'));
  }

  function test_insert() {
    $data = array(
      'tc_user_id' => '111',
      'screen_name' => 'Tester'
    );
    $rows = DB::instance()->insert('tc_user', $data);
    $this->assertTrue($rows);
  }
}

