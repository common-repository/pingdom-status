<?php
require_once(ABSPATH . 'wp-content/plugins/pingdom-status/php/wsproxy/PingdomStatus_business.php');
require_once(ABSPATH . 'wp-content/plugins/pingdom-status/php/wsproxy/PingdomStatus_presentation_data.php');
require_once(ABSPATH . 'wp-content/plugins/pingdom-status/php/wsproxy/PingdomStatus_security.php');
require_once(ABSPATH . 'wp-content/plugins/pingdom-status/PingdomStatus_config.php');
require_once(ABSPATH . 'wp-content/plugins/pingdom-status/php/PingdomStatus_DB.php');


// Initialize instrumenter
class WebServiceHelper {
	static $WS_BUSINESS_OPTIONS = array();
	static $WS_PRESENTATION_DATA_OPTIONS = array();
	static $WS_SECURITY_OPTIONS = array();
	
	/**
	 * Authenticates user to pingdom web services and returns sessionId
	 *
	 * @param int $user_pk
	 * @return string sessionId
	 */
	public function authenticate()
	{
		//
		// Get user's username, password and apikey
		$conn = PingdomStatus_DB::getConnection();

		//
		// Get settings from pingdom status database
		$settingsTable = $conn->getTable('PingdomPsSettings');
		$settingsArray = $settingsTable->findAll();
		$settings = $settingsArray[0];
		
		//
		// Call login method of security web service
		
		// Prepare parameters for web service call
		$params = new Login();
		$params->apiKey = $settings->pingdom_api_key;
		$params->username = $settings->username;
		$params->password = $settings->password;

		// Call web service
		$response = new LoginResponse();
		try 
		{
			$webServiceProxy = new Security(WS_SECURITY_URL, WebServiceHelper::$WS_SECURITY_OPTIONS);
			$response = $webServiceProxy->Login($params);	
		}
		catch (SoapFault $ex)
		{
			WebServiceHelper::logWebServiceError(true, $params, $response, "authenticate", $ex);
			return "";
		}
		
		// If there is a authorization error
		if($response->LoginResult->Status != EWSStatusCodes::OK){
			WebServiceHelper::logWebServiceError(true, $params, $response, "authenticate", null);
			return "";
		}
		
		return $response->LoginResult->SessionId;
	}
	
	/**
	 * Write error about web service call
	 *
	 * @param string $request Request that caused the error
	 * @param string $response Response that is returned (if any)
	 * @param string $fromFunction Which function caused the error.
	 * @param SoapFault $exception Exception object
	 */
	static function logWebServiceError($doEcho, $request, $response, $fromFunction, $exception= null) {
		$toWrite = "Error in Pingdom web service call from function $fromFunction. Request: \n" . var_export($request, true);
		$toWrite .= "\n\nResponse:\n" . var_export($response, true);
		
		if(null != $exception) {
			$toWrite .= "\n\nException:\n" . $exception->getMessage();
		}
		
		// Somehow write to wordpress error log.
		if($doEcho){
			echo $toWrite;
		}
	}
}
?>