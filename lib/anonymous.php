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

/**
 * Class for wrapping static classes as object variables.
 */
class OC_Anonymous {
 
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