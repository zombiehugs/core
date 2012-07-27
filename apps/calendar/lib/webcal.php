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
class OC_Calendar_WebCal{
	/*
	 * @brief returns all remote calendars of a user
	 * @param (string) $userid - userid of the user
	 * @return (array) $calendars - all remote calendars of a user
	 */
	public static function all($userid, $type = null){
		$typesql = (is_null($type)?'':' AND type = ?');
		$stmt = OC_DB::prepare('SELECT * FROM *PREFIX*calendar_webcal INNER JOIN *PREFIX*calendar_calendars ON *PREFIX*calendar_remote.calendarid = *PREFIX*calendar_calendars.id WHERE userid = ?' . $typesql);
		if(is_null($type)){
			$result = $stmt->execute(array($userid));
		}else{
			$result = $stmt->execute(array($userid, $type));
		}
		while($row = $result->fetchRow()){
			$return[] = $row;
		}
		return $return;
	}
	
	/*
	 * @brief returns a remote calendar
	 * @param (int) $id - id of the calendar
	 * @return (array) $caledar - info about the calendar
	 */
	public static function find($id){
		$stmt = OC_DB::prepare('SELECT * FROM *PREFIX*calendar_webcal INNER JOIN *PREFIX*calendar_calendars ON *PREFIX*calendar_remote.calendarid = *PREFIX*calendar_calendars.id WHERE calendarid = ?');
		$result = $stmt->execute(array($id));
		return $result->fetchRow();
	}
	
	/*
	 * @brief adds a remote calendar
	 * @param interger $calendarid - id of the calendar
	 * @param string $url - url of the calendar
	 * @return integer $id 
	 */
	public static function add($calendarid, $url){
		$stmt = OC_DB::prepare('INSERT INTO *PREFIX*calendar_webcal (calendarid, url) VALUES(?,?)');
		$stmt->execute(array($calendarid, $url));
		$id = OC_DB::insertid('*PREFIX*calendar_remote_' . $type);
		return $id;
	}
	
	/*
	 * @brief edits a remote calendar
	 * @param interger $calendarid - id of the calendar
	 * @param string $url - url of the calendar
	 * @return boolean
	 */
	public static function edit($calendarid, $url){
		$stmt = OC_DB::prepare('UPDATE *PREFIX*calendar_webcal SET url=? WHERE calendarid = ?');
		$stmt->execute(array($url, $type, $calendarid));
		return true;
	}
	
	/*
	 * @brief deletes a remote calendar
	 * @param integer $id - id of the calendar
	 * @return boolean
	 */
	public static function delete($id){
		$stmt = OC_DB::prepare('DELETE FROM *PREFIX*calendar_remote WHERE calendarid = ?');
		$stmt->execute();
		return true;
	}
	
	/*
	 * @brief checks if the calendar was already cached
	 * @param integer $id - id of the calendar
	 * @return boolean 
	 */
	public static function isCached($id){
		$cal = self::find($id);
		if($cal['hash'] == ''){
			return false;
		}
		return true;
	}
	
	/*
	 * @brief checks if the cache is up to date
	 * @param string $calendardata
	 * @param integer $id - id of the calendar
	 * @return boolean
	 */
	public static function isUpToDate($calendardata, $id){
		$cal = self::find($id);
		if($cal['hash'] == md5($calendardata)){
			return true;
		}
		return false;
	}
	
	/*
	 * @brief updates the cache 
	 * @param integer $id - id of the remote calendar
	 * @return boolean
	 */
	public static function updateCache($id){
		$cal = self::find($id);
		if($cal['type'] == 'webcal'){
			$remote = new OC_Calendar_Webcal($cal['url']);
			$calendardata = $remote->serialize();
			if(!self::isUpToDate($calendardata, $id)){
				$import = new OC_Calendar_Import($calendardata);
				//$import - set id
				$import->disableProgressCache();
				$import->import();
				self::updateMD5($id, md5($calendardata));
				return true;
			}
		}
		if($cal['type'] == 'caldav'){
			//caldav not supported yet
			return false;
		}
	}
	
	public static function updateMD5($id, $hash){
		$stmt = OC_DB::prepare('UPDATE *PREFIX*calendar_remote SET hash=? WHERE calendarid = ?');
		$stmt->execute(array($hash, $id));
		return true;
	}
}
