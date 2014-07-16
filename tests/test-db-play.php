<?php  

require_once('../libraries/DB.php');

$data = array(
  'screen_name' => 'TesterChanged',
  'twitter_user_id' => '65000',
);

$where_condition = 'WHERE tc_user_id = 152';

DB::instance()->update_row('tc_user', $data, $where_condition);



?>



