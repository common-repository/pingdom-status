<?php
require_once(dirname(__FILE__)."/../../PingdomStatus_constants.php");
require_once(PINGDOM_PLUGIN_PATH . "/php/wsproxy/PingdomStatus_wsmain.php");
require_once(PINGDOM_PLUGIN_PATH . "/php/PingdomStatus_DB.php");

ini_set("soap.wsdl_cache_enabled", "0");

/**
 * Functions for performing synchronization between pingdom database and pingdomStatus database.
 *
 */
class PingdomDataProvider{
	/**
	 * If sensors_sync_period is expired, it looks into pingdom database,
	 * gets new sensors from there and saves them into pingdom_ps_sensor table.
	 * Sensors that do not exist in pingdom any more, are also marked by setting deleted_from_pingdom column to 'YES'.
	 *
	 * @return array of PingdomPsSensor
	 */
	public static function performSensorsSync($force = false){
		$conn = PingdomStatus_DB::getConnection();

		//
		// Get settings from pingdom status database
		$settingsTable = $conn->getTable('PingdomPsSettings');
		$settingsArray = $settingsTable->findAll();
		$settings = $settingsArray[0];

		// We need a doctrine sensor object, too
		$sensorObj = $conn->getTable('PingdomPsSensor');

		//
		// Figure out if we need to resync sensors
		$resync = false;
		if($force || mktime() > $settings->next_sensors_sync_time){
			$resync = true;
		}

		if($resync){
			try{
				// Create web service object
				$webService = new Business(WS_BUSINESS_URL, WebServiceHelper::$WS_BUSINESS_OPTIONS);

				//
				// Get all existing sensors from pingdom status database
				$allSensors = $sensorObj->findAll();

				//
				// Authenticate pingdom user.
				$sessionId = WebServiceHelper::authenticate();

				//
				// Get all existing sensors from pingdom database
				$getAllSensorsParams = new GetAllSensors();
				$getAllSensorsParams->apiKey = $settings->pingdom_api_key;
				$getAllSensorsParams->sessionId = $sessionId;
				$getAllSensorsResponse = $webService->GetAllSensors($getAllSensorsParams);
				if($getAllSensorsResponse->GetAllSensorsResult->Status == EWSStatusCodes::OK){
					$sensorsFromPingdom = $getAllSensorsResponse->GetAllSensorsResult->Sensors->SensorInfo;

					if (!is_array($sensorsFromPingdom)) {
						$sensorsFromPingdom = array($sensorsFromPingdom);
					}

					// Add new sensors from pingdom
					foreach($sensorsFromPingdom as $sensorFromPingdom){
						// If we do not have this sensor among our sensors, then we need to add a new one
						$exists = false;
						foreach($allSensors as $sensor){
							if($sensor->pingdom_sensor_id == $sensorFromPingdom->SensorId){
								$exists = true;
								break;
							}
						}

						if(!$exists){
							$newSensor = new PingdomPsSensor();
							$newSensor->pingdom_sensor_id = $sensorFromPingdom->SensorId;
							$newSensor->current_state_id = 1;
							$newSensor->type_id = $sensorFromPingdom->SensorTypeId;
							$newSensor->name = $sensorFromPingdom->Name;
							$newSensor->target = $sensorFromPingdom->Hostname;
							$newSensor->ip = '';
							$newSensor->is_public = 0;
							$newSensor->deleted_in_pingdom = 'NO';
							$newSensor->next_state_update_time = 0;
							$newSensor->next_ip_resolving_time = 0;
							$newSensor->last_detected_down = 0;


							$newSensor->save();

							PingdomDataProvider::log("Added new sensor with pingdom_sensor_id=".$newSensor->pingdom_sensor_id . "\r\n");
						}
					}

					// Remove sensors that do not exist in pingdom any more
					foreach($allSensors as $sensor){
						$exists = false;
						foreach($sensorsFromPingdom as $sensorFromPingdom){
							if($sensor->pingdom_sensor_id == $sensorFromPingdom->SensorId){
								$exists = true;
								break;
							}
						}

						// If sensor doesn't exist in pingdom database, mark it here as removed
						if(!$exists){
							$sensor->deleted_in_pingdom = 'YES';
							$sensor->save();

							PingdomDataProvider::log("Deleted sensor with pingdom_sensor_id=".$sensor->pingdom_sensor_id . "\r\n");
						}
					}
				}

				$settings->next_sensors_sync_time = mktime() + $settings->sensors_sync_period;
				$settings->save();
			}
			catch(Exception $ex){
				PingdomDataProvider::log("Error refreshing sensors: " . $ex->getMessage());
			}
		}
	}

