<?php
	require_once(ABSPATH . 'wp-content/plugins/PingdomStatus/php/PingdomStatus_DB.php');
	require_once(ABSPATH . 'wp-content/plugins/PingdomStatus/php/pingdom_sync/PingdomDataProvider.php');
	
	// Get existing sensors that are not public
	$conn = PingdomStatus_DB::getConnection();
	$sensorObj = $conn->getTable('PingdomPsSensor');
	
	// Get all public sensors	
	$sensors = $conn->query("FROM PingdomPsSensor s LEFT JOIN s.SensorGroups sg LEFT JOIN sg.Group g WHERE s.is_public = 1");
	
	// Get groups
	$groups = $conn->getTable('PingdomPsGroup')->findAll();
	
	// Cache sensor types
	$sensorTypes = $conn->getTable('PingdomPsType')->findAll();
	$sensorTypesById = array();
	foreach ($sensorTypes as $sensorType){
		$sensorTypesById[$sensorType->id] = $sensorType->value;
	}
	
	// Get groups in a format that is appropriate for filling combo boxes
	$groupsForOptions = Array();
	foreach($groups as $group){
		$groupsForOptions[$group->id] = $group->name;
	}
	
	// Convert sensor groups to json in order to use it for group editing
	$sensorGroupsAsJSON = json_encode($groupsForOptions);
?>
<script type="text/javascript">
	var ajax_editurl =  "<?php bloginfo('wpurl') ?>/wp-content/plugins/PingdomStatus/php/admin/ajax_handlers/PingdomStatus_checks_public_change_group.php";
	var ajax_makenonpublicurl =  "<?php bloginfo('wpurl') ?>/wp-content/plugins/PingdomStatus/php/admin/ajax_handlers/PingdomStatus_checks_public_delete.php";
</script>


<script
	type="text/javascript"
	src="<?php bloginfo('wpurl') ?>/wp-content/plugins/PingdomStatus/js/common/deleterow.js"></script>

<script
	type="text/javascript"
	src="<?php bloginfo('wpurl') ?>/wp-content/plugins/PingdomStatus/js/admin/PingdomStatus_checks_public.js"></script>


<!-- Server variables -->
<input type="hidden" id="sensorGroups" value='<?php echo $sensorGroupsAsJSON; ?>'/>

<div class="wrap">
<h2>Public Checks</h2>
<div style="width: 100%">
<table id="checks_list" class="widefat">
	<thead>
		<tr>
			<th scope="col">ID</th>
			<th scope="col">Name</th>
			<th scope="col">IP</th>
			<th scope="col">Type</th>
			<th scope="col">Group</th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php
			foreach($sensors as $sensor){
				$sensorTypeId = $sensor->type_id;
				$groupString = $sensor->SensorGroups[0]->Group->name;
				
				// Sensors that are deleted from pingdom are marked different
				if('NO' == $sensor->deleted_in_pingdom){
					echo "
					<tr id='$sensor->id'>
						<th scope='row'>$sensor->id</th>
						<td>$sensor->name</td>
						<td>$sensor->ip</td>
						<td>$sensorTypesById[$sensorTypeId]</td>
						" . (count($groups) > 0 ? "<td id='$sensor->id'>$groupString <a class='tEditLink' href='#edit'>edit</a></td>" : "<td></td>") . "
			
						<td id='$sensor->id' class='unpublic_button'><a href='#'' rel='permalink'' class='edit'>Make non public </a></td>
					</tr>";
				}
				else{
					echo "
					<tr id='$sensor->id' class='active'>
						<th scope='row' >$sensor->id</th>
						<td>$sensor->name</td>
						<td>$sensor->ip</td>
						<td>$sensorTypesById[$sensorTypeId]</td>
						<td id='$sensor->id'>$groupString</td>
			
						<td id='$sensor->id' class='delete_button'><a href='#'' rel='permalink'' class='edit'>Delete sensor </a></td>
					</tr>";
				}
			}
		?>
	</tbody>
</table>
<label id="progress1" style="display: none;"></label>
</div>
</div>

