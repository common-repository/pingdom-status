<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BasePingdomPsStateType extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('pingdom_ps_state_type');
    $this->hasColumn('id', 'integer', 4, array('alltypes' =>  array(  0 => 'integer', ), 'ntype' => 'int(10) unsigned', 'unsigned' => 1, 'values' =>  array(), 'primary' => true, 'notnull' => true, 'autoincrement' => false));
    $this->hasColumn('value', 'string', 10, array('alltypes' =>  array(  0 => 'string', ), 'ntype' => 'varchar(10)', 'fixed' => false, 'values' =>  array(), 'primary' => false, 'notnull' => true, 'autoincrement' => false));
  }


}