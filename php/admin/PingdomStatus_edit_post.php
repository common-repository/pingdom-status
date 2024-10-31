<?php
	require_once(PINGDOM_PLUGIN_PATH . "/php/PingdomStatus_DB.php");
	require_once(PINGDOM_PLUGIN_PATH . "/php/PingdomStatus_Date.php");
	require_once(PINGDOM_PLUGIN_PATH . "/php/pingdom_sync/PingdomDataProvider.php");

	// Get existing sensors, groups and outages
	$conn = PingdomStatus_DB::getConnection();
	$sensorPostTable = $conn->getTable('PingdomPsSensorPost');
	$groupPostTable = $conn->getTable('PingdomPsGroupPost');
	$statePostTable = $conn->getTable('PingdomPsStatePost');
	$stateTable = $conn->getTable('PingdomPsState');
	
	// Get all public sensors
	$sensors = $conn->query("FROM PingdomPsSensor s LEFT JOIN s.SensorGroups sg LEFT JOIN sg.Group g WHERE s.is_public = 1");
	
	// Get groups
	$sensorGroups = $conn->getTable('PingdomPsGroup')->findAll();
	
	// Try to figure out if we are editing sensor
	$messageStatusId = MESSAGE_STATUS_NO_STATUS_ID;
	$postApplies = "all_servers";
	$selectedServerGroupIds = array();
	$selectedServerIds = array();
	$selectedOutageIds = ""; // Ids for javascript, separated by "|"
	$selectedOutagesDate = ""; // year, month, day, separated by "|"
	if(isset($_GET["post"])){
		$postId = $_GET["post"];
		
		// Search through xx_post tables to find to what entity this post relates to
		$statePosts = $statePostTable->findByDql("post_id=$postId");
		if($statePosts != null && count($statePosts) > 0){
			$messageStatusId = $statePosts[0]->message_status_id;
			$postApplies = "outage";
			
			$statePost = $statePosts[0];
			$oneStatePost = $stateTable->findOneById($statePost->state_id);
			$selectedOutagesDate = date("Y|m|d", strtotime($oneStatePost->time_from));
			foreach($statePosts as $statePost){
				$selectedOutageIds .= $statePost->state_id . "|";
			}
		}
		else {
			$groupPosts = $groupPostTable->findByDql("post_id=$postId");
			if($groupPosts != null && count($groupPosts) > 0){
				$messageStatusId = $groupPosts[0]->message_status_id;
				$postApplies = "server_group";
				foreach($groupPosts as $groupPost){
					$selectedServerGroupIds[] = $groupPost->group_id;
				}
			}
			else {
				$sensorPosts = $sensorPostTable->findByDql("post_id=$postId");
				if($sensorPosts != null && count($sensorPosts) > 0){
					$messageStatusId = $sensorPosts[0]->message_status_id;
					$postApplies = $sensorPosts[0]->sensor_id == -1 ? "all_servers" : "server";
					foreach($sensorPosts as $sensorPost){
						if($sensorPost->sensor_id != -1){
							$selectedServerIds[] = $sensorPost->sensor_id;
						}
					}
				}
			}
		}
	}
?>

<div id="normal-sortables" class="meta-box-sortables ui-sortable" style="position: relative;">
<div id="postpingdomstatus" class="postbox ">
<div class="handlediv" title="Click to toggle"><br /></div><h3 class='hndle'><span>Pingdom Status</span></h3>

<div class="inside">

<script type="text/javascript">
	var ajax_get_outagesurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/wp-content/plugins/pingdom-status/php/admin/ajax_handlers/PingdomStatus_post_getOutages.php";
</script>

<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/admin/PingdomStatus_edit_post.js?v=2"></script>
	
<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/jquery.selectboxes.pack.js"></script>
		
<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/date.js"></script>
	
<script
	type="text/javascript"
	src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/common/pingdom_datepick.js"></script>


<!-- Server variables -->
<input type="hidden" id="selectedOutageIds" value="<?php echo $selectedOutageIds; ?>"></input>
<input type="hidden" id="selectedOutagesDate" value="<?php echo $selectedOutagesDate; ?>"></input>

