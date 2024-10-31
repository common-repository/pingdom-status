<?php
class GetProbingLocations {
	public $apiKey; // string
	public $sessionId; // string
  public $sensorId; // int
  public $startTimeLocal; // dateTime
  public $endTimeLocal; // dateTime
}

class GetProbingLocationsResponse {
  public $GetProbingLocationsResult; // WSLocations
}

class WSLocations {
  public $Status; // EWSStatusCodes
  public $Locations; // ArrayOfLocation
}

class EWSStatusCodes {
  const OK = 'OK';
  const INVALID_ARGUMENT = 'INVALID_ARGUMENT';
  const INTERNAL_ERROR = 'INTERNAL_ERROR';
	const AUTHENTICATION_ERROR = 'AUTHENTICATION_ERROR';
    const AUTHORIZATION_ERROR = 'AUTHORIZATION_ERROR';
}

class Location {
  public $LocationId; // int
  public $Name; // string
  public $CountryName; // string
}

class GetCurrentStates {
	public $apiKey; // string
	public $sessionId; // string
  public $sensorIds; // ArrayOfInt
}

class GetCurrentStatesResponse {
  public $GetCurrentStatesResult; // WSCurrentStates
}

class WSCurrentStates {
  public $Status; // EWSStatusCodes
  public $Statuses; // ArrayOfSensorCurrentStatus
}

class ESensorStatus {
  const UP = 'UP';
  const DOWN = 'DOWN';
  const UNKNOWN = 'UNKNOWN';
}

class SensorCurrentStatus {
  public $SensorId; // int
  public $SensorStatus; // ESensorStatus
}

class GetSensorAverageResponseTimes {
	public $apiKey; // string
	public $sessionId; // string
  public $sensorId; // int
  public $resolution; // string
  public $startTimeLocal; // dateTime
  public $endTimeLocal; // dateTime
  public $locationsIds; // ArrayOfInt
}

class GetSensorAverageResponseTimesResponse {
  public $GetSensorAverageResponseTimesResult; // WSSensorAverageResponseTimes
}

class WSSensorAverageResponseTimes {
  public $Status; // EWSStatusCodes
  public $ResponseTimes; // SensorAverageResponseTimes
}

class SensorAverageResponseTimes {
  public $Resolution; // string
  public $AverageResponseTimes; // ArrayOfSensorAverageResponseTime
}

class SensorAverageResponseTime {
  public $StartTime; // dateTime
  public $AverageResponseTime; // float
}

class GetSensorAverageResponseTimesHourly {
	public $apiKey; // string
	public $sessionId; // string
  public $sensorId; // int
  public $startTimeLocal; // dateTime
  public $endTimeLocal; // dateTime
  public $locationsIds; // ArrayOfInt
}

class GetSensorAverageResponseTimesHourlyResponse {
  public $GetSensorAverageResponseTimesHourlyResult; // WSSensorAverageResponseTimesHourly
}

class WSSensorAverageResponseTimesHourly {
  public $Status; // EWSStatusCodes
  public $ResponseTimes; // SensorAverageResponseTimesHourly
}

class SensorAverageResponseTimesHourly {
  public $AverageResponseTimesHourly; // ArrayOfInt
}

class GetSensorsOverview {
	public $apiKey; // string
	public $sessionId; // string
	public $sensorIds; // ArrayOfInt
	public $startTimeLocal; // dateTime
	public $endTimeLocal; // dateTime
}

class GetSensorsOverviewResponse {
	public $GetSensorsOverviewResult; // WSSensorOverview
}

class WSSensorOverview {
	public $Status; // EWSStatusCodes
	public $SensorOverview; // ArrayOfSensorOverview
}

class SensorOverview {
	public $SensorId; //Int
	public $AverageResponseTime;//Int
	public $UptimePercentage;//Float
	public $Downtime;//Int
}

class GetSensorStatusChanges {
	public $apiKey; // string
	public $sessionId; // string
  public $sensorId; // int
  public $startTimeLocal; // dateTime
  public $endTimeLocal; // dateTime
  public $acceptingStatuses; // ArrayOfString
  public $firstResultIndex; // int
  public $numberOfResults; // int
  public $sortBy; // string
  public $sortOrder; // string
}

class GetSensorStatusChangesResponse {
  public $GetSensorStatusChangesResult; // WSSensorStatusChanges
}

class WSSensorStatusChanges {
  public $Status; // EWSStatusCodes
  public $Changes; // SensorStatusChanges
}

class SensorStatusChanges {
  public $TotalStatusChanges; // int
  public $StatusChanges; // ArrayOfSensorStatusRange
}

class SensorStatusRange {
  public $SensorStatus; // ESensorStatus
  public $StartTimeLocal; // dateTime
  public $EndTimeLocal; // dateTime
  public $Duration; // int
}

class GetSensorStateData {
  public $sensorId; // int
  public $resolution; // string
  public $startTimeLocal; // dateTime
  public $endTimeLocal; // dateTime
}

