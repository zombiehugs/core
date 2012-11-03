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
	
		$this->api = new API( '4.5' );
	
	}
	
	function tearDown(){}

	function testgetVersion() {
		
		$version = $this->api->getVersion();
		
		$this->assertEquals( '4.5', $version );
	
	}
	
	function testregisterObject() {
	
// 		// Create a Mock Object for the Observer class
// 		// mocking only the update() method.
// 		$observer = $this->getMock( 'testObject', array( 'testMethod' ) );
// 	
// 		// Set up the expectation for the update() method
// 		// to be called only once and with the string 'something'
// 		// as its parameter.
// 		$observer->expects( $this->once() )
// 			->method( 'testMethod' )
// 			->with($this->equalTo( 'testString' ) );
// 	
// 		// Create a Subject object and attach the mocked
// 		// Observer object to it.
// 		$subject = new Subject;
// 		$subject->attach( $observer );
// 	
// 		// Call the doSomething() method on the $subject object
// 		// which we expect to call the mocked Observer object's
// 		// update() method with the string 'something'.
// 		$subject->doSomething();
	
		$this->api->registerObject( 'obName', new Anonymous( 'obName' ) );
		
		$container = $this->api->getObjects();
		
		$this->assertEquals( 'Anonymous', get_class( $container['obName'] ) );
		
		$this->assertFalse( 'Anonymous' == get_class( $container['NonObName'] ) );
	
	}
	
	function testreturnMethod() {
	
		$this->assertEquals( 15, strlen( $this->api->PasswordHash->get_random_bytes( 15 ) ) );
	
	}
	
	function testreturnNamespacedMethod() {
	
		$this->assertEquals( 15, $this->api->OCP__App->isEnabled( 'files_archive' ) );
		
	}
	
}