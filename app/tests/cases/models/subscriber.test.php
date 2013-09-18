<?php 
/* SVN FILE: $Id$ */
/* Subscriber Test cases generated on: 2009-09-27 11:52:28 : 1254070348*/
App::import('Model', 'Subscriber');

class SubscriberTestCase extends CakeTestCase {
	var $Subscriber = null;
	var $fixtures = array('app.subscriber');

	function startTest() {
		$this->Subscriber =& ClassRegistry::init('Subscriber');
	}

	function testSubscriberInstance() {
		$this->assertTrue(is_a($this->Subscriber, 'Subscriber'));
	}

	function testSubscriberFind() {
		$this->Subscriber->recursive = -1;
		$results = $this->Subscriber->find('first');
		$this->assertTrue(!empty($results));

		$expected = array('Subscriber' => array(
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
		$this->assertEqual($results, $expected);
	}
}
?>