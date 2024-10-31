<?php
	/*	
		Pingdom Status Overview
		
		Prints a list with all public sensors grouped
		together and some quick info about 
		their current status.
	*/
	global $pingdom_PingdomStatus; 
	$sensorGroups = $pingdom_PingdomStatus->current_status_long_all();
?>
<?php if(count($sensorGroups) > 0) :?>
	<?php foreach($sensorGroups as $sensorGroup) : ?>
	<?php if($sensorGroup->id != 1) : ?>
	<h3><?php echo $sensorGroup->name; ?></h3>
	<?php endif; ?>
	
	
	<table class="status" cellpadding="0" cellspacing="0">
		<tr>
			<th><?php _e("Service", "PingdomStatus"); ?></th>
			<th><?php _e("Response time", "PingdomStatus"); ?></th>
			<th><?php _e("Status", "PingdomStatus"); ?></th>
		</tr>
	
		<?php foreach($sensorGroup->sensors as $sensor) : ?>
		<tr>
			<td><a href="<?php echo add_query_arg("sensorId", $sensor->id);?>"><?php echo $sensor->name; ?></a></td>
			<td><?php echo $sensor->getAverageResponseTime()?></td>
			<td>
				<?php if($sensor->isUp) :?>
					<img src="<?php echo PINGDOM_PLUGIN_URL; ?>/images/up.png" alt="UP" title="UP" width="16" height="16" />
				<?php else : ?>
					<img src="<?php echo PINGDOM_PLUGIN_URL; ?>/images/down.png" alt="DOWN" title="DOWN" width="16" height="16" />
				<?php endif;?>
			</td>
		</tr>
		<?php endforeach;?>
	</table>
	
	
	<?php endforeach;?>
<?php endif;?>