<?php 
	/*
		Pingdom Status Response Chart
		
		On sensor page, prints a chart of
		average response times during that
		current month.
	*/ 
	
	include_once(dirname(__FILE__) . "/PingdomStatus_charts.php");
?>
<div id="responseChart" style="width:500px;height:200px"></div>
<script type="text/javascript">
	/*
		responseColors['Line and Fill Color']
		Style the hoverbox by altering the div.responseChartHover class in pingdomstatus.css
	*/
	var responseChartData = '<?php echo PINGDOM_PLUGIN_URL; ?>/php/ajax_handlers/responsetime_data.php'+'<?php echo $varsForGraphs; ?>';
	var responseColors = ['#00cc00'];
</script>
<script type="text/javascript" src="<?php echo PINGDOM_PLUGIN_URL; ?>/js/flot/jquery.response.chart.js"></script>