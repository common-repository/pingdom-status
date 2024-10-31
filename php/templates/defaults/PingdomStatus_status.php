<div id="PingdomStatus">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js" type="text/javascript"></script>
<?php
	global $wp_query;
	if (isset($wp_query->query_vars["sensorId"])) {
		require(dirname(__FILE__) . '/report.php');
	} 
	else {
		require(dirname(__FILE__) . '/overview.php');
	}
?>
	<div id="PingdomStatus_footer">
		<p>
			Monitored by <a href="http://www.pingdom.com"/>Pingdom</a>
			<img src="<?php echo PINGDOM_PLUGIN_URL; ?>/images/logo_pingdom.png"/>
		</p>
		<p class="clear"></p>
	</div>
</div>