<?php
if (!function_exists('add_action'))
{
	require_once("../../../../../../wp-config.php");
}
require_once('../../PingdomStatus_DB.php');
 
if (isset($pingdom_PingdomStatus) &&
	isset($_POST["group_name"])) {
		
	$new_name = $_POST["group_name"];
	
	$conn = PingdomStatus_DB::getConnection();
	$group = new PingdomPsGroup();
	$group->name = $new_name;
	$group->save();
	
	// Object to return
	$toReturn = null;
	$toReturn->newName = $new_name;
	$toReturn->id = $group->id;
	
	echo json_encode($toReturn);
}

?>