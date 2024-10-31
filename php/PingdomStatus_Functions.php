<?php
require_once(dirname(__FILE__)."/../PingdomStatus_constants.php");
require_once(PINGDOM_PLUGIN_PATH . "/php/PingdomStatus_DB.php");
require_once(PINGDOM_PLUGIN_PATH . "/php/objects/SensorSummaryData.php");
require_once(PINGDOM_PLUGIN_PATH . "/php/objects/GroupSummaryData.php");
require_once(PINGDOM_PLUGIN_PATH . "/php/objects/StatusHistoryRow.php");
require_once(PINGDOM_PLUGIN_PATH . "/php/objects/SensorBasicInfo.php");

/**
 * PingdomStatus business logic functions.
 *
 */
class PingdomStatus_Functions {
    /**
     * Saves or updates connection between post and object to which this post is related to.
     *
     * @param int $postId Id of post that is saved/published...
     * @param string $objectType Identifies to which object type this post is related.
     *                                     Can be one of "all_servers", "server_group", "server", "outage"
     * @param array of int $objectIds Ids of objects to which this post is related. Example:
     *                                 ["objectType1"=>{3, 4, 6}, "objectType2"=>{5, 7, 9}]
     * @param int $message_status_id Id of status of this message
     */
    public static function saveOrUpdatePost($postId, $objectType, $objectIds, $message_status_id){
        $ids = $objectIds[$objectType];

        // Delete all old ones
        PingdomStatus_Functions::deletePost($postId);
        switch ($objectType){
            case "all_servers":
                // If post is related to all servers, then make a connection where sensor_id is -1
                $conn = PingdomStatus_DB::getConnection();

                $newOne = new PingdomPsSensorPost();
                $newOne->sensor_id = -1;
                $newOne->post_id = $postId;
                $newOne->message_status_id = $message_status_id;
                $newOne->save();
                break;
            case "server_group":
                // There should be (or already is) a connection between blog post and server group
                foreach ($ids as $id){
                    $conn = PingdomStatus_DB::getConnection();
                    $newOne = new PingdomPsGroupPost();
                    $newOne->group_id = $id;
                    $newOne->post_id = $postId;
                    $newOne->message_status_id = $message_status_id;
                    $newOne->save();
                }
                break;
            case "server":
                // There should be (or already is) a connection between blog post and server
                foreach ($ids as $id){
                    $conn = PingdomStatus_DB::getConnection();
                    $newOne = new PingdomPsSensorPost();
                    $newOne->sensor_id = $id;
                    $newOne->post_id = $postId;
                    $newOne->message_status_id = $message_status_id;
                    $newOne->save();
                }
                break;
            case "outage":
                // There should be (or already is) a connection between status and server
                foreach ($ids as $id){
                    $conn = PingdomStatus_DB::getConnection();

                    $newOne = new PingdomPsStatePost();
                    $newOne->state_id = $id;
                    $newOne->post_id = $postId;
                    $newOne->message_status_id = $message_status_id;
                    $newOne->save();
                }
                break;
            default:
                break;
        }
    }

    /**
     * Deletes connection between post and all related objects.
     *
     * @param int $postId Id of post to delete
     */
    public static function deletePost($postId){
        $conn = PingdomStatus_DB::getConnection();

        // Perform deletion in PingdomPsStatePost table
        $tableObject = $conn->getTable('PingdomPsStatePost');
        $connections = $tableObject->findByDql("post_id=$postId");
        $connections->delete();

        // Perform deletion in PingdomPsSensorPost table
        $tableObject = $conn->getTable('PingdomPsSensorPost');
        $connections = $tableObject->findByDql("post_id=$postId");
        $connections->delete();

        // Perform deletion in PingdomPsGroupPost table
        $tableObject = $conn->getTable('PingdomPsGroupPost');
        $connections = $tableObject->findByDql("post_id=$postId");
        $connections->delete();
    }

