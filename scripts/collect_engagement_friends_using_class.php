<?php

// set up error reporting for debugging
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(-1);

require_once '../libraries/TC.php';

TC::instance()->collect_engagement(TC::FRIENDS);
