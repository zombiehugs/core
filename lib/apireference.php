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

abstract class OC_ApiReference {

	// This will hold the version number of the API being implemented
	protected $version; // float
	
	// This will hold the manually set class names that won't be set 
	// by the class autoloader
	protected $stockClasses; // array

	/**
	 * @brief Get an API object
	 * @param string $version the key under which the api is registered
	 */
	public function getClasses() {
	
		return $this->stockClasses;
	
	}
	
	/**
	 * @brief Get an API object
	 * @param string $version the key under which the api is registered
	 */
	public function getVersion() {
	
		return $this->version;
	
	}

}