    /**
     * Returns an object with properties "ok":(bool)true if status is resolved "status":string and "scope": (one of MESSAGE_SCOPE... constants)
     *
     * @param unknown_type $messageId
     */
    public static function getMessageStatusAndScope($postId){
        $toReturn = null;
        $conn = PingdomStatus_DB::getConnection();

        // Check to see if it is in pingdom_ps_sensor_post table
        $tableObject = $conn->getTable('PingdomPsSensorPost');
        $connections = $tableObject->findByDql("post_id=$postId");
        if($connections != null && count($connections) > 0){
            if($connections[0]->message_status_id != MESSAGE_STATUS_NO_STATUS_ID){
                $toReturn->status = $connections[0]->Type->value;
            }
            $toReturn->ok = ($connections[0]->message_status_id == MESSAGE_STATUS_RESOLVED_ID);
            $toReturn->scope = $connections[0]->sensor_id == -1 ? MESSAGE_SCOPE_ALL_SERVERS : MESSAGE_SCOPE_SERVER;
            return $toReturn;
        }

        //     Check to see if it is in pingdom_ps_group_post table
        $tableObject = $conn->getTable('PingdomPsGroupPost');
        $connections = $tableObject->findByDql("post_id=$postId");
        if($connections != null && count($connections) > 0){
            if($connections[0]->message_status_id != MESSAGE_STATUS_NO_STATUS_ID){
                $toReturn->status = $connections[0]->Type->value;
            }
            $toReturn->ok = ($connections[0]->message_status_id == MESSAGE_STATUS_RESOLVED_ID);
            $toReturn->scope = MESSAGE_SCOPE_SERVER_GROUP;
            return $toReturn;
        }

        //     Check to see if it is in pingdom_ps_state_post table
        $tableObject = $conn->getTable('PingdomPsStatePost');
        $connections = $tableObject->findByDql("post_id=$postId");
        if($connections != null && count($connections) > 0){
            if($connections[0]->message_status_id != MESSAGE_STATUS_NO_STATUS_ID){
                $toReturn->status = $connections[0]->Type->value;
            }
            $toReturn->ok = ($connections[0]->message_status_id == MESSAGE_STATUS_RESOLVED_ID);
            $toReturn->scope = MESSAGE_SCOPE_OUTAGE;
            return $toReturn;
        }

        $toReturn->status = "ERROR";
        $toReturn->ok = false;
        $toReturn->scope = "ERROR";
        return $toReturn;

    }

    /**
     * Gets sensor basic info
     *
     * @param int $sensorId
     */
    public static function getSensorInfo($sensorId){
        $conn = PingdomStatus_DB::getConnection();
        $sensors = $conn->query("FROM PingdomPsSensor s LEFT JOIN s.Type LEFT JOIN s.SensorGroups sg LEFT JOIN sg.Group WHERE s.id=$sensorId");

        $toReturn = new SensorBasicInfo();
        if($sensors != null && count($sensors) > 0){
            $toReturn->name = $sensors[0]->name;
            $toReturn->checkType = $sensors[0]->Type->value;
            $toReturn->group = count($sensors[0]->SensorGroups) > 0 ? $sensors[0]->SensorGroups[0]->Group->name : "";
            $toReturn->groupId = count($sensors[0]->SensorGroups) > 0 ? $sensors[0]->SensorGroups[0]->Group->id : -1;
            $toReturn->sensorId = $sensorId;
            $toReturn->is_public = $sensors[0]->is_public;

            return $toReturn;
        }
        return $toReturn;
    }

    /**
     * Gets a list of the months with sensor data for a sensor
     *
     * @param int $sensorId
     */
    public static function getMonthsForSensor($sensorId){
	$toReturn = array();

        $conn = PingdomStatus_DB::getConnection();
        $months = $conn->query("SELECT day FROM PingdomPsResponseTime WHERE sensor_id=$sensorId");

	if ($months != null && count($months) > 0) {
		foreach ($months as $monthrow) {
			$time = strtotime($monthrow->day);

			$retrow = array();
			$retrow["year"] = date("Y", $time);
			$retrow["month"] = date("m", $time);
			$toReturn[$retrow["year"] . $retrow["month"]] = $retrow;
		}
	}

	return $toReturn;
    }

