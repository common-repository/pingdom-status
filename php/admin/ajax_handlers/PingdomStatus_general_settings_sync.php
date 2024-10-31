<?php
if (!function_exists('add_action'))
{
	require_once("../../../../../../wp-config.php");
}

require_once (dirname(__FILE__) . '/../../pingdom_sync/PingdomDataProvider.php');

PingdomDataProvider::performSensorsSync(true);
PingdomDataProvider::performStateSync(true);
PingdomDataProvider::performIpResolving(true);
