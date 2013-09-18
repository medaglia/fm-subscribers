<?php 
/* SVN FILE: $Id$ */
/* Subscriber Fixture generated on: 2009-09-27 11:52:28 : 1254070348*/

class SubscriberFixture extends CakeTestFixture {
	var $name = 'Subscriber';
	var $table = 'subscribers';
	var $fields = array(
		'id' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 8, 'key' => 'primary'),
		'firstname' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 40),
		'lastname' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 60),
		'company' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 60),
		'email' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 120),
		'address' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 250),
		'postcode' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 40),
		'source' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 60),
		'issue_start' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 60),
		'issue_end' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 60),
		'last_issue_date' => array('type'=>'date', 'null' => true, 'default' => NULL),
		'indexes' => array()
	);
	var $records = array(array(
		'id'  => 1,
		'firstname'  => 'Lorem ipsum dolor sit amet',
		'lastname'  => 'Lorem ipsum dolor sit amet',
		'company'  => 'Lorem ipsum dolor sit amet',
		'email'  => 'Lorem ipsum dolor sit amet',
		'address'  => 'Lorem ipsum dolor sit amet',
		'postcode'  => 'Lorem ipsum dolor sit amet',
		'source'  => 'Lorem ipsum dolor sit amet',
		'issue_start'  => 'Lorem ipsum dolor sit amet',
		'issue_end'  => 'Lorem ipsum dolor sit amet',
		'last_issue_date'  => '2009-09-27'
	));
}
?>