    /**
     * Gets group name
     *
     * @param unknown_type $groupId
     * @return unknown
     */
    public static function getGroupName($groupId){
        $conn = PingdomStatus_DB::getConnection();
        $tableObject = $conn->getTable('PingdomPsGroup');
        $obj = $tableObject->findByDql("id=$groupId");
        if($obj != null && count($obj) > 0){
            return $obj[0]->name;
        }
        else{
            return "";
        }
    }

    /**
     * Gets array of objects (servers) with properties:
     * name, ip, uptime, downtime, outages
     *
     * @param unknown_type $groupId
     */
    public static function getSensorsForGroup($groupId, $startDate, $endDate){
        $toReturn = array();

        $conn = PingdomStatus_DB::getConnection();

        // Cache sensor types
        $sensorTypes = $conn->getTable('PingdomPsType')->findAll();
        $sensorTypesById = array();
        foreach ($sensorTypes as $sensorType){
            $sensorTypesById[$sensorType->id] = $sensorType->value;
        }

        // Get all sensor groups
        $allGroups = $conn->query("FROM PingdomPsGroup g LEFT JOIN g.SensorGroups sg LEFT JOIN sg.Sensor s WHERE g.id=$groupId ORDER BY s.name ASC");
        if($allGroups != null && count($allGroups) > 0){
            $sensorGroups = $allGroups[0]->SensorGroups;
            foreach($sensorGroups as $sensorGroup){
                $sensor = $sensorGroup->Sensor;
                $newOne = new SensorSummaryData();
                $newOne->id = $sensor->id;
                $newOne->name = $sensor->name;
                $newOne->type = $sensorTypesById[$sensor->type_id];
                $newOne->isUp = ($sensor->current_state_id == SENSOR_STATE_UP_ID);
                $toReturn[] = $newOne;
            }
        }

        // Update with uptime data
        PingdomStatus_Functions::getUptimeDataForSensors($toReturn, $startDate, $endDate);

        return $toReturn;
    }

    /**
     * Gets summary data for specified sensor
     *
     * @param unknown_type $sensorId
     * @param unknown_type $startDate
     * @param unknown_type $endDate
     */
    public static function getSensorsForId($sensorId, $startDate, $endDate){
        $toReturn = array();


        $conn = PingdomStatus_DB::getConnection();

        // Cache sensor types
        $sensorTypes = $conn->getTable('PingdomPsType')->findAll();
        $sensorTypesById = array();
        foreach ($sensorTypes as $sensorType){
            $sensorTypesById[$sensorType->id] = $sensorType->value;
        }

        $tableObject = $conn->getTable('PingdomPsSensor');
        $sensors = $tableObject->findByDql("id=?", array($sensorId));
        if($sensors != null && count($sensors) > 0){
            foreach($sensors as $sensor){
                $newOne = new SensorSummaryData();
                $newOne->id = $sensor->id;
                $newOne->name = $sensor->name;
                $newOne->type = $sensorTypesById[$sensor->type_id];
                $newOne->isUp = ($sensor->current_state_id == SENSOR_STATE_UP_ID);
                $toReturn[] = $newOne;
            }

            // Update with uptime data
            PingdomStatus_Functions::getUptimeDataForSensors($toReturn, $startDate, $endDate);
        }

        return $toReturn;
    }

