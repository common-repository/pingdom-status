<?php
if (!function_exists('add_action'))
{
	require_once("../../../../../../wp-config.php");
}
require_once('../../../PingdomStatus_constants.php');
require_once('../../PingdomStatus_DB.php');

if (isset($pingdom_PingdomStatus) &&
	isset($_POST["id"])) {
		
	$id = $_POST["id"];

	$conn = PingdomStatus_DB::getConnection();
	$allGroups = $conn->query("FROM PingdomPsGroup g LEFT JOIN g.SensorGroups sg LEFT JOIN sg.Sensor s WHERE g.id=$id");
		
	if(count($allGroups) > 0){
		// Group can not be deleted if it contains sensors
		if(count($allGroups[0]->SensorGroups) == 0){
			$allGroups[0]->delete();
			echo $id;
		}
		else{
			echo PINGDOM_AJAX_ERROR;
		}
	}
	else {
		echo PINGDOM_AJAX_ERROR;
	}
}

?>