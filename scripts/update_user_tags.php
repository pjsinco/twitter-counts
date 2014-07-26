<?php

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../config/config.php';
require_once '../inc/oauth_lib.php';
require_once '../libraries/DB.php';
include '../vendor/krumo/class.krumo.php';

// this is all done within the db, so there are no rate limits
$q = "
  SELECT user_id, description
  FROM tc_user
";

$q_results = DB::instance()->select_rows($q);

foreach ($q_results as $row) {

  $user_id = $row['user_id'];
  $description = $row['description'];

  // delete the old description tags for this user
  DB::instance()->delete_row('tc_user_tag', 'user_id = ' . $user_id);

  // parse out any tags in latest desc
  preg_match_all(
    "/\B(?<![=\/])#([\w]+[a-z]+([0-9]+)?)/i",
    $description,
    $tags
  );

  // add the current tags into the database
  foreach ($tags[1] as $tag) {
    DB::instance()->insert(
      'tc_user_tag',
      array(
        'user_id' => $user_id,
        'tag' => $tag,
      ) 
    );
  }
} // end foreach
