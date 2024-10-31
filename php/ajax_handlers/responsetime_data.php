<?php

if (!function_exists('add_action'))
{
	require_once("../../../../../wp-config.php");
}

require_once('../../PingdomStatus.php');
 
if (isset($pingdom_PingdomStatus) &&
	isset($_GET["sensor_id"]) &&
	isset($_GET["month"])) {
		
	global $pingdom_PingdomStatus;
	
	$sensorId = $_GET["sensor_id"];
	$month = $_GET["month"];
	
	// Convert month to start and end dates
	$year = substr($month, 0, 4);
	$month = substr($month, 4);
	$endDay = date('t', strtotime($year . '-' . $month . '-01'));
	$startDate = $year . "-" . $month . "-01 00:00:00";
	$endDate = $year . "-" . $month . "-" . $endDay . " 23:59:59";
	
	// Get responsetime data	
	$averageResponsetime = PingdomStatus_Functions::getAggregatedResponseTimeDataForSensor($sensorId, $startDate, $endDate);
	$toReturn = array();
	foreach($averageResponsetime as $key => $value){
		$keySplit = preg_split("/\-/", $key);
		$time = mktime(0, 0, 0, (int)$keySplit[1], (int)$keySplit[2],(int)$keySplit[0]) * 1000;
		$toReturn[] = array($time, (int)$value);
	}
	echo json_encode($toReturn);
}
?>