	/**
	 * Performs ip resolving for all public sensors
	 *
	 */
	public static function performIpResolving($force = false){
		$conn = PingdomStatus_DB::getConnection();

		//
		// Get settings from pingdom status database
		$settingsTable = $conn->getTable('PingdomPsSettings');
		$settingsArray = $settingsTable->findAll();
		$settings = $settingsArray[0];

		// We need a doctrine sensor object, too
		$sensorObj = $conn->getTable('PingdomPsSensor');

		// Get all public sensors
		$sensors = $sensorObj->findByDql("is_public = 1");

		//
		// For each sensor figure out if we need to resolve the ip
		foreach($sensors as $sensor){
			$resolve = false;
			if($force || mktime() > $sensor->next_ip_resolving_time){
				$resolve = true;
			}
			if($resolve){
				try{
					//
					// Save next resolve time and prevent concurrent resolving
					$sensor->next_ip_resolving_time = mktime() + $settings->ip_resolving_period;

					// Resolve ip
					$sensor->ip = gethostbyname(trim($sensor->target));

					// Save sensor
					$sensor->save();

					PingdomDataProvider::log("Resolving performed for sensor with id: " . $sensor->id);
				}
				catch(Exception $ex){
					PingdomDataProvider::log("Error resolving ips: " . $ex->getMessage());
				}
			}
		}
	}

