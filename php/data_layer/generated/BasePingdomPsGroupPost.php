<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BasePingdomPsGroupPost extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('pingdom_ps_group_post');
    $this->hasColumn('group_id', 'integer', 4, array('alltypes' =>  array(  0 => 'integer', ), 'ntype' => 'int(10) unsigned', 'unsigned' => 1, 'values' =>  array(), 'primary' => true, 'notnull' => true, 'autoincrement' => false));
    $this->hasColumn('post_id', 'integer', 4, array('alltypes' =>  array(  0 => 'integer', ), 'ntype' => 'int(10) unsigned', 'unsigned' => 1, 'values' =>  array(), 'primary' => true, 'notnull' => true, 'autoincrement' => false));
    $this->hasColumn('message_status_id', 'integer', 4, array('alltypes' =>  array(  0 => 'integer', ), 'ntype' => 'int(10) unsigned', 'unsigned' => 1, 'values' =>  array(), 'primary' => true, 'notnull' => true, 'autoincrement' => false));
  }
  
  public function setUp(){
		$this->hasOne('PingdomPsMessageStatusType as Type', array('local'=>'message_status_id', 'foreign'=>'id'));
  }


}