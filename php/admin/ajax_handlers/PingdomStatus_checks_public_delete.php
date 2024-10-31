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
	$sensor = $conn->getTable('PingdomPsSensor')->find((int)$id);
	$sensor->is_public = 0;
	$sensor->save();
	
	$sensorGroupTable = $conn->getTable('PingdomPsSensorGroup');
	$sensorGroups = $sensorGroupTable->findByDql("sensor_id = " . $id);
	foreach ($sensorGroups as $sensorGroup){
		$sensorGroup->delete();
	}
	
	echo PINGDOM_AJAX_OK;
}

?>