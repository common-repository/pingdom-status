<?php
require_once(PINGDOM_PLUGIN_PATH . "/php/doctrine/Doctrine.php");

// Initialize doctrine
spl_autoload_register(array('Doctrine', 'autoload'));
spl_autoload_register(array('Doctrine_Core', 'modelsAutoload'));
Doctrine::loadModels(PINGDOM_PLUGIN_PATH .'/php/data_layer', Doctrine::MODEL_LOADING_CONSERVATIVE);

class PingdomStatus_DB{	
	/**
	 * Gets connection string for wordpress database.
	 *
	 * @return unknown
	 */
	static function getConnString(){
		return 'mysql://'. DB_USER . ':' . DB_PASSWORD .'@' . DB_HOST. '/' . DB_NAME;
	}
	
	/**
	 * Creates now connection for Doctrine and returns it.
	 *
	 * @return unknown
	 */
	static function getConnection(){
		return Doctrine_Manager::connection(PingdomStatus_DB::getConnString());
	}
}
?>