    /**
     * Gets array of objects (sensors) with properties:
     * name, ip, uptime, downtime, outages
     *
     * @param unknown_type $groupId
     */
    public static function getSensorsForDomain($domainName, $startDate, $endDate, $detailed = true){
        $toReturn = array();
        $toSearchFor = array("www", "mail", "pop3", "imap", "smtp", "mysql", "sql");

        $conn = PingdomStatus_DB::getConnection();
        $tableObject = $conn->getTable('PingdomPsSensor');

        // Cache sensor types
        $sensorTypes = $conn->getTable('PingdomPsType')->findAll();
        $sensorTypesById = array();
        foreach ($sensorTypes as $sensorType){
            $sensorTypesById[$sensorType->id] = $sensorType->value;
        }

        //
        // Create all subdomains that we are searching for
        $domains = array();
        $domainBase = trim(strtolower($domainName));
        $domains[] = $domainBase;

        //
        // If we are searching specific subdomain, then nothing
        $subdomainSpecified = false;
        foreach($toSearchFor as $prefix){
        	$index = strpos($domainBase, $prefix);
        	if($index === 0){
        		$subdomainSpecified = true;
        		break;
        	}
        }

        // Add another domains if no subdomain is specified
        if(!$subdomainSpecified){
        	foreach($toSearchFor as $prefix){
        		$domains[] = $prefix . '.' . $domainBase;
        	}
        }


        // Get all IPs to search for
        $ips = array();

        $alreadyAdded = array();
        foreach($domains as $domain){
	        // Resolve domain name to IP
	        $ip = gethostbyname($domain);
	        if(trim($ip) != $domain){
	        	$ips[] = $ip;
	        }

	        // For each ip, find appropriate sensors
	        foreach($ips as $ip){
	            $sensors = $tableObject->findByDql("ip='$ip' ORDER BY name ASC");
	            foreach($sensors as $sensor){
	            	if(!in_array($sensor->id, $alreadyAdded)){
		                $newOne = new SensorSummaryData();
		                $newOne->id = $sensor->id;
		                $newOne->name = $sensor->name;
		                $newOne->type = $sensorTypesById[$sensor->type_id];
		                $newOne->isUp = ($sensor->current_state_id == SENSOR_STATE_UP_ID);
		                $toReturn[] = $newOne;
		                $alreadyAdded[] = $sensor->id;
	            	}
	            }
	        }
        }

        // Update with uptime data
        if($detailed){
        	PingdomStatus_Functions::getUptimeDataForSensors($toReturn, $startDate, $endDate);
        }

        return $toReturn;
    }

    /**
     * Returns array of SensorSummaryData
     *
     * @param bool $detailed should detailed sensors summary data be generated (with uptime, downtime and outages data)
     */
    public static function getAllSensors($detailed, $startDate, $endDate){
        $toReturn = array();

        $conn = PingdomStatus_DB::getConnection();
        $tableObject = $conn->getTable('PingdomPsSensor');

        // Cache sensor types
        $sensorTypes = $conn->getTable('PingdomPsType')->findAll();
        $sensorTypesById = array();
        foreach ($sensorTypes as $sensorType){
            $sensorTypesById[$sensorType->id] = $sensorType->value;
        }

	$sensors = $tableObject->findByDql("WHERE is_public = 1 ORDER BY name ASC");

	foreach($sensors as $sensor){
	    $newOne = new SensorSummaryData();
	    $newOne->id = $sensor->id;
	    $newOne->name = $sensor->name;
	    $newOne->type = $sensorTypesById[$sensor->type_id];
	    $newOne->isUp = ($sensor->current_state_id == SENSOR_STATE_UP_ID);
	    $toReturn[] = $newOne;
        }

        // Update with uptime data
        if($detailed){
        	PingdomStatus_Functions::getUptimeDataForSensors($toReturn, $startDate, $endDate);
        }

        return $toReturn;
    }

