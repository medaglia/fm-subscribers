<?php 
/* SVN FILE: $Id$ */
/* SubscribersController Test cases generated on: 2009-09-27 12:07:56 : 1254071276*/
App::import('Controller', 'Subscribers');

class TestSubscribers extends SubscribersController {
	var $autoRender = false;
}

class SubscribersControllerTest extends CakeTestCase {
	var $Subscribers = null;

	function startTest() {
		$this->Subscribers = new TestSubscribers();
		$this->Subscribers->constructClasses();
	}

	function testSubscribersControllerInstance() {
		$this->assertTrue(is_a($this->Subscribers, 'SubscribersController'));
	}

	function endTest() {
		unset($this->Subscribers);
	}
}
?>