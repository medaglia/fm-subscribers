<?php
/* SVN FILE: $Id: cake_web_test_case.php 8207 2009-06-30 01:37:33Z mark_story $ */
/**
 * CakeWebTestCase a simple wrapper around WebTestCase
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) Tests <https://trac.cakephp.org/wiki/Developement/TestSuite>
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 *  Licensed under The Open Group Test Suite License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          https://trac.cakephp.org/wiki/Developement/TestSuite CakePHP(tm) Tests
 * @package       cake
 * @subpackage    cake.cake.tests.lib
 * @since         CakePHP(tm) v 1.2.0.4433
 * @version       $Revision: 8207 $
 * @modifiedby    $LastChangedBy: mark_story $
 * @lastmodified  $Date: 2009-06-29 20:37:33 -0500 (Mon, 29 Jun 2009) $
 * @license       http://www.opensource.org/licenses/opengroup.php The Open Group Test Suite License
 */
/**
 * Ignore base class.
 */
	SimpleTest::ignore('CakeWebTestCase');
/**
 * Simple wrapper for the WebTestCase provided by SimpleTest
 *
 * @package       cake
 * @subpackage    cake.cake.tests.lib
 */
class CakeWebTestCase extends WebTestCase {
}
?>