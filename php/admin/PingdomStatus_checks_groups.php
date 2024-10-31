<?php
	require_once(PINGDOM_PLUGIN_PATH . '/php/PingdomStatus_DB.php');
	
	// Get existing groups
	$conn = PingdomStatus_DB::getConnection();
	$groupsTable = $conn->getTable('PingdomPsGroup');
	$groups = $groupsTable->findAll();
	
?>
<script type="text/javascript">
	var ajax_editurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_checks_groups_edit.php";
	var ajax_addurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_checks_groups_add.php";
	var ajax_deleteurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_checks_groups_delete.php";
</script>

<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/jquery.teditable.js"></script>
<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/common/deleterow.js"></script>
<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/admin/PingdomStatus_checks_groups.js"></script>

<div class="wrap">
<h2>Check Groups</h2>
<div id="progress" class="updated" style="display: none;"><p></p></div>
<div style="width: 100%">
<table id="groups_list" class="widefat">
	<thead>
		<tr>
			<th scope="col">ID</th>
			<th scope="col">Group Name</th>
			<th scope="col"></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach($groups as $group){

			echo "
			<tr id='$group->id'>
			<th scope='row'>$group->id</th>
			<td id='$group->id'>$group->name</td>". 
			($group->id != 1 ? "<td id='$group->id' class='delete_button'><a href='#' rel='permalink' class='edit'>Delete </a></td>" : "<td>Default for ungrouped checks</td>" ).
			"</tr>";
		
		}
		?>
	</tbody>
</table>
</div>


</div>

<div class="wrap">

<h2>Add New Check Group</h2>

<table class="form-table">
<tr valign="top">
	<th scope="row">
		<label>Group Name</label>
	</th>
	<td>
		<input type="text" name="group_name" id="group_name" value="" />
	</td>
</tr>
</table>
<p class="submit"><input type="button" name="submit" id="submit"
	value="Add Group &raquo;" /> <br />
</p>

</div>
