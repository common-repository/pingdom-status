
<?php
/**
 * Periodically updates states of the sensors.
 */
require_once (dirname(__FILE__) . '/../../../../../wp-config.php');
require_once (dirname(__FILE__) . '/PingdomDataProvider.php');

PingdomDataProvider::performSensorsSync();
PingdomDataProvider::performStateSync();
PingdomDataProvider::performIpResolving();

?>