    /**
     * Returns array of GroupSummaryDataObjects
     *
     * @param bool $detailed should detailed sensors summary data be generated (with uptime, downtime and outages data)
     * @param bool $allSensors should we generate tree only for down servers or for all servers
     */
    public static function getGroupsTree($detailed, $allSensors, $startDate, $endDate){
        $conn = PingdomStatus_DB::getConnection();

        $toReturn = array();

        // Cache sensor types
        $sensorTypes = $conn->getTable('PingdomPsType')->findAll();
        $sensorTypesById = array();
        foreach ($sensorTypes as $sensorType){
            $sensorTypesById[$sensorType->id] = $sensorType->value;
        }

        // Get all sensor groups
        $allGroups = $conn->query("FROM PingdomPsGroup g LEFT JOIN g.SensorGroups sg LEFT JOIN sg.Sensor s ORDER BY g.id ASC");

        //
        //
        foreach($allGroups as $group){
            // Number of up and down sensors in this group
            $numOfDownSensors = 0;
            $numOfUpSensors = 0;

            $sensorList = array();
            $containsServers = false;
            foreach ($group->SensorGroups as $sensorGroup){
            	$containsServers = true;
                $sensor = $sensorGroup->Sensor;

                if($sensor->deleted_in_pingdom == 'YES')
                {
                    // Ignore sensors that have been deleted in Pingdom
                    continue;
                }

                $newSensor = new SensorSummaryData();
                $newSensor->id = $sensor->id;
                $newSensor->name = $sensor->name;
                $newSensor->type = $sensorTypesById[$sensor->type_id];

                // Is up?
                if($sensor->current_state_id == SENSOR_STATE_UP_ID){
                    $newSensor->isUp = true;
                    $numOfUpSensors++;
                }
                else {
                    $newSensor->isUp = false;
                    $numOfDownSensors++;
                }

                // We add this sensor if it is down, or if we need to add any sensors
                if($allSensors || $newSensor->isUp == false){
                    $sensorList[] = $newSensor;
                }
            }

            $newGroup = new GroupSummaryData();
            $newGroup->id = $group->id;
            $newGroup->name = $group->name;
            $newGroup->numOfDownSensors = $numOfDownSensors;
            $newGroup->numOfUpSensors = $numOfUpSensors;
            $newGroup->sensors = $sensorList;

            // Update with uptime data
            if($detailed){
                PingdomStatus_Functions::getUptimeDataForSensors($sensorList, $startDate, $endDate);
            }

            // Add only if group contains sensors
            if($containsServers){
            	$toReturn[] = $newGroup;
            }
        }
        return $toReturn;
    }

