<?php
/**
 * Basic info about sensor.
 *
 */
class SensorBasicInfo{
	public $checkType;
	public $name;
	public $group;
	public $groupId;
	public $sensorId;
	
	public function SensorBasicInfo(){
		$this->checkType = "";
		$this->name = "";
		$this->group = "";
		$this->groupId = -1;
		$this->sensorId = -1;
	}
}
?>