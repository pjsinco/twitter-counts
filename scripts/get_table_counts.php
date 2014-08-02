<?php 

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

/**
  Make sure our scripts are collecting data
  by tracking how big our tables are every day
 */

// get an array of all our tables
$q = "
  show tables;
";
$tables = DB::instance()->select_rows($q);

$counts = array();
$counts['count_date'] = date('Y-m-d');


// loop through our tables and add the row count of each to $counts
foreach ($tables as $table_array) {

  $table = $table_array['Tables_in_rempatri_twitter_counts'];

  // don't bother with 
  if ($table == 'tc_collection_tracker') {
    continue;
  }

  $q = "select count(*) as count from " .  $table;

  $results = DB::instance()->select_rows($q);
  $counts[$table] = $results[0]['count'];
}

// insert our counts into the db
$results = (DB::instance()->insert('tc_collection_tracker', $counts));

// report results
echo '<pre>'; 
echo 'Data successfully inserted? ' . (($results) ? 'Yes' : 'No') . 
  PHP_EOL;
var_dump($counts);
echo '</pre>'; // debug

