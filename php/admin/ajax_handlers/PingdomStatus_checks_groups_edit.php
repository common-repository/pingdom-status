<?php
if (!function_exists('add_action'))
{
	require_once("../../../../../../wp-config.php");
}
require_once('../../../PingdomStatus_constants.php');
require_once('../../PingdomStatus_DB.php');

if (isset($pingdom_PingdomStatus) &&
	isset($_POST["id"]) &&
	isset($_POST["value"])) {
		
	$id_to_edit = $_POST["id"];
	$value = $_POST["value"];
	if($id_to_edit != 1){
		$conn = PingdomStatus_DB::getConnection();
		$group = $conn->getTable('PingdomPsGroup')->find((int)$id_to_edit);
		if($group != null){
			$group->name = $value;
			$group->save();
			
			echo $value;
		}
		else {
			echo PINGDOM_AJAX_ERROR;
		}
	}
	else{
		echo "Ungrouped";
	}

}

?>

