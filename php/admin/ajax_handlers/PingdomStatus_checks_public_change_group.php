<?php
if (!function_exists('add_action'))
{
	require_once("../../../../../../wp-config.php");
}
require_once('../../../PingdomStatus_constants.php');
require_once('../../PingdomStatus_DB.php');

if (isset($pingdom_PingdomStatus) &&
	isset($_POST["id"]) &&
	isset($_POST["selectId"])) {
		
	$check_id = $_POST["id"];
	$group_id = $_POST["selectId"];

	$conn = PingdomStatus_DB::getConnection();

	// Get group name to return
	$group = $conn->getTable('PingdomPsGroup')->find((int)$group_id);

	if($group == null){
		echo PINGDOM_AJAX_ERROR;
	}
	else{
		// Delete all group associations for this sensor
		$sensorGroupTable = $conn->getTable('PingdomPsSensorGroup');
		$sensorGroups = $sensorGroupTable->findByDql("sensor_id = " . $check_id);
		foreach ($sensorGroups as $sensorGroup){
			$sensorGroup->delete();
		}

		// Add new connection
		$sensorGroup = new PingdomPsSensorGroup();
		$sensorGroup->sensor_id = $check_id;
		$sensorGroup->group_id = $group_id;
		$sensorGroup->save();
		echo $group->name;
	}
}

?>