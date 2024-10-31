<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BasePingdomPsSensorGroup extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('pingdom_ps_sensor_group');
    $this->hasColumn('sensor_id', 'integer', 4, array('alltypes' =>  array(  0 => 'integer', ), 'ntype' => 'int(10) unsigned', 'unsigned' => 1, 'values' =>  array(), 'primary' => true, 'notnull' => true, 'autoincrement' => false));
    $this->hasColumn('group_id', 'integer', 4, array('alltypes' =>  array(  0 => 'integer', ), 'ntype' => 'int(10) unsigned', 'unsigned' => 1, 'values' =>  array(), 'primary' => true, 'notnull' => true, 'autoincrement' => false));
  }
  
  public function setUp(){		
	$this->hasOne('PingdomPsGroup as Group', array('local'=>'group_id', 'foreign'=>'id'));
	$this->hasOne('PingdomPsSensor as Sensor', array('local'=>'sensor_id', 'foreign'=>'id'));
  }


}