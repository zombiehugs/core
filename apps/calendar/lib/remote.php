<?php
/**
 * Copyright (c) 2012 Georg Ehrke <georg@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */
/*
 * This class manages remote calendars
 */
class OC_Calendar_Remote{
	/*
	 * @brief adds an remote calendar
	 * @param (string) $url - url of the remote calendar
	 * @return (int) $id - new id of the remote calendar
	 */
	public static function add($url, $userid){
		$url = OC_Validator::httpurl($url, true);
		if(!$url){
			return false;
		}
		$stmt = OC_DB::prepare('INSERT INTO *PREFIX*calendar_remote (url, userid, calendardata, lastupdate, active) VALUES(?,?,null,null, 1)');
		$stmt->execute(array($url,$userid,null));
		$id = OC_DB::insertid('*PREFIX*calendar_remote');
		self::update_cache($id);
		return $id;
	}
	/*
	 * @brief returns the remote calendar
	 * @param (int) $id - id of the calendar
	 * @return (array) $caledar - info about the calendar
	 */
	public static function get($id){
		$stmt = OC_DB::prepare('SELECT * FROM *PREFIX*calendar_remote WHERE id = ?');
		$result = $stmt->execute(array($id));
		return $result->fetchRow();
	}
	/*
	 * @brief returns all remote calendars of a user
	 * @param (string) $userid - userid of the user
	 * @return (array) $calendars - all remote calendars of a user
	 */
	public static function getall($userid){
		$stmt = OC_DB::prepare('SELECT * FROM *PREFIX*calendar_remote WHERE userid = ?');
		$result = $stmt->execute(array($userid));
		$return = array();
		while( $row = $result->fetchRow()){
			$return[] = $row;
		}
		return $return;
	}
	/*
	 * @brief deletes a remote calendar
	 * @param (int) $id - id of the calendar
	 * @return (bool) 
	 */
	public static function delete($id){
		$stmt = OC_DB::prepare('DELETE FROM *PREFIX*calendar_remote WHERE id = ?');
		$stmt->execute();
		if(self::get($id)){
			return false;
		}
		return true;
	}
	/*
	 * @brief checks if the calendar was already cached
	 * @param (int) $id - id of the calendar
	 * @return (bool) 
	 */
	public static function is_cached($id){
		$remotecal = self::get($id);
		if(is_null($remotecal['calendardata']) || is_null($remotecal['lastupdate'])){
			return false;
		}
		return true;
	}
	/*
	 * @brief checks if the cache is up to date
	 * @param (int) $id - id of the remote calendar
	 * @return (bool)
	 */
	public static function is_cache_uptodate($id){
		$remotecal = self::get($id);
		$md5cache = md5($remotecal['calendardata']);
		$md5remote = md5(self::getremoteics($id));
		if($md5cache == $md5remote){
			return true;
		}else{
			return false;
		}
	}
	/*
	 * @brief updates the cache 
	 * @param (int) $id - id of the remote calendar
	 * @return (bool)
	 */
	public static function update_cache($id){
		$ics = self::getremoteics($id);
		if(!$ics){
			return false;
		}
		$stmt = OC_DB::prepare('UPDATE *PREFIX*calendar_remote SET calendardata = ? WHERE id = ?');
		$stmt->execute(array($ics, $id));
		return true;
	}
	/*
	 * @brief returns the remote calendardata
	 * @param (string) $id - id of the remote calendar
	 * @return (string) $calendardata
	 */
	public static function getremoteics($id){
		$remotecal = self::get($id);
		$ics = @fopen($remotecal['url'], 'r');
		if($ics == '' || !$ics){
			return false;
		}
	}
}