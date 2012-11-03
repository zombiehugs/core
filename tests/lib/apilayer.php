<?php
/**
 * Copyright (c) 2012 Sam Tuke <samtuke@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */



require_once "PHPUnit/Framework/TestCase.php";
require_once realpath( dirname(__FILE__).'/../../lib/base.php' );

use OCA\Encryption;

class Test_Apilayer extends \PHPUnit_Framework_TestCase {
	
	function setUp() {
	
	}
	
	function tearDown(){}

	function testgetVersion() {
	
		$api = new apilayer( '4.5' );
		
		$version = $api->getVersion();
		
		$this->assertEquals( '4.5', $version );
	
	}
	
}