	/**
	 * Performs state synchronization for all public sensors
	 */
	public static function performStateSync($force = false){
		$conn = PingdomStatus_DB::getConnection();

		//
		// Get settings from pingdom status database
		$settingsTable = $conn->getTable('PingdomPsSettings');
		$settingsArray = $settingsTable->findAll();
		$settings = $settingsArray[0];
		$toleranceSeconds = 60 * $settings->minimum_downtime_period;

		// We need a doctrine sensor object, too
		$sensorObj = $conn->getTable('PingdomPsSensor');

		// Get all public sensors that are not deleted from pingdom
		$sensors = $sensorObj->findByDql("is_public = 1 AND deleted_in_pingdom = 'NO' ");

		$sessionId = null;
		$webService = null;

		//
		// For each sensor figure out if we need to get new status
		foreach($sensors as $sensor){
			$updateStatus = false;
			if($force || mktime() > $sensor->next_state_update_time){
				$updateStatus = true;
			}
			if($updateStatus){
				if ($sessionId == null) {
					// Authenticate pingdom user.
					$sessionId = WebServiceHelper::authenticate();
				}

				if ($webService == null) {
					// Create web service object
					$webService = new PresentationData(WS_PRESENTATION_DATA_URL, WebServiceHelper::$WS_PRESENTATION_DATA_OPTIONS);
				}

				try{
					$conn->beginTransaction();

					// Set next state update time
					$sensor->next_state_update_time = mktime() + $settings->state_update_period;

					//
					// Call web service to get the sensor's latest state
					$getCurrentStatesRequest = new GetCurrentStates();
					$getCurrentStatesRequest->apiKey = $settings->pingdom_api_key;
					$getCurrentStatesRequest->sessionId = $sessionId;
					$getCurrentStatesRequest->sensorIds = array();
					$getCurrentStatesRequest->sensorIds[0] = $sensor->pingdom_sensor_id;
					$getCurrentStatesResponse = $webService->GetCurrentStates($getCurrentStatesRequest);
					if($getCurrentStatesResponse->GetCurrentStatesResult->Status == EWSStatusCodes::OK){
						$state_id = PingdomDataProvider::getStateIdFromName($getCurrentStatesResponse->GetCurrentStatesResult->Statuses->SensorCurrentStatus->SensorStatus);

						// If it is down state, then it needs to be checked if passed enough time to be marked as real down
						if($state_id == SENSOR_STATE_DOWN_ID){
							if($sensor->last_detected_down == 0){
								// First down detection after some UP or UNKNOWN. Do not change sensor state
								$sensor->last_detected_down = mktime();
							}
							else{
								// If enough time passed, make it down, otherwise, nothing
								$span = mktime() - $sensor->last_detected_down;
								if($span >= $toleranceSeconds){
									$sensor->current_state_id = $state_id;
								}
							}
						}
						else{
							$sensor->current_state_id = SENSOR_STATE_UP_ID;
							$sensor->last_detected_down = 0;
						}

						// Save sensor
						$sensor->save();
						PingdomDataProvider::log("Current state refreshed for sensor with id=" . $sensor->id);
					}
					else{
						PingdomDataProvider::log("Failed getting current state for sensor with id=" . $sensor->id . " Response: " . var_export($getCurrentStatesResponse, true));
					}

					//
					// Now, update sensor's state history

					// Get latest state recorded for this sensor.
					$lastStateRecord = $conn->queryOne("FROM PingdomPsState WHERE PingdomPsState.sensor_id = ? ORDER BY PingdomPsState.time_from DESC", array($sensor->id));
					$startDate = '1970-01-01T00:00:00';
					if($lastStateRecord != null){
						$startDate = $lastStateRecord->time_to;
					}

					// Retrieve status changes related to this sensor, from pingdom aggregation database, starting from date which is the same as latest recorded date for this sensor
					$paramsStatusChanges = new GetSensorStatusChanges();
					$paramsStatusChanges->apiKey = $settings->pingdom_api_key;
					$paramsStatusChanges->sessionId = $sessionId;
					$paramsStatusChanges->startTimeLocal = date("Y-m-d\TH:i:s", strtotime($startDate));
					$paramsStatusChanges->endTimeLocal = date("Y-m-d\TH:i:s", mktime() + 86400); // I added one day, just not to bother with time zones. This will get all the changes, for sure
					$paramsStatusChanges->acceptingStatuses = array("UP","DOWN","UNKNOWN");
					$paramsStatusChanges->firstResultIndex = 0;
					$paramsStatusChanges->numberOfResults = 1000000; // TODO: This is a hack...it is considered that we will not have so much state changes
					$paramsStatusChanges->sensorId = $sensor->pingdom_sensor_id;
					$paramsStatusChanges->sortBy = "START_TIME";
					$paramsStatusChanges->sortOrder = 'ASC';
					$responseStatusChanges = $webService->GetSensorStatusChanges($paramsStatusChanges);

					if($responseStatusChanges->GetSensorStatusChangesResult->Status == EWSStatusCodes::OK){
						$statusChangesFromServer = PingdomDataProvider::getAsArray($responseStatusChanges->GetSensorStatusChangesResult->Changes->StatusChanges->SensorStatusRange);
						$countFromServer = count($statusChangesFromServer);
						if( $countFromServer > 0 ){
							// Update latest state recorded with the first end date that we got (if there is any)
							$inserted = false;
							if($lastStateRecord != null && $lastStateRecord->state_type_id == PingdomDataProvider::getStateIdFromName($statusChangesFromServer[0]->SensorStatus)){
								$lastStateRecord->time_to = $statusChangesFromServer[0]->EndTimeLocal;
								$lastStateRecord->save();
								$inserted = true;
							}

							// Insert all states retrived from pingdom aggregation database (except the first one, from which latest state recorded is updated)
							$startId = $inserted ? 1 : 0;
							if($countFromServer > $startId){
								for ($i = $startId; $i < $countFromServer; $i++){
									$newState = new PingdomPsState();
									$newState->sensor_id = $sensor->id;
									$newState->state_type_id = PingdomDataProvider::getStateIdFromName($statusChangesFromServer[$i]->SensorStatus);
									$newState->time_from = $statusChangesFromServer[$i]->StartTimeLocal;
									$newState->time_to = $statusChangesFromServer[$i]->EndTimeLocal;
									$newState->save();
								}
							}
						}
						PingdomDataProvider::log("State history refreshed for sensor with id=" . $sensor->id);
					}
					else{
						PingdomDataProvider::log("Failed getting state history for sensor with id=" . $sensor->id . " Response: " . var_dump($responseStatusChanges, true));
					}

					//
					// Now, update sensor's uptime history

					// Get latest state recorded for this sensor.
					$lastRespTimeRecord = $conn->queryOne("FROM PingdomPsResponseTime WHERE PingdomPsResponseTime.sensor_id = ? ORDER BY PingdomPsResponseTime.day DESC", array($sensor->id));
					$startDate = '1970-01-01';
					if($lastRespTimeRecord != null){
						$startDate = $lastRespTimeRecord->day;
					}

					// Retrieve status changes related to this sensor, from pingdom aggregation database, starting from date which is the same as latest recorded date for this sensor
					$paramsResponseTimes = new GetSensorAverageResponseTimes();
					$paramsResponseTimes->apiKey = $settings->pingdom_api_key;
					$paramsResponseTimes->sessionId = $sessionId;
					$paramsResponseTimes->sensorId = $sensor->pingdom_sensor_id;
					$paramsResponseTimes->resolution = "DAILY";
					$paramsResponseTimes->startTimeLocal = date("Y-m-d\TH:i:s", strtotime($startDate));
					$paramsResponseTimes->endTimeLocal = date("Y-m-d\TH:i:s", mktime() + 86400); // I added one day, just not to bother with time zones. This will get all the changes, for sure
					$responseResponseTimes = $webService->GetSensorAverageResponseTimes($paramsResponseTimes);

					if($responseResponseTimes->GetSensorAverageResponseTimesResult->Status == EWSStatusCodes::OK){
						$responseTimesFromServer = PingdomDataProvider::getAsArray($responseResponseTimes->GetSensorAverageResponseTimesResult->ResponseTimes->AverageResponseTimes->SensorAverageResponseTime);
						$countFromServer = count($responseTimesFromServer);
						if( $countFromServer > 0 ){
							foreach ($responseTimesFromServer as $responseTimeFetched) {
								if ($lastRespTimeRecord != null && PingdomDataProvider::timeToDate($responseTimeFetched->StartTime) == $lastRespTimeRecord->day) {
									$lastRespTimeRecord->average_responsetime = $responseTimeFetched->AverageResponseTime / 1000;
									$lastRespTimeRecord->save();
									PingdomDataProvider::log("Updated responsetime for " . $lastRespTimeRecord->day);
								} else {
									$newRespTime = new PingdomPsResponseTime();
									$newRespTime->sensor_id = $sensor->id;
									$newRespTime->day = PingdomDataProvider::timeToDate($responseTimeFetched->StartTime);
									$newRespTime->average_responsetime = $responseTimeFetched->AverageResponseTime / 1000;
									$newRespTime->save();
								}
							}
						}
						PingdomDataProvider::log("Responsetime history refreshed for sensor with id=" . $sensor->id);
					} else {
						PingdomDataProvider::log("Failed getting reponsetime history for sensor with id=" . $sensor->id . " Response: " . var_dump($responseResponseTimes, true));
					}

					$conn->commit();
				}
				catch(Exception $ex){
					PingdomDataProvider::log("Error gettting sensor states: " . $ex->getMessage());
					$conn->rollback();
				}
			}
		}

		return $sensors;
	}

	private static function timeToDate($datetime) {
		return date("Y-m-d", strtotime($datetime));
	}

	/**
	 * For specified ESensorStatus, return appropriate sensor_state_id
	 *
	 */
	private static function getStateIdFromName($state){
		$toReturn = SENSOR_STATE_UNKNOWN_ID;
		if($state == "UP"){
			$toReturn = SENSOR_STATE_UP_ID;
		}
		else if($state == "DOWN"){
			$toReturn = SENSOR_STATE_DOWN_ID;
		}
		return $toReturn;
	}

	/**
	 * Converts input to array (if it is not already an array)
	 *
	 * @param unknown_type $mixed
	 */
	private static function getAsArray($mixed){
		if($mixed == null){
			return array();
		}
		else if(!is_array($mixed)){
			return array($mixed);
		}
		else{
			return $mixed;
		}
	}

	/**
	 * Writes to output file
	 *
	 * @param unknown_type $text
	 */
	private static function log($text){
		$date = date('l dS \of F Y h:i:s A');
		if(file_exists("../../../synclog.txt") && is_writeable("../../../synclog.txt")){
			file_put_contents("../../../synclog.txt", "\n$date -> $text", FILE_APPEND);
		}
		echo "\n$date -> $text";
	}


}
?>
