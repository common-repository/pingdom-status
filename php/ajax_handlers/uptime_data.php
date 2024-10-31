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
	$endDate = $year . "-" . $month . "-" . $endDay . " 11:59:59";
	
	
	// Get downtime data	
	$downtimeSecondsByDate = PingdomStatus_Functions::getAggregatedUptimeDataForSensor($sensorId, $startDate, $endDate);
	$toReturn = array();
	foreach($downtimeSecondsByDate as $key=>$value){
		$monitoredTime = $value->uptime + $value->downtime;
		$uptimePercentage = $monitoredTime == 0 ? -1 : $value->uptime / $monitoredTime;
		
		if($uptimePercentage != -1){
			$uptimePercentage = round(100 * $uptimePercentage, 2);
		}
				
		$keySplit = preg_split("/\-/", $key);
		$time = mktime(0, 0, 0, (int)$keySplit[1], (int)$keySplit[2],(int)$keySplit[0]) * 1000;
		$toReturn[] = array($time, (float)$uptimePercentage);
	}
	echo json_encode($toReturn);
}

?>
