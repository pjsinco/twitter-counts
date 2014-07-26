<?php 

$q = "
  SELECT user_id
  FROM tc_user
  WHERE last_updated < date_sub(now(), interval 24 hour)
  limit 15000
";

require 'collect_account_profiles.php';
collect_account_profiles($q);