class GetSensorStateDataResponse {
  public $GetSensorStateDataResult; // WSSensorStatesData
}

class WSSensorStatesData {
  public $Status; // EWSStatusCodes
  public $States; // SensorStates
}

class SensorStates {
  public $Resolution; // string
  public $SensorPeriods; // ArrayOfSensorPeriod
}

class SensorPeriod {
  public $StartTimeLocal; // dateTime
  public $UptimeSeconds; // int
  public $DowntimeSeconds; // int
  public $UnknownSeconds; // int
}

class WSSensorReportsItems {
  public $Status; // EWSStatusCodes
  public $ReportItem; // ArrayOfSensorReportItem
}

class SensorReportItem {
  public $SensorInfo; // SensorInfo
  public $LastError; // dateTime
  public $LastProbing; // Probing
  public $CurrentStatus; // ESensorStatus
  public $CheckTypeName; // string
  public $Url; // string
  public $SensorPausedState; // SensorPausedState
}

class SensorInfo {
  public $SensorId; // int
  public $Name; // string
  public $CheckInterval; // int
  public $CreationTime; // dateTime
}

class Probing {
  public $TimeOfProbingLocal; // dateTime
  public $LocationOfProbing; // Location
}

class SensorPausedState {
  const PAUSED = 'PAUSED';
  const UNPAUSED = 'UNPAUSED';
}

class GetSensorsResponseStatistics {
	public $apiKey; // string
	public $sessionId; // string
  public $sensorIds; // ArrayOfInt
  public $startTimeLocalLocal; // dateTime
  public $endTimeLocalLocal; // dateTime
}

class GetSensorsResponseStatisticsResponse {
  public $GetSensorsResponseStatisticsResult; // WSSensorResponseStatistics
}

class WSSensorResponseStatistics {
  public $Status; // EWSStatusCodes
  public $ResponseStatistics; // ArrayOfSensorResponseStatistics
}

class SensorResponseStatistics {
  public $SensorId; // int
  public $SlowestResponseTime; // float
  public $FastestResponseTime; // float
  public $AverageResponseTime; // float
  public $SlowestResponseTimeMeasuredFrom; // Probing
  public $FastestResponseTimeMeasuredFrom; // Probing
}

class GetSensorsUptimeStatistics {
	public $apiKey; // string
	public $sessionId; // string
  public $sensorIds; // ArrayOfInt
  public $startTimeLocal; // dateTime
  public $endTimeLocal; // dateTime
}

class GetSensorsUptimeStatisticsResponse {
  public $GetSensorsUptimeStatisticsResult; // WSSensorUptimeStatistics
}

class WSSensorUptimeStatistics {
  public $Status; // EWSStatusCodes
  public $UptimeStatistics; // ArrayOfSensorUptimeStatistics
}

class SensorUptimeStatistics {
  public $SensorId; // int
  public $UptimePercentage; // float
  public $Downtime; // int
  public $LongestDowntime; // int
  public $ShortestDowntime; // int
  public $AverageDowntime; // int
  public $TotalNumberOfDowntimeOccurances; // int
}

/**
 * PresentationData class
 * 
 *  
 * 
 * @author    Aleksandar Vucetic
 * @copyright Pingdom AB
 */
class PresentationData extends SoapClient {

