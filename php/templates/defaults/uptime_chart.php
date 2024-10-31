<?php 
	/*
		Pingdom Status Uptime Chart
		
		On sensor page, prints a chart of
		uptime and downtime percentage per day 
		during that current month.
	*/ 
	
	include_once(dirname(__FILE__) . "/PingdomStatus_charts.php");
?>
<div id="uptimeChart" style="width:500px;height:200px"></div>
<script type="text/javascript">
	/*
		uptimeColors['Uptime Color', 'Downtime Color']
		Style the hoverbox by altering the div.uptimeChartHover class in pingdomstatus.css
	*/
	var uptimeColors = ['#00cc00', '#cc0000'];
	var uptimeChartData = '<?php echo PINGDOM_PLUGIN_URL; ?>/php/ajax_handlers/uptime_data.php'+'<?php echo $varsForGraphs; ?>';
</script>
<script type="text/javascript" src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/flot/jquery.uptime.chart.js"></script>
