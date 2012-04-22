<?php
/**
 * ownCloud
 *
 * @author Georg Ehrke
 * @copyright 2012 Georg Ehrke <georg@owncloud.com>
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
class OC_Validator{
	/*
	 * @brief validates an url, if fix is true it tries to fix the url
	 * @param (string) $url
	 * @param (bool) $fix
	 * @return (mixed)
	 */
	public static function httpurl($url, $fix){
		if(!preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url)){
			if($fix == true){
				$parsed = parse_url($url);
				if(!$parsed){
					return false;
				}
				if(!$parsed['scheme']){
					$url = 'http://' . $url;
					if(self::httpurl($url, false)){
						return $url;
					}
				}
			}
			return false;
		}
		return true;
	}
	public static function email($email, $fix);
	public static function birthdate($birthdate, $fix);
	public static function ipv4($ip);
	public static function ipv6($ip);
	public static function ip($ip){
		return (self::ipv4($ip) || self::ipv6($ip))?true:false;
	}
}