<?php
	/*
		Pingdom Status Report

		Prints the page with detailed
		information of a given service and month.

		This page will include the following:
		uptime_chart.php
		response_chart.php
		history.php
	*/

        // Use "do {} while (0)" so that we can break out of parsing this file easily.
        // Clever idea from http://stackoverflow.com/questions/1673131/how-to-stop-further-php-parsing-but-continue-page-load/1673333#1673333
        do
        {

	global $pingdom_PingdomStatus;
	global $wp_query, $wp_locale;
	wp_enqueue_script('jquery');
	$pingdom_PingdomStatus->pre_get_posts();
	$sensorInfo = $pingdom_PingdomStatus->get_sensor_info();
        if($sensorInfo->sensorId == -1 || !$sensorInfo->is_public)
        {
            // If the sensor isn't public, show an error message
            echo __('Sensor not found');

            header("HTTP/1.1 404 Not Found");
            break;
        }

	$sensorOverview = $pingdom_PingdomStatus->list_sensors();

	$monthOfYear = (isset($wp_query->query_vars["statusmonth"])) ? $wp_query->query_vars["statusmonth"] : $wp_query->query_vars["m"];
	$year = substr($monthOfYear, 0, 4);
	$month = substr($monthOfYear, 4, 2);
	$monthOfYear = date("F Y", mktime(0, 0, 0, $month, 1, $year));
	$overview_link = get_permalink($wp_query->post->ID);
	$image_path = get_bloginfo('wpurl') . "/wp-content/plugins/pingdom-status/images";
?>

<div id="sensor_nav">
	&laquo; <a href="<?php echo $overview_link ?>"><?php _e("Go back to all services", "PingdomStatus"); ?></a>
	<?php include(dirname(__FILE__) . "/PingdomStatus_month_selector.php"); ?>
	<br class="clear"/>
</div>

<div id="statusInfo">
	<h3>
		<?php if($sensorOverview[0]->isUp) :?>
			<img src="<?php echo PINGDOM_PLUGIN_URL; ?>/images/up.png" alt="UP" title="UP" width="16" height="16" />
		<?php else : ?>
			<img src="<?php echo PINGDOM_PLUGIN_URL; ?>/images/down.png" alt="DOWN" title="DOWN" width="16" height="16" />
		<?php endif;?>
		<?php echo $sensorOverview[0]->name; ?>
	</h3>

	<div class="statusInfo_details">
		<dl>
			<dt><?php _e("Uptime", "PingdomStatus"); ?>:</dt>
			<dd><?php echo $sensorOverview[0]->getUptime(); ?>%</dd>
		</dl>
	</div>
	<div class="statusInfo_details details_mid">
		<dl>
			<dt><?php _e("Downtime", "PingdomStatus"); ?>:</dt>
			<dd><?php echo $sensorOverview[0]->getDowntime(); ?></dd>
		</dl>
	</div>
	<div class="statusInfo_details">
		<dl>
			<dt><?php _e("Avg. response time", "PingdomStatus"); ?></dt>
			<dd><?php echo $sensorOverview[0]->getAverageResponseTime(); ?></dd>
		</dl>
	</div>
	<div class="clear"></div>
</div>

<div id="statistics">
	<h4>Uptime, <?php echo $monthOfYear; ?> </h4>
	<?php include(dirname(__FILE__) . "/uptime_chart.php"); ?>
	<h4>Average response time, <?php echo $monthOfYear; ?> </h4>
	<?php include(dirname(__FILE__) . "/response_chart.php"); ?>
	<h4>Outages, <?php echo $monthOfYear; ?> </h4>
	<?php include(dirname(__FILE__) . "/outages.php"); ?>
</div>

<?php

        }
        while(0);

?>