<table width="100%" cellpadding="3" cellspacing="3">
	<tbody>

		<tr valign="top">
			<td width="50%">
			<p><strong>Message Status:</strong><br /><label class="selectit"><input
				name="message_status" type="radio" id="post_status"
				value="<?php echo MESSAGE_STATUS_NO_STATUS_ID; ?>" <?php if ($messageStatusId == MESSAGE_STATUS_NO_STATUS_ID) echo "checked='checked'"; ?>/>
				<?php echo MESSAGE_STATUS_NO_STATUS_STRING; ?> (Nothing will be
			displayed) </label> <label class="selectit" style="color: #c00"><input
				id="post_status" name="message_status" type="radio"
				value="<?php echo MESSAGE_STATUS_UNRESOLVED_ID; ?>" <?php if ($messageStatusId == MESSAGE_STATUS_UNRESOLVED_ID) echo "checked='checked'"; ?>/> <?php echo MESSAGE_STATUS_UNRESOLVED_STRING; ?>
			</label> <label class="selectit" style="color: #0c0"><input
				id="post_status" name="message_status" type="radio"
				value="<?php echo MESSAGE_STATUS_RESOLVED_ID; ?>" <?php if ($messageStatusId == MESSAGE_STATUS_RESOLVED_ID) echo "checked='checked'"; ?>/> <?php echo MESSAGE_STATUS_RESOLVED_STRING; ?>
			</label></p>

			<p><strong>Post applies to: </strong><br /><label class="selectit"><input
				id="post_applies1" name="post_applies" value="all_servers"
				type="radio" <?php if ($postApplies == "all_servers") echo "checked='checked'"; ?>/> <?php echo MESSAGE_SCOPE_ALL_SERVERS; ?></label> <label
				class="selectit"><input id="post_applies2" name="post_applies"
				type="radio" value="server_group" <?php if ($postApplies == "server_group") echo "checked='checked'"; ?>/> <?php echo MESSAGE_SCOPE_SERVER_GROUP; ?> </label> <label
				class="selectit"><input id="post_applies3" name="post_applies"
				type="radio" value="server" <?php if ($postApplies == "server") echo "checked='checked'"; ?>/> <?php echo MESSAGE_SCOPE_SERVER; ?> </label> <label
				class="selectit"><input name="post_applies" type="radio"
				id="post_applies3" value="outage" <?php if ($postApplies == "outage") echo "checked='checked'"; ?>/> <?php echo MESSAGE_SCOPE_OUTAGE; ?> </label></p>
			</td>

			<td>
			<p>
			
			<span id="date_picker" <?php if ($postApplies != "outage") echo "style='display:none'"; ?>> <strong>Show outages
			for:</strong><br />

			<select id="selectMonth" name="selectMonth">
			</select> <select id="selectDay" name="selectDay">
			</select> <select id="selectYear" name="selectYear">

			</select> <input class="button" value="Update " id="updateOutages" name="updateOutages"
				type="button" />
			<label id="progress1" style="display:none"><br/>Getting outages...</label>
			<br /><br/>
			</span>
			 
			
			<span id="groups_panel" <?php if ($postApplies != "server_group") echo "style='display:none'"; ?>>
			<strong>Server groups:</strong> Choose server group(s).<br />
			<select name="selectGroups[]" size="9" multiple="multiple" id="select2"
				tabindex="7" style="width: 100%">
				<?php
					foreach ($sensorGroups as $group){
						if(in_array($group->id, $selectedServerGroupIds)){
							echo "<option value='$group->id' selected='selected'>$group->name</option>";
						}
						else{
							echo  "<option value='$group->id'>$group->name</option>";
						}
					}
				?>
			</select>
			Hold down CTRL to mark several.
			</span>
			
			
			
			<span id="servers_panel" <?php if ($postApplies != "server") echo "style='display:none'"; ?>>
			<strong>Servers:</strong> Choose server(s).<br />
			<select name="selectSensors[]" size="9" multiple="multiple" id="select2"
				tabindex="7" style="width: 100%">
				<?php
					foreach ($sensors as $sensor){
						$label = $sensor->name . " (" . $sensor->SensorGroups[0]->Group->name . ")";
						
						if(in_array($sensor->id, $selectedServerIds)){
							echo "<option value='$sensor->id' selected='selected'>$label</option>";
						}
						else{
							echo "<option value='$sensor->id'>$label</option>";
						} 
					}
				?>
			</select>
			Hold down CTRL to mark several.
			</span>
			
			<span id="outages_panel" <?php if ($postApplies != "outage") echo "style='display:none'"; ?>>
			<strong>Outages:</strong> Choose outage(s).<br />
			<select name="selectOutages[]" size="9" multiple="multiple" id="selectOutages"
				tabindex="7" style="width: 100%">
				<?php if($selectedOutagesHtml != "") echo $selectedOutagesHtml; ?>
			</select>
			Hold down CTRL to mark several.
			</span>
		    </p>
			</td>
		</tr>
	</tbody>
</table>
</div>
</div>
</div>
</div>
