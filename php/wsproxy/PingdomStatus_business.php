<?php
class GetAllSensors {
	public $apiKey; // string
	public $sessionId; // string
}

class GetAllSensorsResponse {
  public $GetAllSensorsResult; // WSSensors
}

class WSSensors {
  public $Status; // EWSStatusCodes
  public $Sensors; // ArrayOfSensorInfo
}

class GetUserSettings {
	public $apiKey; // string
	public $sessionId; // string
}

class GetUserSettingsResponse {
  public $GetUserSettingsResult; // WSUserSettings
}

class WSUserSettings {
  public $Status; // EWSStatusCodes
  public $UserSettings; // UserSettings
}

class UserSettings {
  public $TimeZoneStandardName; // string
  public $TimezoneOffset; // int
  public $DateFormat; // string
  public $TimeFormat; // string
  public $NumberFormat;// string
}


/**
 * Business class
 * 
 *  
 * 
 * @author    Aleksandar Vucetic
 * @copyright Pingdom AB
 */
class Business extends SoapClient {

  private static $classmap = array(
                                    'GetAllSensors' => 'GetAllSensors',
                                    'GetAllSensorsResponse' => 'GetAllSensorsResponse',
                                    'WSSensors' => 'WSSensors',
                                    'EWSStatusCodes' => 'EWSStatusCodes',
                                    'SensorInfo' => 'SensorInfo',
                                    'GetUserSettings' => 'GetUserSettings',
                                    'GetUserSettingsResponse' => 'GetUserSettingsResponse',
                                    'WSUserSettings' => 'WSUserSettings',
                                    'UserSettings' => 'UserSettings'
                                   );

  public function Business($wsdl = "Business.wsdl", $options = array()) {
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
   * @param GetAllSensors $parameters
   * @return GetAllSensorsResponse
   */
  public function GetAllSensors(GetAllSensors $parameters) {
    return $this->__soapCall('GetAllSensors', array($parameters),       array(
            'uri' => 'http://pingdom.com/business/2008/04/09',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetUserSettings $parameters
   * @return GetUserSettingsResponse
   */
  public function GetUserSettings(GetUserSettings $parameters) {
    return $this->__soapCall('GetUserSettings', array($parameters),       array(
            'uri' => 'http://pingdom.com/business/2008/04/09',
            'soapaction' => ''
           )
      );
  }
  
}

?>
