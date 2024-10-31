<?php
	global $pingdom_PingdomStatus;
	global $wp_query, $wp_locale;
	$sensorInfo = $pingdom_PingdomStatus->get_sensor_info();
?>
<form id="operation_status_month_selector" method="get" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <input type="hidden" name="page_id" value="<?php echo $wp_query->query_vars["page_id"]; ?>" />
    <input type="hidden" name="sensorId" value="<?php echo $sensorInfo->sensorId; ?>" />
    <label id="month_selector" for="activemonth"><?php _e("Active month", "PingdomStatus"); ?>:</label>
    <select name="statusmonth" id="activemonth" onChange="javascript:document.getElementById('operation_status_month_selector').submit();">
    <?php

    	$months_for_sensor = PingdomStatus_Functions::getMonthsForSensor($sensorInfo->sensorId);
    	if ($months_for_sensor && count($months_for_sensor) > 0) {
    		foreach ($months_for_sensor as $monthrow) {
    			$monthlink = sprintf("%04d%02d", $monthrow["year"], $monthrow["month"]);
    			$selected = $monthlink == $wp_query->query_vars["statusmonth"] ? ' selected="selected"' : "";
    			$description = sprintf("%s %s", $monthrow["year"], ucfirst($wp_locale->get_month($monthrow["month"])));
    			echo "<option value='$monthlink'$selected>$description</option>\n";
    		}
    	}
    ?>
    </select>
</form>