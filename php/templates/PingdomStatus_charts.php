<?php
	global $pingdom_PingdomStatus;
	$varsForGraphs = $pingdom_PingdomStatus->vars_for_graphs(); 	
?>
<?php wp_enqueue_script('jquery'); ?> 
<script type="text/javascript" src="<?php echo PINGDOM_PLUGIN_URL; ?>/flot/jquery.flot.js"></script>
<script type="text/javascript" src="<?php echo PINGDOM_PLUGIN_URL; ?>/flot/jquery.flot.stack.js"></script>
<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo PINGDOM_PLUGIN_URL; ?>/flot/excanvas.min.js"></script><![endif]-->