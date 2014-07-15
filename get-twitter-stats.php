#!/usr/bin/env php

<?php

require_once('config/config.php');

try {
  $db = new PDO('mysql:host=' . DB_HOST . ';db=' .  DB_NAME . 
    ';port=' . DB_PORT, DB_USER, DB_PASS);
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}

// test connection
//echo ($db) ? 'connected' : 'didn\'t connect';


