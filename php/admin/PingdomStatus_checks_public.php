<?php
	require_once(PINGDOM_PLUGIN_PATH . "/php/PingdomStatus_DB.php");
	require_once(PINGDOM_PLUGIN_PATH . "/php/pingdom_sync/PingdomDataProvider.php");
	
	// Get existing sensors 
	$conn = PingdomStatus_DB::getConnection();
	$sensorObj = $conn->getTable('PingdomPsSensor');
	
	// Get all public sensors	
	$sensors = $conn->query("FROM PingdomPsSensor s LEFT JOIN s.SensorGroups sg LEFT JOIN sg.Group g WHERE s.is_public = 1");
	
	// Get groups
	$sensorGroups = $conn->getTable('PingdomPsGroup')->findAll();
	
	// Cache sensor types
	$sensorTypes = $conn->getTable('PingdomPsType')->findAll();
	$sensorTypesById = array();
	foreach ($sensorTypes as $sensorType){
		$sensorTypesById[$sensorType->id] = $sensorType->value;
	}
?>
<script type="text/javascript">
	var ajax_editurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_checks_public_change_group.php";
	var ajax_makenonpublicurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_checks_public_delete.php";
</script>


<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/common/deleterow.js"></script>

<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/admin/PingdomStatus_checks_public.js"></script>


<!-- Server variables -->
<input type="hidden" id="sensorGroups" value='<?php echo $sensorGroupsAsJSON; ?>'/>

<div class="wrap">
<h2>Public Checks</h2>
<div id="progress" class="updated" style="display: none;"><p></p></div>
<div style="width: 100%">
<table id="checks_list" class="widefat">
	<thead>
		<tr>
			<th scope="col">ID</th>
			<th scope="col">Name</th>
			<th scope="col">Type</th>
			<th scope="col">Group</th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($sensors as $sensor){
				$options = "";
				foreach ($sensorGroups as $group){
					$options .= ($group->name == $sensor->SensorGroups[0]->Group->name) ?  "<option value='$group->id' selected='selected'>$group->name</option>" : "<option value='$group->id'>$group->name</option>";
				}
				$sensorTypeId = $sensor->type_id;
				$groupString = $sensor->SensorGroups[0]->Group->name;
				
				// Sensors that are deleted from pingdom are marked different
				if('NO' == $sensor->deleted_in_pingdom){
					echo "
					<tr id='$sensor->id'>
						<th scope='row'>$sensor->id</th>
						<td>$sensor->name</td>
						<td>$sensorTypesById[$sensorTypeId]</td>
						<td>
							<p class='PsAdminEdit'>$groupString <a href='#edit'>Edit</a></p>
							<div class='PsAdminEditBox'><select id='select$sensor->id'>$options</select> <input type='button' value='OK' class='PsAdminSave'/></div>
						</td> 
						<td id='$sensor->id' class='unpublic_button'><a href='#'' rel='permalink'' class='edit'>Make non public </a></td>
					</tr>";
				}
				else{
					echo "
					<tr id='$sensor->id' class='active'>
						<th scope='row' >$sensor->id</th>
						<td>$sensor->name</td>
						<td>$sensorTypesById[$sensorTypeId]</td>
						<td>
							$groupString <a class='PsAdminEdit' href='#edit'>Edit</a><br />
							<div class='PsAdminEditBox'><select id='select$sensor->id'>$options</select> <input type='button' value='OK' class='PsAdminSave'/></div>
						</td> 
			
						<td id='$sensor->id' class='delete_button'><a href='#'' rel='permalink'' class='edit'>Delete sensor </a></td>
					</tr>";
				}
			}
		?>
	</tbody>
</table>
</div>
</div>

