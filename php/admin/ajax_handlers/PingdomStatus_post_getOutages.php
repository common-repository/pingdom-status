<?php
if (!function_exists('add_action'))
{
	require_once("../../../../../../wp-config.php");
}
require_once('../../../PingdomStatus_constants.php');
require_once('../../PingdomStatus_DB.php');
require_once('../../PingdomStatus_Date.php');

if (isset($pingdom_PingdomStatus) &&
	isset($_POST["year"]) &&
	isset($_POST["month"]) &&
	isset($_POST["day"])) {
		
	$year = $_POST["year"];
	$month = $_POST["month"] + 1;
	$day = $_POST["day"];

	$conn = PingdomStatus_DB::getConnection();
	$statesObj = $conn->getTable('PingdomPsState');
	
	// Get downtime tolerance setting
	$settingsTable = $conn->getTable('PingdomPsSettings');
	$settingsArray = $settingsTable->findAll();
	$settings = $settingsArray[0];
	$toleranceSeconds = 60 * $settings->minimum_downtime_period;
	
	// Format date for query 
	$startDate = "$year-$month-$day 00:00:00";
	$endDate = "$year-$month-$day 23:59:59";

	// Get all down states, that were at specified date
	$states = $statesObj->findByDql("state_type_id = " . SENSOR_STATE_DOWN_ID . " AND time_from > '" . $startDate . "' AND time_from < '" . $endDate . "'");

	$toReturn = array();
	foreach($states as $state){
		$span = strtotime($state->time_to) - strtotime($state->time_from);

		if($span >= $toleranceSeconds){
			$obj = null;
			$obj->id = $state->id;
			$obj->text = $state->Sensor->name . ": " . $state->time_from . ", " . PingdomStatus_Date::getTimeSpanString((int)$span);
			array_push($toReturn, $obj);
		}
	}

	echo json_encode($toReturn);
}
?>