<?php
require_once(PINGDOM_PLUGIN_PATH . "/php/PingdomStatus_DB.php");
// Get existing settings
$conn = PingdomStatus_DB::getConnection();
$settingsTable = $conn->getTable('PingdomPsSettings');
$settingsArray = $settingsTable->findAll();
$settings = $settingsArray[0];
?>
<script type="text/javascript">
	var ajax_saveurl =  "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_general_settings_save.php";
	var ajax_syncurl = "<?php echo PINGDOM_PLUGIN_URL; ?>/php/admin/ajax_handlers/PingdomStatus_general_settings_sync.php";
</script>
<script type="text/javascript" src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/admin/PingdomStatus_general_settings.js"></script>

<div class="wrap">
<div id="progress" class="updated" style="display: none;">
	<p></p>
</div>
<h2>General Settings</h2>
<p>Don't have a Pingdom account? <a href="http://pingdom.com/free" target="_blank">Get one for free</a>.</p>
<div style="width: 100%">

<table class="form-table">
	<tr>
		<th scope="row">Pingdom Username:</th>
		<td><input name="username" type="text" id="username" size="30"
			value="<?php echo $settings->username ?>" /></td>
	</tr>
	<tr>
		<th scope="row">Pingdom Password:</th>
		<td><input name="password" type="password" id="password" size="30"
			value="<?php echo $settings->password ?>" /></td>
	</tr>
	<tr>
		<th scope="row">Pingdom API key:</th>
		<td><input name="api_key" type="text" id="api_key" size="30"
			value="<?php echo $settings->pingdom_api_key ?>" /><span class="setting-description">
		The Pingdom API-key is available in the Pingdom control panel.</span></td>
	</tr>
	<tr>
		<th scope="row">Downtime Threshold :</th>
		<td><input name="threshold" type="text" id="threshold" size="2"
			value="<?php echo $settings->minimum_downtime_period ?>" /> minutes.<br />
		<span class="setting-description">Outages shorter than this will not be displayed or counted.</span></td>
	</tr>
</table>

<p class="submit">
	<input type="button" id="submit" name="submit" value="Update Options &raquo;" />
	<input type="button" id="syncnow" name="submit" value="Synchronize with Pingdom &raquo;" /> <br />
</p>
<a class="showlog" href="#" style="display: none">Show output &raquo;</a>
<p class="log" style="font-size: 0.7em; font-style: italic; display: none"></p>
</div>
</div>
