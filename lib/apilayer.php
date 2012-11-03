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

# TODO: make sure arguments which are references remain passed by reference and do not become values, to avoid bugs

# NOTE: preRunProxies issue: certain methods underneath the abstraction layer need to pas vars by reference. Theses ares are part of the array handled by __call, however currently I'm unsure how to retain the reference during the passing of vars through the abstraction layer. error: '__call() cannot take arguments by reference' - but this relates to attempting to pass the entire array by reference, rather than the individual arguments. The arguements have already lost their references by the time they are accessible from within Anonymous->__call() - so how can we do anything about it? At least for the time being the abstraction layer can still be used in any circumstances where the method being accessed via the layer does not require vars by reference.

/**
 * Class for wrapping static classes as object variables.
 */
class Anonymous {
 
	private $className;
	
	function __construct( $className ) {
	
		$this->className = $className;
		
	}
	
	/**
	 * @brief Magic method for handling calls to methods
	 * @param  string $methodName name of the method called
	 * @param string $arguments arguments that were passed to the called method
	 * @return return value of the method called
	 */
	function __call( $methodName, $arguments ) {
	
		// Check if namespace identifier has been used, and if so,
		// substitute it for the real thing
		if ( preg_match ( '/__/', $this->className ) ) {
		
			$classnameF = preg_replace( '/__/', '\\', $this->className );
		
		} else {
		
			$classnameF = $this->className;
		
		}
		
		return call_user_func_array( $classnameF.'::'.$methodName, $arguments );
		
	}
}

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
class API {

	private $version;
	private $container = array();
	
	public function __construct( $version = '4.5' ){
		
		$this->version = $version;
		
		// Manually set the class names that won't be set by the 
		// autoloader
		// TODO: find a better way to set these core objects
		$apiV4_5 = array(
			'OC_Lib' => new Anonymous( 'OC_Lib' )
			, 'OC_Config' => new Anonymous( 'OC_Config' )
			, 'OC_Request' => new Anonymous( 'OC_Request' )
			, 'OC_Util' => new Anonymous( 'OC_Util' )
			, 'OC_DB' => new Anonymous( 'OC_DB' )
			, 'OC_Template' => new Anonymous( 'OC_Template' )
			, 'OC_Minimizer_CSS' => new Anonymous( 'OC_Minimizer_CSS' )
			, 'OC_Minimizer_JS' => new Anonymous( 'OC_Minimizer_JS' )
			, 'OC_App' => new Anonymous( 'OC_App' )
			, 'OC_Appconfig' => new Anonymous( 'OC_Appconfig' )
			, 'OC_Router' => new Anonymous( 'OC_Router' )
			, 'OC_User_Database' => new Anonymous( 'OC_User_Database' )
			, 'OC_Group_Database' => new Anonymous( 'OC_Group_Database' )
			, 'OC_BackgroundJob_RegularTask' => new Anonymous( 'OC_BackgroundJob_RegularTask' )
			, 'OC_Hook' => new Anonymous( 'OC_Hook' )
			, 'OC_Helper' => new Anonymous( 'OC_Helper' )
			, 'OC_Response' => new Anonymous( 'OC_Response' )
			, 'OC_Preferences' => new Anonymous( 'OC_Preferences' )
		);

		if ( $version == '4.5' ) {
		
			$this->container = $apiV4_5;
		
		}
	
	}
	
	/**
	 * @brief Get the API version being used
	 * @return string $version API version
	 */
	public function getVersion() {
	
		return $this->version;
	
	}
	
	/**
	 * @brief Magic method for handling returning of undefined properties
	 * @param string $className name of the 
	 * @return Anonymous Anonymous class wrapping specified class
	 */
	public function __get( $className ){
	
		# TODO: Consider parsing $className for namespaces, and somehow
		# manually applying them
	
		if ( array_key_exists( $className, $this->container ) ) {
			
			return $this->container[$className];
			
		} else {
			
			return new Anonymous( $className );
		
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
	public function registerObject( $key, $object ){

		$this->container[$key] = $object;
		
		return true;
		
	}
	
	/**
	 * @brief Simple getter to return objects array
	 * @return array $container Array of accessible objects
	 */
	public function getObjects(){

		return $this->container;
		
	}
	
}