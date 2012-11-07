<?php

/**
 * ownCloud
 *
 * @author Sam Tuke
 * @copyright 2012 Sam Tuke <samtuke@owncloud.com>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * Class that provides an interface object for accessing OC classes. 
 * 
 * Multiple API objects can be used simultaneously and act as a compatibility 
 * layer.
 * 
 * One API object, containing the API for the running version of ownCloud, is
 * instantiated during bootstrap (base.php), and available to all classes as
 * OC::$api .
 *
 * When classes are used for the first time and autoloaded, they are 
 * automatically added to this object and can be accessed via it.
 */
class OC_ApiLayer {

	private $version;
	private $container = array();
	
	public function __construct( OC_ApiReference $apiStockClasses ) {
		
		// Get the version number of the API we're 
		// implementing
		$this->version = $apiStockClasses->getVersion();
		
		// Get the stock classes for this version number
		$classes = $apiStockClasses->getClasses();
		
		// Initialise the temporary api array
		$container = array();
		
		// Wrap each class name in a closure to return an object when 
		// necessary
		foreach ( $classes as $key => $value ) {
		
			$container['$key'] = function () { return new OC_Anonymous( $value ); };
		
		}
		
		$this->container = $container;
	
	}
	
	/**
	 * @brief Return the version number of the implemented API
	 * @return string $this->version API version
	 */
	public function getVersion() {
	
		return $this->version;
	
	}
	
	/**
	 * @brief Magic method for handling returning of undefined properties
	 * @param string $className name of the 
	 * @return Anonymous Anonymous class wrapping specified class
	 */
	public function __get( $className ) {
	
		// If the object key is already registered, return it. Else
		// assume that the referenced class has the same name as the
		// key and return a new Anonymous object using that.
		if ( array_key_exists( $className, $this->container ) ) {
			
			return $this->container[$className];
			
		} else {
			
			return new OC_Anonymous( $className );
		
		}
		
	}
	
	/**
	 * @brief Register additional classes with the API container
	 * @param string $key reference name of the object
	 * @param object object corresponding to $key
	 * @return bool true
	 * @note $key must be consistent between all API versions, but 
	 * differing objects can be set to $object as necessary
	 */
	public function registerObject( $key, $object ) {

		$this->container[$key] = function () { return new OC_Anonymous( $object ); };
		
		return true;
		
	}
	
	/**
	 * @brief Simple getter to return objects array
	 * @return array $container Array of accessible objects
	 */
	public function getObjects() {

		return $this->container;
		
	}
	
}