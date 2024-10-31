<?php
/*
 Plugin Name: Pingdom Status
 Plugin URI: http://www.pingdom.com
 Description: Displays uptime and response time statistics from the website monitoring service Pingdom.
 Author: Pingdom
 Version: 1.1.4
 Author URI: http://www.pingdom.com

 Copyright 2010 Pingdom AB

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
   2. Redistributions in binary form must reproduce the above copyright notice,
   	  this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE FREEBSD PROJECT ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

require_once("PingdomStatus_constants.php");
require_once(PINGDOM_PLUGIN_PATH . '/php/PingdomStatus_DB.php');
require_once(PINGDOM_PLUGIN_PATH . '/php/PingdomStatus_Functions.php');
require_once(PINGDOM_PLUGIN_PATH . '/php/utils/feedcreator.class.php');
if (!class_exists("PingdomStatus")) {
	class PingdomStatus {
		public	$customVars = array();
		public	$filteredByDateVars = array();

		function PingdomStatus(){
			$this->customVars = array("sensorId", "groupId", "domain", "status", "rssDomain", "rssCurrentStatus", "responseTime", "statusmonth");
			$this->filteredByDateVars = array("sensorId", "groupId", "domain", "responseTime", "statusmonth");
		}

		/**
		 * Installs plugin (on activation)
		 *
		 */
		function install() {
			global $wpdb;

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_group (
id int(10) unsigned NOT NULL auto_increment,
name varchar(255) NOT NULL,
PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Group of sensors' AUTO_INCREMENT=1;");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_group_post (
group_id int(10) unsigned NOT NULL,
post_id int(10) unsigned NOT NULL,
message_status_id int(10) unsigned NOT NULL,
PRIMARY KEY  (group_id,post_id),
KEY message_status_id (message_status_id),
KEY post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Connects post to a group';");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_message_status_type (
id int(10) unsigned NOT NULL,
value varchar(45) NOT NULL,
PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Type of message status';");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_sensor (
id int(10) unsigned NOT NULL auto_increment,
pingdom_sensor_id int(10) unsigned NOT NULL,
current_state_id int(10) unsigned NOT NULL,
type_id int(10) unsigned NOT NULL,
name varchar(512) NOT NULL,
target varchar(512) NOT NULL,
ip varchar(15) NOT NULL,
is_public int(10) unsigned NOT NULL,
deleted_in_pingdom enum('YES','NO') NOT NULL default 'NO' COMMENT 'If YES indicates that sensor doesn''t exist in pingdom',
next_state_update_time int(10) unsigned NOT NULL,
next_ip_resolving_time int(10) unsigned NOT NULL,
last_detected_down int(11) unsigned NOT NULL,
PRIMARY KEY  (id),
UNIQUE KEY Index_pingdom_sensor_id (pingdom_sensor_id),
KEY Index_type_id (type_id),
KEY Index_current_state_id (current_state_id),
KEY next_state_update_time (next_state_update_time),
KEY next_ip_resolving_time (next_ip_resolving_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sensors' AUTO_INCREMENT=1;");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_sensor_group (
sensor_id int(10) unsigned NOT NULL,
group_id int(10) unsigned NOT NULL,
PRIMARY KEY  USING BTREE (sensor_id,group_id),
KEY group_id (group_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Connection between sensor and group';");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_sensor_post (
sensor_id int(11) NOT NULL,
post_id int(10) unsigned NOT NULL,
message_status_id int(10) unsigned NOT NULL,
PRIMARY KEY  (sensor_id,post_id),
KEY post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Connects post to a sensor';");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_settings (
id int(10) unsigned NOT NULL auto_increment,
username varchar(255) NOT NULL,
password varchar(255) NOT NULL,
pingdom_api_key varchar(255) NOT NULL,
next_sensors_sync_time int(10) unsigned NOT NULL,
minimum_downtime_period int(10) unsigned NOT NULL,
ip_resolving_period int(10) unsigned NOT NULL,
sensors_sync_period int(10) unsigned NOT NULL,
state_update_period int(10) unsigned NOT NULL,
PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contains only one record with settings' AUTO_INCREMENT=1;");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_state (
id int(10) unsigned NOT NULL auto_increment,
sensor_id int(10) unsigned NOT NULL,
state_type_id int(10) unsigned NOT NULL,
time_from datetime NOT NULL,
time_to datetime NOT NULL,
PRIMARY KEY  (id),
KEY Index_sensor_id (sensor_id),
KEY state_type_id (state_type_id,sensor_id),
KEY time_from (time_from),
KEY time_to (time_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sensors states (from state_xx in aggregation database)' AUTO_INCREMENT=1;");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_responsetime (
id int(10) unsigned NOT NULL auto_increment,
sensor_id int(10) unsigned NOT NULL,
day date NOT NULL,
average_responsetime int unsigned NOT NULL,
PRIMARY KEY  (id),
KEY Index_sensor_id (sensor_id),
KEY Index_sensor_day (sensor_id, day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Sensors responsetimes' AUTO_INCREMENT=1;");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_state_post (
state_id int(10) unsigned NOT NULL,
post_id int(10) unsigned NOT NULL,
message_status_id int(10) unsigned NOT NULL,
PRIMARY KEY  USING BTREE (state_id,post_id),
KEY post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Connection between state and post';");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_state_type (
id int(10) unsigned NOT NULL,
value varchar(10) NOT NULL,
PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Catalog table with possible values for sensor state.';");

			$wpdb->query("CREATE TABLE IF NOT EXISTS pingdom_ps_type (
id int(10) unsigned NOT NULL,
value varchar(255) NOT NULL,
PRIMARY KEY  (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='type of sensor (WWW, POP3...)';");

			$wpdb->query("INSERT INTO pingdom_ps_group (id, name) VALUES
(1,'Ungrouped');");

			$wpdb->query("INSERT INTO pingdom_ps_message_status_type (id, value) VALUES
(1,'No status'),
(2,'Unresolved'),
(3,'Resolved');");

			$wpdb->query("INSERT INTO pingdom_ps_settings (id, username, password, pingdom_api_key, next_sensors_sync_time, minimum_downtime_period, ip_resolving_period, sensors_sync_period, state_update_period) VALUES
(1, '', '', '', 1206972002, 0, 3600, 600, 120);");

			$wpdb->query("INSERT INTO pingdom_ps_state_type (id, value) VALUES
(1, 'UP'),
(2, 'DOWN'),
(3, 'UNKNOWN');");

			$wpdb->query("INSERT INTO pingdom_ps_type (id, value) VALUES
(1, 'HTTP'),
(2, 'TCP Port'),
(3, 'Ping'),
(4, 'UDP'),
(5, 'SMTP'),
(6, 'POP3'),
(7, 'IMAP'),
(8, 'DNS'),
(9, 'Custom');");

	// Create backup on template files for the user
	$tpath = PINGDOM_PLUGIN_PATH . "/php/templates";
	$tbpath = $tpath . "/backup_" . date("Ymd_His");
	if(is_writeable($tpath)){
		mkdir($tbpath);
		copy($tpath . "/outages.php", 			$tbpath . "/outages.php");
		copy($tpath . "/overview.php", 			$tbpath . "/overview.php");
		copy($tpath . "/report.php", 			$tbpath . "/report.php");
		copy($tpath . "/response_chart.php", 	$tbpath . "/response_chart.php");
		copy($tpath . "/uptime_chart.php", 		$tbpath . "/uptime_chart.php");
		copy($tpath . "/pingdomstatus.css", 	$tbpath . "/pingdomstatus.css");
	}
}

		/**
		 * Adds general submenu
		 *
		 */
		function admin_menu_general_settings() {
			include 'php/admin/PingdomStatus_general_settings.php';
		}

		/**
		 * Adds submenu for non public checks
		 *
		 */
		function admin_menu_checks_nonpublic() {
			include 'php/admin/PingdomStatus_checks_nonpublic.php';
		}

		/**
		 * Adds submenu for public checks
		 *
		 */
		function admin_menu_checks_public() {
			include 'php/admin/PingdomStatus_checks_public.php';
		}

		/**
		 * Adds submenu for check groups
		 *
		 */
		function admin_menu_checks_groups() {
			include 'php/admin/PingdomStatus_checks_groups.php';
		}

		/**
		 * Adds submenu for editing templates
		 *
		 */
		function admin_menu_edit_templates() {
			include 'php/admin/PingdomStatus_edit_templates.php';
		}

		/**
		 * Adds menus to admin page
		 *
		 */
		function admin_actions() {
			wp_enqueue_script('jquery-ui-tabs');

			add_menu_page("Pingdom Status", "Pingdom Status", 'activate_plugins', __FILE__, array(&$this, "admin_menu_general_settings"));
			add_submenu_page(__FILE__, "Pingdom Status General Settings", "General Settings", 'activate_plugins', __FILE__, array(&$this, "admin_menu_general_settings"));
			add_submenu_page(__FILE__, "Pingdom Status Non-public Checks", "Non-public Checks", 'activate_plugins', "PingdomStatus_nonpublic", array(&$this, "admin_menu_checks_nonpublic"));
			add_submenu_page(__FILE__, "Pingdom Status Public Checks", "Public Checks", 'activate_plugins', "PingdomStatus_public", array(&$this, "admin_menu_checks_public"));
			add_submenu_page(__FILE__, "Pingdom Status Check Groups", "Check Groups", 'activate_plugins', "PingdomStatus_groups", array(&$this, "admin_menu_checks_groups"));
			add_submenu_page(__FILE__, "Pingdom Status Edit Templates", "Edit Templates", 'activate_plugins', "PingdomStatus_templates", array(&$this, "admin_menu_edit_templates"));
		}

		/**
		 * Adds edit post widget
		 *
		 */
		function edit_post_widget(){
			include 'php/admin/PingdomStatus_edit_post.php';
		}
		/**
		 * When post is saved...
		 *
		 */
		function save_post($postId){
			require_once 'php/PingdomStatus_Functions.php';

			// Gets variables from $_POST
			$objectType = $_POST["post_applies"];

			$objectIds = array();
			$objectIds["all_servers"] = array();
			$objectIds["server_group"] = $_POST["selectGroups"];
			$objectIds["server"] = $_POST["selectSensors"];
			$objectIds["outage"] =  $_POST["selectOutages"];

			$message_status_id = $_POST["message_status"];

			// Perform save or update operation
			if($objectType != null && $message_status_id != null){
				PingdomStatus_Functions::saveOrUpdatePost($postId, $objectType, $objectIds, $message_status_id);
			}
		}

		/**
		 * When post is deleted, we need to remove all related connections.
		 *
		 * @param unknown_type $postId
		 */
		function delete_post($postId){
			PingdomStatus_Functions::deletePost($postId);
		}

		/**
		 * Displays current status component on template
		 *
		 * @param unknown_type $type
		 */
		function current_status() {
			$timeRange = $this->queryStringToDate();
			return PingdomStatus_Functions::getGroupsTree(false, false, $timeRange->startDate, $timeRange->endDate);
		}

		/**
		 * Gets current status object that contains objects for current status table with only down sensors
		 */
		function current_status_long() {
			$timeRange = $this->queryStringToDate();
			return PingdomStatus_Functions::getGroupsTree(true, false, $timeRange->startDate, $timeRange->endDate);
		}

		/**
		 * Gets current status of all sensors
		 */
		function current_status_sensors() {
			$timeRange = $this->queryStringToDate();
			return PingdomStatus_Functions::getAllSensors(true, $timeRange->startDate, $timeRange->endDate);
		}

		/**
		 * Gets current status object that contains objects for current status table with all sensors
		 */
		function current_status_long_all(){
			$timeRange = $this->queryStringToDate();
			return PingdomStatus_Functions::getGroupsTree(true, true, $timeRange->startDate, $timeRange->endDate);
		}

		/**
		 * Handles redirects to PingdomStatus custom pages
		 *
		 */
		function templates(){
			global $wp_query;

			// Add jquery link
			wp_enqueue_script('jquery');

			if(isset($wp_query->query_vars["rssCurrentStatus"])){
				// Main feed parameters
				$rss = new UniversalFeedCreator();
				$rss->title = __("Pingdom status", "PingdomStatus");
				$rss->description = __("Pingdom status", "PingdomStatus");
				$rss->link = get_option('home') . "/?rssCurrentStatus=" . $wp_query->query_vars["rssCurrentStatus"];
				$rss->syndicationURL = get_option('home') . "/?rssCurrentStatus=" . $wp_query->query_vars["rssCurrentStatus"];

				// Create one item containing data
			    $item = new FeedItem();
			    $item->date = mktime();
			    $item->author = __("PingdomStatus User", "PingdomStatus");
			    $item->descriptionHtmlSyndicated = true;
			    $item->source = get_option('home');
			    $item->title = sprintf(__("Pingdom status on %s", "PingdomStatus"), date("Y-m-d H:i:s", mktime()));
			    $item->link = get_option('home');


			    $descriptionText = "";
			    $groups = PingdomStatus_Functions::getGroupsTree(false, false, null, null);

				foreach($groups as $group){
					$image = PINGDOM_PLUGIN_URL . "/images/status_up.gif";
					if($group->numOfDownSensors != 0){
						$image = PINGDOM_PLUGIN_URL . "/images/status_down.gif";
					}
					$url = get_option('home') . "/?groupId=" . $group->id;
					$descriptionText .= "<img src='$image'/>&nbsp;<a href='$url'>" . $group->name . "</a><br/>";

					foreach ($group->sensors as $sensor){
						$image = PINGDOM_PLUGIN_URL . "/images/status_down.gif";
						if($sensor->isUp){
							$image = PINGDOM_PLUGIN_URL . "/images/status_up.gif";
						}
						$url = get_option('home') . "/?sensorId=" . $sensor->id;
						$descriptionText .= "&nbsp;&nbsp;&nbsp;&nbsp;<img src='$image'/>&nbsp;<a href='$url'>" . $sensor->name . "</a><br/>";
					}
				}

			    $item->description = $descriptionText;
			    $rss->addItem($item);

				echo $rss->createFeed("2.0");
				exit;
			}

			if(isset($wp_query->query_vars["rssDomain"])){
				// Main feed parameters
				$rss = new UniversalFeedCreator();
				$rss->title = "Status for domain " . $wp_query->query_vars["rssDomain"];
				$rss->description = "Status for domain " . $wp_query->query_vars["rssDomain"];
				$rss->link = get_option('home') . "/?rssDomain=" . $wp_query->query_vars["rssDomain"];
				$rss->syndicationURL = get_option('home') . "/?rssDomain=" . $wp_query->query_vars["rssDomain"];

				// Create one item containing data
			    $item = new FeedItem();
			    $item->date = mktime();
			    $item->author = __("PingdomStatus User", "PingdomStatus");
			    $item->descriptionHtmlSyndicated = true;
			    $item->source = get_option('home');
			    $item->title = sprintf(__("Status for domain %s on %s", "PingdomStatus"), $wp_query->query_vars["rssDomain"], date("Y-m-d H:i:s", mktime()));
			    $item->link = get_option('home') . "/?domain=" . $wp_query->query_vars["rssDomain"];


			    $descriptionText = "";
			    $sensors = PingdomStatus_Functions::getSensorsForDomain($wp_query->query_vars["rssDomain"], null, null, false);
			    foreach($sensors as $sensor)
			    {
			    	$image = PINGDOM_PLUGIN_URL . "/images/status_down.gif";
			    	if($sensor->isUp){
			    		$image = PINGDOM_PLUGIN_URL . "/images/status_up.gif";
			    	}
			    	$url = get_option('home') . "/?sensorId=" . $sensor->id;
			    	$descriptionText .= "<img src='$image'/>&nbsp;<a href='$url'>" . $sensor->name . "</a><br/>";
			    }
			    $item->description = $descriptionText;
			    $rss->addItem($item);

				echo $rss->createFeed("2.0");
				exit;
			}
		}

		/**
		 * Our category, post and other links should append the "m" variable
		 *
		 * @param unknown_type $URL
		 * @param unknown_type $year
		 * @param unknown_type $month
		 */
		function format_wp_link($URL = '', $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = ''){
			global $wp_query;
			$toAppend = "";

			$paramPrefix = '&amp;';
			if(strstr($URL, '?') == FALSE){
				$paramPrefix = '?';
			}
			if(isset($wp_query->query_vars["statusmonth"]) && strlen($wp_query->query_vars["statusmonth"]) > 0){
				$toAppend .= $paramPrefix . 'statusmonth=' . $wp_query->query_vars["statusmonth"];
			}

			return $URL . $toAppend;
		}

		/**
		 * Our m link...etc  link should append all other post variables when on a page that uses them
		 *
		 * @param unknown_type $URL
		 * @param unknown_type $year
		 * @param unknown_type $month
		 */
		function format_m_link($URL, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = ''){
			global $wp_query;

			$modified = false;

			if (preg_match('/[?&]statusmonth=([^&]*)/', $URL, $matches)) {
				$link = add_query_arg('statusmonth', $matches[1]);

				foreach($wp_query->query_vars as $key=>$value){
					// Append appropriate custom var
					foreach($this->filteredByDateVars as $customVar){
						if($key == $customVar){
							$link = add_query_arg($key, $value, $link);
						}

						$modified = true;
						break;
					}
				}

				$link = clean_url($link);
			}

			return $modified ? $link : $URL;
		}

		/**
		 * Gets status of the message
		 *
		 * @param int $post_id
		 * @return string message status string
		 */
		function get_message_status_and_scope($post_id){
			return PingdomStatus_Functions::getMessageStatusAndScope($post_id);
		}

		/**
		 * Adds query vars that wordpress should "understand"
		 *
		 * @param ArrayOfString $qvars
		 */
		function query_vars($qvars){
			foreach ($this->customVars as $var){
				$qvars[] = $var;
			}
			return $qvars;
		}

		/**
		 * Performs posts filtering.
		 *
		 */
		function pre_get_posts(){
			global $wp_query;

			// We should always have a date
			if($wp_query->query_vars["m"] == 0){

				$wp_query->query_vars["m"] = date('Ym', mktime());
			}
		}

		/**
		 * Gets html to display as monthly archives
		 *
		 */
		function get_pingdom_archives($sensor_id){
			global $wpdb;
			global $wp_locale;
			global $wp_query;

			$toReturn = "";
			$months_for_sensor = PingdomStatus_Functions::getMonthsForSensor((int) $sensor_id);

			if ($months_for_sensor && count($months_for_sensor) > 0) {
				foreach ($months_for_sensor as $monthrow) {
					$monthlink = sprintf("%04d%02d", $monthrow["year"], $monthrow["month"]);
					$url = apply_filters('month_link', get_option('home') . '/?statusmonth=' . $monthlink, $year, $month);
					$text = sprintf('%1$s %2$d', $wp_locale->get_month($monthrow["month"]), $monthrow["year"]);
					$class = $monthlink == $wp_query->query_vars["statusmonth"] ? ' class="on"' : "";
					$text = wptexturize($text);
					$title_text = attribute_escape($text);
					$url = clean_url($url);
					$toReturn .= "<li $class><a href='$url' title='$title_text'>$text</a></li>";
				}
			}

			return $toReturn;
		}

		/**
		 * Gets current domain search string.
		 *
		 */
		function domain_search_string(){
			global $wp_query;
			if(isset($wp_query->query_vars["domain"])){
				return strip_tags($wp_query->query_vars["domain"]);
			}
			return "";
		}

		/**
		 * According to groupId, gets sensor group name
		 *
		 */
		function get_sensor_group_string(){
			global $wp_query;
			if(isset($wp_query->query_vars["groupId"])){
				return PingdomStatus_Functions::getGroupName($wp_query->query_vars["groupId"]);
			}
			return "";
		}

		/**
		 * According to sensorId gets sensor name, type...
		 *
		 */
		function get_sensor_info(){
			global $wp_query;
			if(isset($wp_query->query_vars["sensorId"])){
				return PingdomStatus_Functions::getSensorInfo($wp_query->query_vars["sensorId"]);
			}
			return "";
		}

		/**
		 * Gets array of SensorSummaryData according to current applied filter
		 *
		 */
		function list_sensors(){
			global $wp_query;

			$timeRange = $this->queryStringToDate();
			if(isset($wp_query->query_vars["groupId"])){
				$groupId = $wp_query->query_vars["groupId"];
				if(strlen($groupId) > 0){
					return PingdomStatus_Functions::getSensorsForGroup($groupId, $timeRange->startDate, $timeRange->endDate);
				}
				else {
					return false;
				}
			}
			else if(isset($wp_query->query_vars["domain"])){
				$domainString = $wp_query->query_vars["domain"];
				if(strlen($domainString) > 0){
					return PingdomStatus_Functions::getSensorsForDomain($domainString, $timeRange->startDate, $timeRange->endDate);
				}
				else {
					return false;
				}
			}
			else if(isset($wp_query->query_vars["sensorId"])){
				$sensorId = $wp_query->query_vars["sensorId"];
				if(strlen($sensorId) > 0){
					return PingdomStatus_Functions::getSensorsForId($sensorId, $timeRange->startDate, $timeRange->endDate);
				}
				else{
					return false;
				}
			}
			return false;
		}

		/**
		 * Gets array of StatusHistoryRow objects
		 *
		 */
		function get_status_history(){
			$timeRange = $this->queryStringToDate();

			global $wp_query;
			if(isset($wp_query->query_vars["sensorId"]) && count($wp_query->query_vars["sensorId"]) != 0){
				return PingdomStatus_Functions::getStatusHistory($wp_query->query_vars["sensorId"], $timeRange->startDate, $timeRange->endDate);
			}
			return array();
		}

		/**
		 * Returns list of categories for select->option tag
		 *
		 */
		function options_categories(){
			global $wp_query;
			$currentlySelected = "";

			if(isset($wp_query->query_vars['cat'])){
				$currentlySelected = $wp_query->query_vars['cat'];
			}

			$toReturn = "";
			if(strlen($currentlySelected) == 0){
				$toReturn .= "<option value='' selected='selected'>" . $this->all_string() . "</option>";
			}
			else {
				$toReturn .= "<option value=''>" . $this->all_string() . "</option>";
			}

			$categories = get_categories();

			foreach($categories as $category){
				if($category->cat_ID != $currentlySelected){
					$toReturn .= "<option value='$category->cat_ID'>$category->name</option>";
				}
				else{
					$toReturn .= "<option value='$category->cat_ID' selected='selected'>$category->name</option>";
				}
			}
			return $toReturn;
		}

		/**
		 * Returns list of message statuses for select->option tag
		 *
		 */
		function options_statuses(){
			global $wp_query;
			$currentlySelected = "";

			if(isset($wp_query->query_vars['status'])){
				$currentlySelected = $wp_query->query_vars['status'];
			}

			$toReturn = "";
			$conn = PingdomStatus_DB::getConnection();
			$statuses = $conn->getTable('PingdomPsMessageStatusType')->findAll();

			if(strlen($currentlySelected) == 0){
				$toReturn .= "<option value='' selected='selected'>" . $this->all_string() . "</option>";
			}
			else {
				$toReturn .= "<option value=''>" . $this->all_string() . "</option>";
			}

			foreach($statuses as $status){
				if($status->id != $currentlySelected){
					$toReturn .= "<option value='$status->id'>$status->value</option>";
				}
				else{
					$toReturn .= "<option value='$status->id' selected='selected'>$status->value</option>";
				}
			}
			return $toReturn;
		}

		/**
		 * Hidden vars for forms. It passes vars that are able to be filtered by date, and also date variable
		 *
		 */
		function vars_for_forms(){
			global $wp_query;
			$toReturn = "";
			foreach($wp_query->query_vars as $key=>$value){
				// Append appropriate custom var
				foreach($this->filteredByDateVars as $customVar){
					if($key == $customVar){
						$toReturn .= "<input type='hidden' value='$value' name='$key'/>";
						break;
					}
				}

				if($key == "m"){
					$toReturn .= "<input type='hidden' value='$value' name='m'/>";
				}
			}
			return $toReturn;
		}

		/**
		 * Gets a string that needs to be passed to uptime calculation script
		 * in the format ?sensor_id=6&month=200803
		 *
		 */
		function vars_for_graphs(){
			global $wp_query;

			if($this->exists("statusmonth") && $this->exists("sensorId")){
				return "?sensor_id=" . $wp_query->query_vars["sensorId"] . "&month=" . $wp_query->query_vars["statusmonth"];
			}
			return "";
		}

		/**
		 * Gets number of unresolved/resolved posts
		 *
		 */
		function get_number_of_posts(){
			global $wp_query;

			$resolved = 0;
			$unresolved = 0;

			//
			// Filtered posts
			$posts = query_posts($wp_query->query);

			foreach($posts as $post){
				$statusAndScope = PingdomStatus_Functions::getMessageStatusAndScope($post->ID);
				if($statusAndScope->ok === true){
					$resolved++;
				}
				else{
					$unresolved++;
				}
			}

			$toReturn = null;
			$toReturn->unresolved = $unresolved;
			$toReturn->resolved = $resolved;
			return $toReturn;
		}


		/**
		 * Gets number of minutes of downtime that is ignored
		 *
		 */
		function get_downtime_threshold(){
			$conn = PingdomStatus_DB::getConnection();
			$settingsTable = $conn->getTable('PingdomPsSettings');
			$settingsArray = $settingsTable->findAll();
			$settings = $settingsArray[0];
			return $settings->minimum_downtime_period;
		}

		/**
		 * Add where when filtering posts
		 *
		 */
		function filter_posts($posts){
			global $wp_query;
			global $wpdb;

			//
			// If there are no posts, return that empty array
			if(count($posts) == 0){
				return $posts;
			}

			//
			// Database objects
			$conn = PingdomStatus_DB::getConnection();
			$sensorPostTable = $conn->getTable("PingdomPsSensorPost");
			$statePostTable = $conn->getTable("PingdomPsStatePost");
			$groupPostTable = $conn->getTable("PingdomPsGroupPost");

			//
			// Create list of posts that are result of wordpress query
			$postIdsAsIn = "(";
			foreach($posts as $post){
				$postIdsAsIn .= $post->ID . ",";
			}
			$postIdsAsIn[strlen($postIdsAsIn) - 1] = ')';

			//
			// Lists to return
			$new_posts = array();
			$acceptable_post_ids = array();
			$filtered = false;

			//
			// Filter posts if status filter and current sensor is set
			if($this->exists("status")
			&& $this->exists("sensorId")){
				$statusId = $wp_query->query_vars["status"];
				$sensorId = $wp_query->query_vars["sensorId"];

				// Find all sensor_post entries with appropriate message_status_id, sensor_id (sensor_id can be -1, too)
				$sensorPosts = $sensorPostTable->findByDql("(sensor_id=? OR sensor_id=-1) AND message_status_id=? AND post_id IN $postIdsAsIn", array($sensorId, $statusId));

				// Find all state_post entries with appropriate message_status_id and sensor_id
				$statePosts = $conn->query("FROM PingdomPsStatePost statePost LEFT JOIN statePost.State state WHERE state.sensor_id=? AND statePost.message_status_id=? AND statePost.post_id IN $postIdsAsIn", array($sensorId, $statusId));

				foreach($sensorPosts as $sensorPost){
					$acceptable_post_ids[] = $sensorPost->post_id;
				}
				foreach($statePosts as $statePost){
					$acceptable_post_ids[] = $statePost->post_id;
				}
				$filtered = true;
			}
			// Filter posts if current sensor is set
			else if($this->exists("sensorId")){
				$sensorId = $wp_query->query_vars["sensorId"];
				$sensorPosts = $sensorPostTable->findByDql("(sensor_id=? OR sensor_id=-1) AND post_id IN $postIdsAsIn", array($sensorId));

				// Find all state_post entries with appropriate sensor_id
				$statePosts = $conn->query("FROM PingdomPsStatePost statePost LEFT JOIN statePost.State state WHERE state.sensor_id=? AND statePost.post_id IN $postIdsAsIn", array($sensorId));

				foreach($sensorPosts as $sensorPost){
					$acceptable_post_ids[] = $sensorPost->post_id;
				}
				foreach($statePosts as $statePost){
					$acceptable_post_ids[] = $statePost->post_id;
				}

				$filtered = true;
			}
			// Filter posts if status filter is set
			else if($this->exists("status")){
				$statusId = $wp_query->query_vars["status"];

				$sensorPosts = $sensorPostTable->findByDql("message_status_id=? AND post_id IN $postIdsAsIn", array($statusId));
				$statePosts = $statePostTable->findByDql("message_status_id=? AND post_id IN $postIdsAsIn", array($statusId));
				$groupPosts = $groupPostTable->findByDql("message_status_id=? AND post_id IN $postIdsAsIn", array($statusId));

				foreach($sensorPosts as $sensorPost){
					$acceptable_post_ids[] = $sensorPost->post_id;
				}

				foreach($statePosts as $statePost){
					$acceptable_post_ids[] = $statePost->post_id;
				}

				foreach($groupPosts as $groupPost){
					$acceptable_post_ids[] = $groupPost->post_id;
				}

				$filtered = true;
			}

			//
			// Include only acceptable posts
			foreach($posts as $post){
				$acceptable = false;
				foreach($acceptable_post_ids as $acceptable_id){
					if($acceptable_id == $post->ID){
						$acceptable = true;
						break;
					}
				}

				if($acceptable){
					$new_posts[] = $post;
				}
			}

			//
			// If not filtered, return all posts
			if($filtered == true){
				return $new_posts;
			}
			else{
				return $posts;
			}
		}

		/**
		 * Gets current date according to selected
		 *
		 * @param unknown_type $format
		 */
		function the_time($format){
			global $wp_query;
			global $wp_locale;

			//
			// Default values
			$localTimestamp = time() + (get_option('gmt_offset') * 3600);
			$year = date("Y", $localTimestamp);
			$month = date("m", $localTimestamp);

			if(isset($wp_query->query_vars["m"])){
				if(count($wp_query->query_vars["m"]) != 0){
					$year = substr($wp_query->query_vars["m"], 0, 4);
					$month = substr($wp_query->query_vars["m"], 4);
				}
			}

			echo date_i18n($format, mktime(1, 1, 1, $month, 1, $year));
		}

		/**
		 * Returns true if $param_name is set
		 *
		 */
		function exists($param_name){
			global $wp_query;
			return isset($wp_query->query_vars[$param_name])
			&& strlen($wp_query->query_vars[$param_name]) > 0;
		}

		/**
		 * Gets date from a query string. Returns an object with startDate and endDate properties.
		 *
		 */
		function queryStringToDate($dateTimeSeparator = "T"){
			global $wp_query;

			//
			// Default values
			$localTimestamp = time() + (get_option('gmt_offset') * 3600);
			$year = date("Y", $localTimestamp);
			$month = date("m", $localTimestamp);

			$toReturn = null;
			if(isset($wp_query->query_vars["statusmonth"])){
				if(count($wp_query->query_vars["statusmonth"]) != 0){
					$year = substr($wp_query->query_vars["statusmonth"], 0, 4);
					$month = substr($wp_query->query_vars["statusmonth"], 4);
				}
			}

			$endDay = date('t', strtotime($year . '-' . $month . '-01'));
			$toReturn->startDate = $year . "-" . $month . "-01" . $dateTimeSeparator. "00:00:00";
			$toReturn->endDate = $year . "-" . $month . "-" . $endDay . $dateTimeSeparator . "23:59:59";
			return $toReturn;
		}

		/**
		 * TODO: This should be fixed to use multilanguage
		 *
		 */
		function all_string(){
			return "Alla";
		}

		/*
		 * Synchronize database with Pingdom.
		 *
		 */
		function synchronize() {
			ob_start();

			require_once (dirname(__FILE__) . '/php/pingdom_sync/PingdomDataProvider.php');
			PingdomDataProvider::performSensorsSync();
			PingdomDataProvider::performStateSync();
			PingdomDataProvider::performIpResolving();

			ob_end_clean();
		}

		function header_includes() {
			global $wp_query;

			if(!$this->exists("statusmonth")) {
				$localTimestamp = time() + (get_option('gmt_offset') * 3600);
				$year = date("Y", $localTimestamp);
				$month = date("m", $localTimestamp);
				$wp_query->set("statusmonth", "$year$month");
			}

			echo '<link type="text/css" rel="stylesheet" href="' . PINGDOM_PLUGIN_URL . '/css/smoothness/ui.all.css" />' . "\n";
			echo '<link type="text/css" rel="stylesheet" href="' . PINGDOM_PLUGIN_URL . '/php/templates/pingdomstatus.css" />' . "\n";
		}

		function init() {
			$plugin_dir = basename(dirname(__FILE__)) . "/locales";
			load_plugin_textdomain('PingdomStatus', 'wp-content/plugins/' . $plugin_dir, $plugin_dir);
		}

	} //End Class PingdomStatus
}

/*
 * [pingdomstatus_status] shortcode
 *
 */
function pingdomstatus_status_shortcode($attrs) {
	extract(shortcode_atts(array(
		'locale' => 'default'
	), $attrs));

	if ($locale !== "default") {
		$old_locale = setlocale(LC_ALL, null);
		setlocale(LC_ALL, $locale);
	}

	ob_start();
	require(dirname(__FILE__) . '/php/templates/PingdomStatus_status.php');
	$retval = ob_get_contents();
	ob_end_clean();

	if ($locale !== "default") {
		setlocale(LC_ALL, $old_locale);
	}

	return $retval;
}

if (class_exists("PingdomStatus")) {
	$pingdom_PingdomStatus = new PingdomStatus();
}








//Actions and Filters
if (isset($pingdom_PingdomStatus)) {
	// Actions on admin page and installation
	add_action('activate_pingdom-status/PingdomStatus.php', array(&$pingdom_PingdomStatus, 'install'));
	add_action('admin_menu', array(&$pingdom_PingdomStatus, 'admin_actions'));

	// Init for translation
	add_action('init', array(&$pingdom_PingdomStatus, 'init'));



	// Add or edit post action
/*
	add_action('simple_edit_form', array(&$pingdom_PingdomStatus,'edit_post_widget'));
	add_action('edit_form_advanced', array(&$pingdom_PingdomStatus,'edit_post_widget'));
	add_action('edit_page_form', array(&$pingdom_PingdomStatus,'edit_post_widget'));
*/

	// Save changes to post
/*
	add_action('publish_post', array(&$pingdom_PingdomStatus,'save_post'));
	add_action('edit_post', array(&$pingdom_PingdomStatus,'save_post'));
	add_action('save_post', array(&$pingdom_PingdomStatus,'save_post'));
	add_action('wp_insert_post', array(&$pingdom_PingdomStatus,'save_post'));
	add_action('delete_post', array(&$pingdom_PingdomStatus, 'delete_post'));
*/

	// Filter posts
	// add_action('pre_get_posts', array(&$pingdom_PingdomStatus, 'pre_get_posts') );
	// add_filter('the_posts', array(&$pingdom_PingdomStatus, 'filter_posts'), 1 );

	// Display specific pages
	// add_action('template_redirect', array(&$pingdom_PingdomStatus, 'templates'));

	// Synchronize with Pingdom
	 add_action('wp_footer', array(&$pingdom_PingdomStatus, 'synchronize'));

	// Add custom javascript + css
	add_action('wp_head', array(&$pingdom_PingdomStatus, 'header_includes'));
	add_action('admin_head', array(&$pingdom_PingdomStatus, 'header_includes'));

	// Filters
	add_filter('query_vars', array(&$pingdom_PingdomStatus, 'query_vars'));
	//add_filter('month_link', array(&$pingdom_PingdomStatus, 'format_m_link'));
	//add_filter('category_link', array(&$pingdom_PingdomStatus, 'format_wp_link'));
	//add_filter('post_link', array(&$pingdom_PingdomStatus, 'format_wp_link'));
	//add_filter('page_link', array(&$pingdom_PingdomStatus, 'format_wp_link'));

	// Shortcodes
	add_shortcode('pingdom_status', 'pingdomstatus_status_shortcode');



	// Widget
	//add_action('widgets_init', create_function('', 'return register_widget("PingdomStatus_widget");'));
}
?>
