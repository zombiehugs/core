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
 
 class ApiV4_5 extends OC_ApiReference {

	protected $version = 4.5;
	
	protected $stockClasses = array(
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

}