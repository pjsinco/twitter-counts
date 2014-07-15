#!/usr/bin/env php

<?php

require_once('config/config.php');

$db = new PDO('mysql:host=' . DB_HOST . ';db=' .  DB_NAME . 
  ';port=' . DB_PORT, DB_USER, DB_PASS);
