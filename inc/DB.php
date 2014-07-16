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
  public function select_array($query) {

    try {
      $results = $this->db->query($query);
    } catch (PDOException $e) {
      $error = $e->getMessag();
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
    $fields = '';
    $values = '';

    foreach ($data as $field => $value) {
      $fields .= $field . ',';
      $values .= $this->db->quote($value) . ',';
    }

    $fields = substr($fields, 0, -1);
    $values = substr($values, 0, -1);
  
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

  public function update($table, $field_values, $where) {
    $q = "
      update $table 
      set $field_values 
      where $where
    ";
    $num_rows = $this->db->exec($q);

    return $num_rows;
  }
  

} // eoc


