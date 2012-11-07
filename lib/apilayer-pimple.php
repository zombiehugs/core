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
 * Factory for getting and storing api versions of owncloud
 */
class APIFactory {

	private static $registeredAPIs = array();


	/**
	 * @brief Register an API object
	 * @param Pimple $api a pimple container with preset classes and shared objects
	 * @param string $version the version under which the container is stored and
	 *                        accessed
	 */
	public static function registerAPI($api, $version){
		self::$registeredAPIs[$version] = $api;
	}


	/**
	 * @brief Get an API object
	 * @param string $version the key under which the api is registered
	 */
	public static function getAPI($version){
	
		return self::$registeredAPIs[$version];
	
	}

}


// this has to be included in some bootloader class that pushes the different
// api layers
$apiV4_5 = new Pimple();

$apiLayerV4_5 = array(
	'OC_Lib' => 'OC_Lib'
	, 'OC_Config' => 'OC_Config'
	, 'OC_Request' => 'OC_Request'
	, 'OC_Util' => 'OC_Util'
	, 'OC_DB' => 'OC_DB'
	, 'OC_Template' => 'OC_Template'
	, 'OC_Minimizer_CSS' => 'OC_Minimizer_CSS'
	, 'OC_Minimizer_JS' => 'OC_Minimizer_JS'
	, 'OC_App' => 'OC_App'
	, 'OC_Appconfig' => 'OC_Appconfig'
	, 'OC_Router' => 'OC_Router'
	, 'OC_User_Database' => 'OC_User_Database'
	, 'OC_Group_Database' => 'OC_Group_Database'
	, 'OC_BackgroundJob_RegularTask' => 'OC_BackgroundJob_RegularTask'
	, 'OC_Hook' => 'OC_Hook'
	, 'OC_Helper' => 'OC_Helper'
	, 'OC_Response' => 'OC_Response'
	, 'OC_Preferences' => 'OC_Preferences'
);

foreach ( $apiLayerV4_5 as $key => $className ) {

	$api[$key] = $api->share( function ( $c ) {
	
		return new Anonymous( $className );
	
	});
	
}

// Register the 4.5 api with the APIFactory
APIFactory::registerAPI( $apiV4_5, '4.5' );