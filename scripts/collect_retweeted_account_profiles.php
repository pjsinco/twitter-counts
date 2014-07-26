<?php

$q = "
  select distinct(target_user_id) as user_id
  from tc_tweet_retweet
  where target_user_id NOT IN (
    select distinct(user_id)
    from tc_user
  )
  limit 15000
";

require 'collect_account_profiles.php';

collect_account_profiles($q);
