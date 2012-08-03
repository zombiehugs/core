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
	 * @param string $userid userid of the user
	 * @return array all remote calendars of a user
	 */
	public static function all($userid){
		$stmt = OC_DB::prepare('SELECT * FROM *PREFIX*calendar_webcal INNER JOIN *PREFIX*calendar_calendars ON *PREFIX*calendar_webcal.calendarid = *PREFIX*calendar_calendars.id WHERE userid = ?');
		$result = $stmt->execute(array($userid));
		while($row = $result->fetchRow()){
			$return[] = $row;
		}
		return $return;
	}
	
	/*
	 * @brief returns a remote calendar
	 * @param integer $id of the calendar
	 * @return array info about the calendar
	 */
	public static function find($id){
		$stmt = OC_DB::prepare('SELECT * FROM *PREFIX*calendar_webcal INNER JOIN *PREFIX*calendar_calendars ON *PREFIX*calendar_webcal.calendarid = *PREFIX*calendar_calendars.id WHERE calendarid = ?');
		$result = $stmt->execute(array($id));
		return $result->fetchRow();
	}
	
	/*
	 * @brief adds a remote calendar
	 * @param interger $id of the calendar
	 * @param string $url of the calendar
	 * @return integer insertid
	 */
	public static function add($id, $url){
		$stmt = OC_DB::prepare('INSERT INTO *PREFIX*calendar_webcal (calendarid, url) VALUES(?,?)');
		$stmt->execute(array($id, $url));
		$id = OC_DB::insertid('*PREFIX*calendar_remote_' . $type);
		return $id;
	}
	
	/*
	 * @brief edits a remote calendar
	 * @param interger $id of the calendar
	 * @param string $url of the calendar
	 * @return boolean
	 */
	public static function edit($id, $url){
		$stmt = OC_DB::prepare('UPDATE *PREFIX*calendar_webcal SET url=? WHERE calendarid = ?');
		$stmt->execute(array($url, $id));
		return true;
	}
	
	/*
	 * @brief deletes a remote calendar
	 * @param integer $id of the calendar
	 * @return boolean
	 */
	public static function delete($id){
		$stmt = OC_DB::prepare('DELETE FROM *PREFIX*calendar_webcal WHERE calendarid = ?');
		$stmt->execute();
		return true;
	}
	
	/*
	 * @brief checks if the calendar is cached
	 * @param integer $id of the calendar
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
	 * @param integer $id of the calendar
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
	 * @param integer $id of the calendar
	 * @return boolean
	 */
	public static function updateCache($id){
		$cal = self::find($id);
		$ics = @file_get_contents($cal['url']);
		if($ics == false){
			OCP\Util::log('calendar', 'Could not load WebCal ressource (' . $cal['url'] . '). Please allow file_get_contents to open online ressources', OCP\Util::ERROR);
			return false;
		}
		if(!self::isUpToDate($calendardata, $id)){
			try{
				$import = new OC_Calendar_Import($calendardata);
				$import->setUserID(OCP\User::getUser());
				$import->setTimeZone(OC_Calendar_App::$tz);
				$import->disableProgressCache();
				$import->setCalendarID($id);
				self::updateMD5($id, md5($calendardata));
			}catch(Exception $e){
				OCP\Util::log('calendar', 'Could not parse WebCal ressource (' . $cal['url'] . ').', OCP\Util::WARN);
				return false;
			}
			return true;
		}
	}
	
	/*
	 * @brief updates the hash for the calendar
	 * @param integer $id of the calendar 
	 */
	public static function updateMD5($id, $hash){
		$stmt = OC_DB::prepare('UPDATE *PREFIX*calendar_webcal SET hash=? WHERE calendarid = ?');
		$stmt->execute(array($hash, $id));
		return true;
	}
	
	/*
	 * @brief checks by calendar id if a calendar is a webcal calendar
	 * @param integer $id of the calendar
	 * @return boolean
	 */
	public static function isWebCal($id){
		if(self::find($id)){
			return true;
		}
		return false;
	}
}
