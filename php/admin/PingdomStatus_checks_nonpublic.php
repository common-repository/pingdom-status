<?php
	require_once(PINGDOM_PLUGIN_PATH . "/php/PingdomStatus_DB.php");
	require_once(PINGDOM_PLUGIN_PATH . "/php/pingdom_sync/PingdomDataProvider.php");
	
	// Get existing sensors
	$conn = PingdomStatus_DB::getConnection();
	$sensorObj = $conn->getTable('PingdomPsSensor');
	
	// Get all non-public sensors
	$sensors = $sensorObj->findByDql("is_public = 0 AND deleted_in_pingdom='NO'");
	
	// Get groups
	$sensorGroups = $conn->getTable('PingdomPsGroup')->findAll();
	
	// Sensor types temp cache
	$sensorTypes = $conn->getTable('PingdomPsType')->findAll();
	$sensorTypesById = array();
	foreach ($sensorTypes as $sensorType){
		$sensorTypesById[$sensorType->id] = $sensorType->value;
	}
?>
<script type="text/javascript">
	var ajax_editurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_checks_nonpublic_change_group.php";
	var ajax_makepublicurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_checks_nonpublic_delete.php";
</script>
<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/common/deleterow.js"></script>
<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/admin/PingdomStatus_checks_nonpublic.js"></script>


<div class="wrap">
<h2>Non-Public Checks</h2>
<div id="progress" class="updated" style="display: none;"><p></p></div>

<div style="width: 100%">
<table id="checks_list" class="widefat">
	<thead>
		<tr>
			<th scope="col">ID</th>
			<th scope="col">Name</th>
			<th scope="col">Target</th>
			<th scope="col">Type</th>
			<th scope="col">Group</th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php
			$options = "";
			foreach ($sensorGroups as $group){
				$options .= "<option value='$group->id'>$group->name</option>";
			}
			foreach($sensors as $sensor){
				$sensorTypeId = $sensor->type_id;
				echo "
				<tr id='$sensor->id'>
					<th scope='row'>$sensor->id</th>
					<td>$sensor->name</td>
					<td>$sensor->target</td>
					<td>$sensorTypesById[$sensorTypeId]</td>
					" . (count($sensorGroups) > 0 ? "<td><select id='select$sensor->id'>$options</select></td>" : "<td></td>") . "
		
					<td id='$sensor->id' class='delete_button'><a href='#'' rel='permalink'' class='edit'>Make public </a></td>
				</tr>";	
			}
		?>
	</tbody>
</table>
</div>
</div>
