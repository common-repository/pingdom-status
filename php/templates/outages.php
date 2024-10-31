<?php
	/*
		Pingdom Status Outages

		On sensor page, prints a list
		of "Outages" (periods of downtime)
		during that current month.
	*/
	global $pingdom_PingdomStatus;
	$sensorName = $pingdom_PingdomStatus->get_sensor_info()->name;
	$history = $pingdom_PingdomStatus->get_status_history();
?>
<?php if(count($history) > 0) : ?>
<table class="status" cellpadding="0" cellspacing="0">
	<tr>

		<th><?php _e("From", "PingdomStatus"); ?></th>
		<th><?php _e("To", "PingdomStatus"); ?></th>
		<th><?php _e("Downtime", "PingdomStatus"); ?></th>
	</tr>
<?php
foreach($history as $row) :
	$row->timeSpan = PingdomStatus_Date::getTimeSpanString($row->spanDowntime);
?>

	<tr>
		<td><?php echo $row->dateFrom; ?></td>
		<td><?php echo $row->dateTo; ?></td>
		<td><?php echo $row->timeSpan; ?></td>
	</tr>

<?php endforeach; ?>
</table>
<?php else : ?>
<p>No downtime during this period.</p>
<?php endif; ?>