    /**
     * Gets status history for sensor (in specific time range)
     * Returns array of StatusHistoryRow objects.
     *
     * @param unknown_type $sensorId
     * @param unknown_type $startDate
     * @param unknown_type $endDate
     */
    public static function getStatusHistory($sensorId, $startDate, $endDate){
        $toReturn = array();

        $conn = PingdomStatus_DB::getConnection();

        // Get settings of minimum downtime period
        $settingsTable = $conn->getTable('PingdomPsSettings');
		$settingsArray = $settingsTable->findAll();
		$settings = $settingsArray[0];
		$toleranceSeconds = 60 * $settings->minimum_downtime_period;

        // Get latest state recorded for this sensor.
        $stateRecords = $conn->query(
"FROM PingdomPsState state
LEFT JOIN state.StatePosts statePosts
WHERE state.sensor_id = ?
    AND state.time_from <= ?
    AND state.time_to >= ?
    AND state.state_type_id = ?
ORDER BY state.time_from DESC", array((int)$sensorId, $endDate, $startDate, (int)SENSOR_STATE_DOWN_ID));

        foreach ($stateRecords as $stateRecord){
            $newOne = new StatusHistoryRow();
            $newOne->dateFrom = $stateRecord->time_from;
            $newOne->dateTo = $stateRecord->time_to;
            $newOne->spanDowntime = strtotime($stateRecord->time_to) -
                                    strtotime($stateRecord->time_from);

            if($newOne->spanDowntime >= $toleranceSeconds){
	            if($stateRecord->StatePosts != null && count($stateRecord->StatePosts) > 0){
	                $newOne->messageId = $stateRecord->StatePosts[0]->post_id;
	                $singlePost = wp_get_single_post($newOne->messageId);
	                $newOne->messageHeading = $singlePost->post_title;
	            }

	            $toReturn[] = $newOne;
            }
        }
        return $toReturn;
    }

    /**
     * @param arrayOfSensorSummaryData $sensors objects that needs to me updated with uptime data.
     * @param datetime $startDate Start date to generate data for.
     * @param datetime $endDate End data to generate data for
     */
    public static function getUptimeDataForSensors($sensors, $startDateParam, $endDateParam){
        global $wpdb;

        if(count($sensors) == 0){
            return;
        }

        // Get settings of minimum downtime period
        $conn = PingdomStatus_DB::getConnection();
        $settingsTable = $conn->getTable('PingdomPsSettings');
		$settingsArray = $settingsTable->findAll();
		$settings = $settingsArray[0];
		$toleranceSeconds = 60 * $settings->minimum_downtime_period;

        // Convert start and end date to timestamp
        $startDate = strtotime($startDateParam);
        $endDate = strtotime($endDateParam);

        //
        // Ask database
        $sensorIdsAsIn = "(";
        foreach($sensors as $sensor){
            $sensorIdsAsIn .= $sensor->id . ",";
        }
        $sensorIdsAsIn[strlen($sensorIdsAsIn) - 1] = ')';

        $downStateId = SENSOR_STATE_DOWN_ID;
        $unknownStateId = SENSOR_STATE_UNKNOWN_ID;
        $queryGetDowntime = "
SELECT
    sensor_id as sensor_id,
    count(time_from) as count,
    sum(timestampdiff(SECOND, time_from, time_to)) sum,
    min(time_from) as min,
    max(time_to) as max
FROM pingdom_ps_state
WHERE sensor_id IN $sensorIdsAsIn
    AND state_type_id = $downStateId
    AND time_from <= '$endDateParam'
    AND time_to >= '$startDateParam'
    AND timestampdiff(SECOND, time_from, time_to) >= $toleranceSeconds
GROUP BY sensor_id";
        $downtimeResults = $wpdb->get_results($queryGetDowntime);
        $downtimeResultsBySensorId = array();
        foreach($downtimeResults as $downtimeResult){
            $downtimeResultsBySensorId[$downtimeResult->sensor_id] = $downtimeResult;
        }


        $queryGetUnmonitoredTime = "
SELECT
    sensor_id as sensor_id,
    sum(timestampdiff(SECOND, time_from, time_to)) as sum,
    min(time_from) as min,
    max(time_to) as max
FROM pingdom_ps_state
WHERE sensor_id in $sensorIdsAsIn
    AND state_type_id =$unknownStateId
    AND time_from <= '$endDateParam'
    AND time_to >= '$startDateParam'
GROUP BY sensor_id";
        $unmonitoredResults = $wpdb->get_results($queryGetUnmonitoredTime);
        $unmonitoredResultsBySensorId = array();
        foreach($unmonitoredResults as $unmonitoredResult){
            $unmonitoredResultsBySensorId[$unmonitoredResult->sensor_id] = $unmonitoredResult;
        }

    	$queryGetMinsAndMaxs = "
SELECT
	sensor_id as sensor_id,
    min(time_from) as min,
    max(time_to) as max
FROM pingdom_ps_state
WHERE sensor_id in $sensorIdsAsIn
GROUP BY sensor_id";
        $minimaxResults = $wpdb->get_results($queryGetMinsAndMaxs);
        $minimaxResultsBySensorId = array();
        foreach( $minimaxResults as  $minimaxResult){
            $minimaxResultsBySensorId[$minimaxResult->sensor_id] = $minimaxResult;
        }

    	$queryGetAverageResponsetime = "
SELECT
	sensor_id as sensor_id,
	avg(average_responsetime) as responsetime
FROM pingdom_ps_responsetime
WHERE sensor_id in $sensorIdsAsIn
    AND day <= '$endDateParam'
    AND day >= '$startDateParam'
GROUP BY sensor_id";
        $averageResponseResults = $wpdb->get_results($queryGetAverageResponsetime);
	$averageResponseBySensorId = array();
        foreach( $averageResponseResults as $averageResponseResult){
            $averageResponseBySensorId[$averageResponseResult->sensor_id] = $averageResponseResult->responsetime;
        }

        //
        // For all sensors
        foreach ($sensors as $sensor){
            if(isset($downtimeResultsBySensorId[$sensor->id])){
            	$startDateForSensor = $startDate;
            	$endDateForSensor = $endDate;

                // Convert min and max downtime time of timechunks into timestamp
                $minimalDowntimeTime = strtotime($downtimeResultsBySensorId[$sensor->id]->min);
                $maximalDowntimeTime = strtotime($downtimeResultsBySensorId[$sensor->id]->max);

                // Convert min and max times into timestamps
                $minimalTime = strtotime($minimaxResultsBySensorId[$sensor->id]->min);
                $maximalTime = strtotime($minimaxResultsBySensorId[$sensor->id]->max);

                // Move start and end date to minimal and maximal times
                if($minimalTime > $startDateForSensor){
                    $startDateForSensor = $minimalTime;
                }
                if($maximalTime < $endDateForSensor){
                    $endDateForSensor = $maximalTime;
                }

                // Calculate total seconds.
                $totalSeconds = $endDateForSensor - $startDateForSensor;

                $monitoredTime = $totalSeconds;
                if(isset($unmonitoredResultsBySensorId[$sensor->id])){
                    // Convert min and max time of unmonitored timechunks into timestamp
                    $minimalUnmonitoredTime = strtotime($unmonitoredResultsBySensorId[$sensor->id]->min);
                    $maximalUnmonitoredTime = strtotime($unmonitoredResultsBySensorId[$sensor->id]->max);

                    $unmonitoredTime = $unmonitoredResultsBySensorId[$sensor->id]->sum;

                    $startOut = $startDateForSensor - $minimalUnmonitoredTime;
                    $endOut = $maximalUnmonitoredTime - $endDateForSensor;
                    if($startOut > 0){
                        $unmonitoredTime = $unmonitoredTime - $startOut;
                    }
                    if($endOut > 0){
                        $unmonitoredTime = $unmonitoredTime - $endOut;
                    }

                    $monitoredTime = $monitoredTime - $unmonitoredTime;
                }

                // Downtime is calculated downtime minus downtime that is outside selected range
                $calculatedDowntime = $downtimeResultsBySensorId[$sensor->id]->sum;
                $startOut = $startDateForSensor - $minimalDowntimeTime;
                $endOut = $maximalDowntimeTime - $endDateForSensor;
                if($startOut > 0){
                    $calculatedDowntime = $calculatedDowntime - $startOut;
                }
                if($endOut > 0){
                    $calculatedDowntime = $calculatedDowntime - $endOut;
                }

                //
                $sensor->downtime = $calculatedDowntime;
                $sensor->uptime = $monitoredTime != 0 ? 100 * (($monitoredTime - $sensor->downtime) / $monitoredTime) : 0;
                $sensor->outages = $downtimeResultsBySensorId[$sensor->id]->count;
            }
            else {
                $sensor->downtime = 0;
                $sensor->uptime = 100;
                $sensor->outages = 0;
            }

	    $sensor->average_responsetime = $averageResponseBySensorId[$sensor->id];
        }
    }

    /**
     * For each day between start and end data, calculates total downtime.
     * Returns an array where key is date (formated in mysql way) and value is total downtime seconds for that day
     *
     * @param int $sensorId
     * @param datetime $startDateParam
     * @param datetime $endDateParam
     */
    public static function getAggregatedUptimeDataForSensor($sensorId, $startDateParam, $endDateParam){
    	$toReturn = array();
    	$conn = PingdomStatus_DB::getConnection();

    	// Convert start and end date to timestamp
    	// Move start date and end date to the beginning and to the end of the day
        $startDate = strtotime(date("Y-m-d 00:00:00", strtotime($startDateParam)));
        $endDate = strtotime(date("Y-m-d 00:00:00", strtotime($endDateParam)));


        // Get settings of minimum downtime period
        $settingsTable = $conn->getTable('PingdomPsSettings');
		$settingsArray = $settingsTable->findAll();
		$settings = $settingsArray[0];
		$toleranceSeconds = 60 * $settings->minimum_downtime_period;

        // Get down states recorded for this sensor.
        $stateRecords = $conn->query(
"FROM PingdomPsState state
LEFT JOIN state.StatePosts statePosts
WHERE state.sensor_id = ?
    AND state.time_from <= ?
    AND state.time_to >= ?
ORDER BY state.time_from ASC", array((int)$sensorId, $endDateParam, $startDateParam));

        // Initialize array to be returned
        while($startDate <= $endDate){
        	$key = date("Y-m-d 00:00:00", $startDate);
        	$toReturn[$key] = null;

        	$toReturn[$key]->uptime = 0;
        	$toReturn[$key]->downtime = 0;
        	$toReturn[$key]->unmonitored = 0;
        	$startDate += 86400;
        }

        // Pass through all returned down states and increase appropriate element in resulting array
        foreach($stateRecords as $stateRecord){
        	$timeFrom = strtotime($stateRecord->time_from);
        	$timeTo = strtotime($stateRecord->time_to);

        	// Skip this time chunk if it is downtime smaller than tolerance
        	if(($timeTo - $timeFrom) < $toleranceSeconds && $stateRecord->state_type_id == SENSOR_STATE_DOWN_ID){
        		continue;
        	}

        	// Find start of the day for this $time_from
        	$startDay = strtotime(date("Y-m-d 00:00:00", $timeFrom));
        	$endDay = strtotime(date("Y-m-d 00:00:00", $timeTo));

        	while($startDay <= $endDay){
        		// Calculate how many seconds we are in this day
        		$currentDayStart = $startDay;
        		$currentDayEnd = $currentDayStart + 86400;

        		$outsideStart = $currentDayStart - $timeFrom;
        		$outsideEnd = $timeTo - $currentDayEnd;
        		$totalInDay = $timeTo - $timeFrom;
        		if($outsideStart > 0){
        			$totalInDay -= $outsideStart;
        		}
        		if($outsideEnd > 0){
        			$totalInDay -= $outsideEnd;
        		}

        		$key = date("Y-m-d 00:00:00", $currentDayStart);
        		if(isset($toReturn[$key])){
	        		switch($stateRecord->state_type_id){
	        			case SENSOR_STATE_DOWN_ID:
				        	$toReturn[$key]->downtime += $totalInDay;
	        				break;
	        			case SENSOR_STATE_UP_ID:
	        				$toReturn[$key]->uptime += $totalInDay;
	        				break;
	        			default:
				        	$toReturn[$key]->unmonitored += $totalInDay;
	        				break;
        			}
        		}

        		$startDay += 86400;
        	}
        }

        return $toReturn;
    }

    /**
     * For each day between start and end data, fetch average responsetime
     * Returns an array where key is date (formated in mysql way) and value is the average responsetime for that day
     *
     * @param int $sensorId
     * @param datetime $startDateParam
     * @param datetime $endDateParam
     */
    public static function getAggregatedResponsetimeDataForSensor($sensorId, $startDateParam, $endDateParam){
    	$toReturn = array();
    	$conn = PingdomStatus_DB::getConnection();

        $uptimeRows = $conn->query("FROM PingdomPsResponseTime WHERE sensor_id = ? AND day >= ? AND day <= ?", array((int) $sensorId, $startDateParam, $endDateParam));
		foreach ($uptimeRows as $dayrow) {
			$toReturn[$dayrow->day] = (int)$dayrow->average_responsetime;

		}
		// Fill out the blanks
		$startDay = strtotime($startDateParam);
       	$endDay = strtotime($endDateParam);

		while($startDay <= $endDay){
			$key = date("Y-m-d", $startDay);
			if(!isset($toReturn[$key])){
				$toReturn[$key] = -1;
			}
			$startDay += 86400;
		}
		uksort($toReturn, array("PingdomStatus_Functions", "getAggregatedResponsetimeDataForSensor_cmp"));
		return $toReturn;
    }

    private static function getAggregatedResponsetimeDataForSensor_cmp($a, $b){
    	$aSplit = preg_split("/\-/", $a);
    	$bSplit = preg_split("/\-/", $b);

    	$a = (int)$aSplit[2];
    	$b = (int)$bSplit[2];

	    if ($a === $b) {  return 0; }
	    return ($a < $b) ? -1 : 1;
    }
}
?>