  private static $classmap = array(
                                    'GetProbingLocations' => 'GetProbingLocations',
                                    'GetProbingLocationsResponse' => 'GetProbingLocationsResponse',
                                    'WSLocations' => 'WSLocations',
                                    'EWSStatusCodes' => 'EWSStatusCodes',
                                    'Location' => 'Location',
  									'GetCurrentStates' => 'GetCurrentStates',
                                    'GetCurrentStatesResponse' => 'GetCurrentStatesResponse',
                                    'WSCurrentStates' => 'WSCurrentStates',
                                    'SensorCurrentStatus' => 'SensorCurrentStatus',
  									'ESensorStatus' => 'ESensorStatus',
                                    'GetSensorAverageResponseTimes' => 'GetSensorAverageResponseTimes',
                                    'GetSensorAverageResponseTimesResponse' => 'GetSensorAverageResponseTimesResponse',
                                    'WSSensorAverageResponseTimes' => 'WSSensorAverageResponseTimes',
                                    'SensorAverageResponseTimes' => 'SensorAverageResponseTimes',
                                    'SensorAverageResponseTime' => 'SensorAverageResponseTime',
                                    'GetSensorAverageResponseTimesHourly' => 'GetSensorAverageResponseTimesHourly',
                                    'GetSensorAverageResponseTimesHourlyResponse' => 'GetSensorAverageResponseTimesHourlyResponse',
                                    'WSSensorAverageResponseTimesHourly' => 'WSSensorAverageResponseTimesHourly',
                                    'SensorAverageResponseTimesHourly' => 'SensorAverageResponseTimesHourly',
  									'GetSensorsOverview' => 'GetSensorsOverview',
								  	'GetSensorsOverviewResponse' => 'GetSensorsOverviewResponse',
								  	'WSSensorOverview' => 'WSSensorOverview',
								  	'SensorOverview' => 'SensorOverview',
                                    'GetSensorStatusChanges' => 'GetSensorStatusChanges',
                                    'GetSensorStatusChangesResponse' => 'GetSensorStatusChangesResponse',
                                    'WSSensorStatusChanges' => 'WSSensorStatusChanges',
                                    'SensorStatusChanges' => 'SensorStatusChanges',
                                    'SensorStatusRange' => 'SensorStatusRange',
                                    'GetSensorStateData' => 'GetSensorStateData',
                                    'GetSensorStateDataResponse' => 'GetSensorStateDataResponse',
                                    'WSSensorStatesData' => 'WSSensorStatesData',
                                    'SensorStates' => 'SensorStates',
                                    'SensorPeriod' => 'SensorPeriod',
                                    'WSSensorReportsItems' => 'WSSensorReportsItems',
                                    'SensorReportItem' => 'SensorReportItem',
                                    'SensorInfo' => 'SensorInfo',
                                    'Probing' => 'Probing',
                                    'SensorPausedState' => 'SensorPausedState',
                                    'GetSensorsResponseStatistics' => 'GetSensorsResponseStatistics',
                                    'GetSensorsResponseStatisticsResponse' => 'GetSensorsResponseStatisticsResponse',
                                    'WSSensorResponseStatistics' => 'WSSensorResponseStatistics',
                                    'SensorResponseStatistics' => 'SensorResponseStatistics',
                                    'GetSensorsUptimeStatistics' => 'GetSensorsUptimeStatistics',
                                    'GetSensorsUptimeStatisticsResponse' => 'GetSensorsUptimeStatisticsResponse',
                                    'WSSensorUptimeStatistics' => 'WSSensorUptimeStatistics',
                                    'SensorUptimeStatistics' => 'SensorUptimeStatistics'
                                   );

  public function PresentationData($wsdl = "CheckData.wsdl", $options = array()) {
    foreach(self::$classmap as $key => $value) {
      if(!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    parent::__construct($wsdl, $options);
  }

  /**
   *  
   *
   * @param GetProbingLocations $parameters
   * @return GetProbingLocationsResponse
   */
  public function GetProbingLocations(GetProbingLocations $parameters) {
    return $this->__soapCall('GetProbingLocations', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }
  
  /**
   * 
   *
   * @param GetCurrentStates $parameters
   * @return GetCurrentStatesResponse
   */
  public function GetCurrentStates(GetCurrentStates $parameters) {
  	return $this->__soapCall('GetCurrentStates', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetSensorAverageResponseTimes $parameters
   * @return GetSensorAverageResponseTimesResponse
   */
  public function GetSensorAverageResponseTimes(GetSensorAverageResponseTimes $parameters) {
    return $this->__soapCall('GetSensorAverageResponseTimes', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetSensorAverageResponseTimesHourly $parameters
   * @return GetSensorAverageResponseTimesHourlyResponse
   */
  public function GetSensorAverageResponseTimesHourly(GetSensorAverageResponseTimesHourly $parameters) {
    return $this->__soapCall('GetSensorAverageResponseTimesHourly', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }
  
  /**
   *  
   *
   * @param GetSensorsOverview $parameters
   * @return GetSensorsOverviewResponse
   */
  public function GetSensorsOverview(GetSensorsOverview $parameters) {
  	return $this->__soapCall('GetSensorsOverview', array($parameters), array(
  			'uri' => 'http://pingdom.com/checkdata/2008/04/09',
  			'soapaction' => ''
  		)
  	);
  }

  /**
   *  
   *
   * @param GetSensorStatusChanges $parameters
   * @return GetSensorStatusChangesResponse
   */
  public function GetSensorStatusChanges(GetSensorStatusChanges $parameters) {
    return $this->__soapCall('GetSensorStatusChanges', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetSensorStateData $parameters
   * @return GetSensorStateDataResponse
   */
  public function GetSensorStateData(GetSensorStateData $parameters) {
    return $this->__soapCall('GetSensorStateData', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }


  /**
   *  
   *
   * @param GetSensorsResponseStatistics $parameters
   * @return GetSensorsResponseStatisticsResponse
   */
  public function GetSensorsResponseStatistics(GetSensorsResponseStatistics $parameters) {
    return $this->__soapCall('GetSensorsResponseStatistics', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetSensorsUptimeStatistics $parameters
   * @return GetSensorsUptimeStatisticsResponse
   */
  public function GetSensorsUptimeStatistics(GetSensorsUptimeStatistics $parameters) {
    return $this->__soapCall('GetSensorsUptimeStatistics', array($parameters),       array(
            'uri' => 'http://pingdom.com/checkdata/2008/04/09',
            'soapaction' => ''
           )
      );
  }
}

?>
