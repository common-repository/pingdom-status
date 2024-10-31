<?php
if (!function_exists('add_action'))
{
	require_once("../../../../../../wp-config.php");
}
require_once('../../../PingdomStatus_constants.php');
require_once('../../PingdomStatus_DB.php');
 
if (isset($pingdom_PingdomStatus) &&
	isset($_POST["username"]) &&
	isset($_POST["password"]) &&
	isset($_POST["api_key"]) &&
	isset($_POST["threshold"])) {
		
	$new_username = $_POST["username"];
	$new_password = $_POST["password"];
	$new_pingdom_api_key = $_POST["api_key"];
	$new_downtime_threshold = $_POST["threshold"];
	
	// Get existing settings
	$conn = PingdomStatus_DB::getConnection();
	$settingsTable = $conn->getTable('PingdomPsSettings');
	$settingsArray = $settingsTable->findAll();
	$settings = $settingsArray[0];
	
	$settings->username = $new_username;
	$settings->password = $new_password;
	$settings->pingdom_api_key = $new_pingdom_api_key;
	$settings->minimum_downtime_period = $new_downtime_threshold;
	$settings->save();
	
	echo PINGDOM_AJAX_OK;
}
?>