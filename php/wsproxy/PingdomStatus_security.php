<?php
class Login {
	public $apiKey; // string
	public $username; // string
	public $password; // string
}

class LoginResponse {
	public $LoginResult; // WSLogin
}

class WSLogin {
	public $Status; // EWSStatusCodes
	public $SessionId; // string
}

class Logout {
	public $apiKey; // string
	public $sessionId; // string
}

class LogoutResponse {
	public $LogoutResult; // WSLogout
}

class WSLogout {
	public $Status; // EWSStatusCodes
}


/**
 * Security class
 *
 *
 *
 * @author    Aleksandar Vucetic
 * @copyright Pingdom AB
 */
class Security extends SoapClient {

	private static $classmap = array(
                                    'Login' => 'Login',
                                    'LoginResponse' => 'LoginResponse',
                                    'WSLogin' => 'WSLogin',
                                    'EWSStatusCodes' => 'EWSStatusCodes',
                                    'Logout' => 'Logout',
                                    'LogoutResponse' => 'LogoutResponse',
                                    'WSLogout' => 'WSLogout'
                                    );

                                    public function Business($wsdl = "Security.wsdl", $options = array()) {
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
                                     * @param Login $parameters
                                     * @return LoginResponse
                                     */
                                    public function Login(Login $parameters) {
                                    	return $this->__soapCall('Login', array($parameters),       array(
            'uri' => 'http://pingdom.com/security/2008/04/09',
            'soapaction' => ''
            )
            );
                                    }

                                    /**
                                     *
                                     *
                                     * @param Logout $parameters
                                     * @return LogoutResponse
                                     */
                                    public function Logout(Logout $parameters) {
                                    	return $this->__soapCall('Logout', array($parameters),       array(
            'uri' => 'http://pingdom.com/security/2008/04/09',
            'soapaction' => ''
            )
            );
                                    }

}

?>
