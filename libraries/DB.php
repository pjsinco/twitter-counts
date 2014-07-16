<?php 

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

class DB
{

  public $db;
  private static $instance;

  public function __construct() {

    require('../config/config.php');

    // set up PDO object
    try {
      $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' .
        DB_NAME .  ';port=' . DB_PORT, DB_USER, DB_PASS);

      // for debugging
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
      exit;
    }

    date_default_timezone_set('America/New_York');
  }


  // singleton pattern
  // DB::instance(DB_NAME)->query('...');
  public static function instance() {

    // use existing instance
    if (!isset(self::$instance)) {
      
      // create a new instance
      self::$instance = new DB();
    }

    return self::$instance;
  }

  // creates a standard date format for inserting PHP dates into mysql
  public function date($php_date) {
    return date('Y-m-d H:i:s', strtotime($php_date));
  }

  // returns a quoted string safe to pass into a sql query
  public function escape($str) {
    return $this->db->quote($str);
  }

  // tests to see if a field values is already in a table
  public function in_table($table, $where) {
    $q = "
      select *
      from $table
      where $where
    ";

    try {
      $results = $this->db->query($q);
    } catch (PDOExeption $e) {
      $error = $e->getMessage();
    }

    if (!isset($error)) {
      return $results->rowCount() > 0;
    } else {
      return $error;
    }
  }

  // performs a generic select and returns a pointer to the result
  // @returns an array of the result set
//  public function select($query) {
//
//    try {
//      $results = $this->db->query($query);
//
//    } catch (PDOException $e) {
//      $error = $e->getMessage();
//    }
//
//    if (!isset($e)) {
//      return $results->fetchAll();
//    } else {
//      return $e;
//    }
//    
//  }

  // performs a select and returns an array containing all the results
  public function select_rows($query) {

    try {
      $results = $this->db->query($query);
    } catch (PDOException $e) {
      $error = $e->getMessage();
    }

    if (!isset($error)) {
      return $results->fetchAll(PDO::FETCH_ASSOC);
    } else {
      return $error;
    }

  }

  // add a tuple to a relation
  public function insert($table, $data) {
    // set up insert statement

    // make strings of the fields, values
    $data_split = $this->separate_fields_and_values($data, 'string');
    $fields = $data_split['fields'];
    $values = $data_split['values'];

    // execute the insert
    try {
      $stmt = $this->db->prepare("
        INSERT INTO $table ($fields)
        VALUES ($values)
      ");

      $stmt->execute();

    } catch (PDOException $e) {
      $error = $e->getMessage(); // what to do with the message?
    }

    // '00000' == no error
    $err_msg = $stmt->errorCode();

    return $err_msg == '00000' && !isset($error) ? true : false;

  }

  // updates a row
  // DB::instance()->update_row('tc_user', $data, 'WHERE tc_user_id = 111');
  public function update_row($table, $data, $where_condition) {

    // work in progress
    $data_split = $this->separate_fields_and_values($data, 'string');
    $fields = $data_split['fields'];
    $values = $data_split['values'];
    
    $q = "update $table set";

    foreach ($data as $field => $value) {
      if ($value === NULL) {
        $q .= " $field = NULL, ";
      } else {
        $q .= " $field = " . $this->db->quote($value) . ",";
      }
    }
    
    $q = substr($q, 0, -1);

    echo $q;

    //$stmt = $this->db->prepare($q);

    //$num_rows = $this->db->exec($q);

    return $num_rows;
  }
  
  // separates an associative array into 2 arrays,
  // one with the fields, one with the values
  // @param  data
  // @param  return_type
  //    'string' or 'array'
  private function separate_fields_and_values($data, $return_type) {

    if ($return_type == 'string') {
      $fields = '';
      $values = '';
      foreach ($data as $field => $value) {
        $fields .= $field . ',';
        $values .= $this->db->quote($value) . ',';
      }
      $fields = substr($fields, 0, -1);
      $values = substr($values, 0, -1);
    } else {
      $fields = array();
      $values = array();
      foreach ($data as $field => $value) {
        array_push($fields, $field);
        array_push($values, $this->db->quote($value));
      }
    }

    return array(
      'fields' => $fields,
      'values' => $values
    );

  }

} // eoc


