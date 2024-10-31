<?php
// Paths
define("PINGDOM_PLUGIN_URL", WP_PLUGIN_URL."/".dirname(plugin_basename(__FILE__)));
define("PINGDOM_PLUGIN_PATH", WP_PLUGIN_DIR."/".dirname(plugin_basename(__FILE__)));

// Errors
define("PINGDOM_AJAX_ERROR", "ERROR");
define("PINGDOM_AJAX_OK", "OK");

// Those constants correspond to pingdom_ps_state_type table
define("SENSOR_STATE_UP_ID", 1);
define("SENSOR_STATE_DOWN_ID", 2);
define("SENSOR_STATE_UNKNOWN_ID", 3);

// Those constans correspond to pingdom_ps_message_status_type pable
define("MESSAGE_STATUS_NO_STATUS_ID", 1);
define("MESSAGE_STATUS_UNRESOLVED_ID", 2);
define("MESSAGE_STATUS_RESOLVED_ID", 3);

define("MESSAGE_STATUS_NO_STATUS_STRING", "No status");
define("MESSAGE_STATUS_UNRESOLVED_STRING", "Unresolved");
define("MESSAGE_STATUS_RESOLVED_STRING", "Resolved");

define("MESSAGE_SCOPE_ALL_SERVERS", "Alla kontroller");
define("MESSAGE_SCOPE_SERVER_GROUP", "Kontrollgrupp");
define("MESSAGE_SCOPE_SERVER", "Kontroll");
define("MESSAGE_SCOPE_OUTAGE", "H&auml;ndelse");
?>