<?php  

require_once('../libraries/DB.php');

$data = array(
  'twitter_user_id' => 11540
);

$where_condition = 'WHERE twitter_user_id = 11539';

DB::instance()->update_row('tc_user', $data, $where_condition);



?>



