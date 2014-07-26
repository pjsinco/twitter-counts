<?php 

require_once('../vendor/simpletest/autorun.php');
require_once('../libraries/DB.php');
require_once('../vendor/krumo/class.krumo.php');


class TestOfDB extends UnitTestCase
{

  function test_create_random_user() {
    $rand_1 = $this->create_random_user();
    $rand_2 = $this->create_random_user();
    
    $this->assertNotEqual($rand_1, $rand_2);
  }

  function test_escape() {
    $this->assertEqual("'Hooligan'", DB::instance()->escape('Hooligan'));
  } 

  function test_in_table() {
    
    $rand_user = $this->create_random_user();
  
    // insert random user
    $this->assertTrue(
      DB::instance()->insert('tc_user_test', $rand_user)
    );

    $this->assertTrue(DB::instance()->
      in_table('tc_user_test', 
        'screen_name=\'' . $rand_user['screen_name'] . '\''));
    $this->assertTrue(DB::instance()->
      in_table('tc_user_test', 'user_id=' . $rand_user['user_id']));
    $this->assertFalse(DB::instance()->
      in_table('tc_user_test', "screen_name='justinbieber'"));

  }

  function test_select_rows_result_is_array() {

    $results = DB::instance()->select_rows('select * from tc_user_test');
    $this->assertIsA($results, 'array');
    //$this->expectError($this->db->select('select * from tcuser'));

  }

  function test_select_rows_result() {
    $results = DB::instance()->select_rows('select * from tc_user_test');
    $this->assertTrue($results > 0);
  }

  function test_insert() {
    $rand_user = $this->create_random_user();

    $this->assertTrue(DB::instance()->insert('tc_user_test', $rand_user));
    
  }

  function test_update() {
    // 1. insert a new user with a random user_id
    $rand_user = $this->create_random_user();
    $this->assertTrue(DB::instance()->insert('tc_user_test', $rand_user));

    // 2. update that user a new screen name
    $rand_user['screen_name'] = $rand_user['screen_name'] . '_rev';

    $where_condition = 'WHERE user_id = ' . $rand_user['user_id'];

    $rows = DB::instance()->update_row('tc_user_test', $rand_user, 
      $where_condition, 'last_updated');

    // 3. run the test
    $this->assertEqual($rows, 1);
      
    // clean up
    //$this->delete_random_user($random_id + 1);
  }
  

  function test_update_with_null_value() {
    $rand_user = $this->create_random_user();
    $this->assertTrue(DB::instance()->insert('tc_user_test', $rand_user));

    $rand_user['screen_name'] = NULL;

    $this->assertNull($rand_user['screen_name']);

    $where_condition = 'WHERE user_id = ' . $rand_user['user_id'];
    
    $rows = DB::instance()->update_row('tc_user_test', $rand_user,
      $where_condition);
    
    $this->assertEqual($rows, 1);
  
    // delete random user
    //$this->delete_random_user($random_id);
  }

  function test_delete_row() {
    $rand_user = $this->create_random_user();
    $this->assertTrue(DB::instance()->insert('tc_user_test', $rand_user));

    $rows = DB::instance()->select_rows('
      select *
      from tc_user_test
      where user_id = \'' . $rand_user['user_id'] . '\'
    ');
    $this->assertTrue($rows);

    $result = DB::instance()->
      delete_row(
        'tc_user_test', 
        'where user_id = ' . $rand_user['user_id'] 
      );
  }


  // inserts a random user into tc_user_test
  // @return the user_id of the new random user
  private function create_random_user() {

    $random_id = rand(1000, 20000);

    $data = array(
      'user_id' => $random_id,
      'screen_name' => 'rand_' . substr($random_id, 0, 5)
    );

    return $data;
  }

  private function delete_random_user($id) {
    $q = "
      delete from tc_user_test
      where user_id = $id
    ";

    DB::instance()->query($q);
  }
}

