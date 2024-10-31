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
	$group_id = isset($_POST["group_id"]) ? $_POST["group_id"] : null;

	$conn = PingdomStatus_DB::getConnection();

	// Delete all group associations for this sensor
	$sensorGroupTable = $conn->getTable('PingdomPsSensorGroup');
	$sensorGroups = $sensorGroupTable->findByDql("sensor_id = " . $id);
	foreach ($sensorGroups as $sensorGroup){
		$sensorGroup->delete();
	}

	// Make sensor public
	$sensor = $conn->getTable('PingdomPsSensor')->find((int)$id);
	$sensor->is_public = true;

	// Add new connection between group and sensor
	if($group_id !== null){
		$sensorGroup = new PingdomPsSensorGroup();
		$sensorGroup->sensor_id = (int)$id;
		$sensorGroup->group_id = (int)$group_id;
		$sensorGroup->save();
		$sensor->save();
	}
	echo $id;
}

?>