<?php
/**
* ownCloud - user notifications
*
* @author Florian Hülsmann
* @copyright 2012 Florian Hülsmann <fh@cbix.de>
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
 * Public class for notifications
 */
class OC_Notify {
	private static $classIdStmt = null;
	private static $classInsertStmt = null;
	private static $notifyStmt = null;
	private static $paramStmt = null;
	/**
	 * @brief get the class id of a given app/class name pair
	 * @param $app app id
	 * @param $class class name defined in the app's info.xml
	 * @return id or false, if the class doesn't exist
	 */
	private static function getClassId($app, $class) {
		if(is_null(self::$classIdStmt)) {
			self::$classIdStmt = OCP\DB::prepare("SELECT id FROM *PREFIX*notification_classes WHERE appid = ? AND name = ?");
		}
		OCP\Util::writeLog("notify", "foo", OCP\Util::DEBUG);
		$result = self::$classIdStmt->execute(array($app, $class));
		$id = $result->fetchOne();
		if($id !== false and is_numeric($id)) {
			return (int)$id;
		}
		return self::readAppNotifications($app, $class);
	}
	
	/**
	 * @brief parse the info.xml of the given app and save its notification templates to database
	 * @param $app application id
	 * @param $class optional name of the class to get its ID
	 * @return class id if a name is given and the class exists
	 */
	public static function readAppNotifications($app, $class = null) {
		if(is_null(self::$classInsertStmt)) {
			self::$classInsertStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notification_classes (appid, name, content) VALUES (?, ?, ?)");
		}
		$appInfo = @file_get_contents(OC_App::getAppPath($app) . '/appinfo/info.xml');
		if($appInfo) {
			$xml = new SimpleXMLElement($appInfo);
		} else {
			return false;
		}
		$templates = $xml->xpath('notifications/template');
		$return = false;
		foreach($templates as $template) {
			$name = $template->attributes()->id;
			$content = strip_tags((string)$template, "<a><b><i><strong><em><span>");
			if(empty($name) or empty($content)) {
				continue;
			}
			try {
				self::$classInsertStmt->execute(array($app, $name, $content));
			} catch(Exception $e) {
				//most likely a database conflict
			}
			if($class == $name and $return !== false) {
				$return = OCP\DB::insertid("*PREFIX*notification_classes");
			}
		}
		if($class) {
			return $return;
		} else {
			return true;
		}
	}
	
    /**
     * @brief get the number of unread notifications for the logged in user
     * @param $uid user id
     * @return number of unread notifications, 0 if not logged in
     */
    public static function getUnreadNumber($uid = null) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
        return OCP\DB::prepare("SELECT COUNT(*) FROM *PREFIX*notifications WHERE read = 0 AND uid = ?")
                ->execute(array($uid))
                ->fetchOne();
    }
    
    /**
     * @brief send a new notification to the given user
     * @param $appid app which sends the notification
     * @param $class id relating to a template in the app's info.xml
     * @param $uid receiving user
     * @param $params keys and values for placeholders in the template and href/img
     * @return id of the inserted notification, null if unsuccessful
     */
    public static function sendUserNotification($appid, $class, $uid, $params = array()) {
        try {
			$classId = self::getClassId($appid, $class);
			if($classId === false) {
				throw new Exception("Notification template $appid/$class not found");
			}
            OCP\DB::beginTransaction();
            if(is_null(self::$notifyStmt)) {
				self::$notifyStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notifications (class, uid, moment) VALUES (?, ?, NOW())");
			}
            self::$notifyStmt->execute(array($classId, $uid));
            $id = OCP\DB::insertid("*PREFIX*notifications");
            if(count($params)) {
				if(is_null(self::$paramStmt)) {
					self::$paramStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notification_params (nid, key, value) VALUES ($id, ?, ?)");
				}
                foreach($params as $key => $value) {
                    self::$paramStmt->execute(array($key, $value));
                    OCP\DB::insertid("*PREFIX*notification_params");
                }
            }
            OCP\DB::commit();
            return $id;
        } catch(Exception $e) {
            OCP\Util::writeLog("notify", "Could not send notification: " . $e->getMessage(), OCP\Util::ERROR);
            return null;
        }
    }
    
    /**
     * @brief get the latest notifications for the logged in user
     * @param $uid user id
     * @param $count limit for number of notifications
     * @return array with notifications
     */
    public static function getNotifications($uid = null, $count = null, $lang = null) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return array();
			}
		}
        if(!$count) {
			$notifyStmt = OCP\DB::prepare("SELECT n.id, n.uid, n.read, n.moment, c.appid, c.name, c.content FROM *PREFIX*notifications AS n INNER JOIN *PREFIX*notification_classes AS c ON n.class = c.id WHERE n.uid = ? ORDER BY n.read ASC, n.moment DESC");
			$result = $notifyStmt->execute(array($uid));
		} else {
			$notifyStmt = OCP\DB::prepare("SELECT n.id, n.uid, n.read, n.moment, c.appid, c.name AS class, c.content FROM *PREFIX*notifications AS n INNER JOIN *PREFIX*notification_classes AS c ON n.class = c.id WHERE n.uid = ? ORDER BY n.read ASC, n.moment DESC LIMIT ?");
			$result = $notifyStmt->execute(array($uid, $count));
		}
        $notifications = $result->fetchAll();
        $paramStmt = OCP\DB::prepare("SELECT key, value FROM *PREFIX*notification_params WHERE nid = ?");
        foreach($notifications as $i => $n) {
            $l = OC_L10N::get($n["appid"], $lang);
            $notifications[$i]["content"] = $l->t($n["content"]);
            $result = $paramStmt->execute(array($n["id"]));
            while($param = $result->fetchRow()) {
				if(in_array($param["key"], array('href', 'img'))) {
					$notifications[$i][$param["key"]] = $param["value"];
				} elseif(strpos($notifications[$i]["content"], "{{$param["key"]}}") !== false) {
					$notifications[$i]["content"] = str_replace("{{$param["key"]}}", $param["value"], $notifications[$i]["content"]);
				} else {
					$notifications[$i]["params"][$param["key"]] = $param["value"];
				}
            }
        }
        return $notifications;
    }
    
    /**
     * @brief mark one or more notifications of the logged in user as read
     * @param $uid user id
     * @param $id either notification id returned by sendUserNotification, app id or null
     * @param $read the (boolean) value to set the read column to
     * @return number of affected rows
     */
    public static function markRead($uid = null, $id = null, $read = true) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				return 0;
			}
		}
		if(is_null($id)) {
			// update all user notifications
			$stmt = OCP\DB::prepare("UPDATE *PREFIX*notifications SET read = ? WHERE uid = ?");
			$stmt->execute(array((int) $read, $uid));
		} else if(is_numeric($id)) {
			// update the user notification with the given id
			$stmt = OCP\DB::prepare("UPDATE *PREFIX*notifications SET read = ? WHERE id = ? AND uid = ?");
			$stmt->execute(array((int) $read, $id, $uid));
		} else if(is_string($id)) {
			// update all user notifications of the given app
			$stmt = OCP\DB::prepare("UPDATE *PREFIX*notifications SET read = ? WHERE uid = ? AND appid = ?");
			$stmt->execute(array((int) $read, $uid, $id));
		} else {
			return 0;
		}
		return $stmt->numRows();
	}
	
	/**
     * @brief delete one or more notifications from the database
     * @param $uid user id
     * @param $id either notification id returned by sendUserNotification, app id, boolean or null
     * @return number of affected rows
     * @fixme also delete assigned notification_params!!
     */
    public static function delete($uid = null, $id = null) {
		if(is_null($uid)) {
			if(OCP\User::isLoggedIn()) {
				$uid = OCP\User::getUser();
			} else {
				throw new Exception("Not authorized!");
			}
		}
		$deleteParams = OCP\DB::prepare("DELETE FROM *PREFIX*notification_params WHERE nid = ?");
		if(is_numeric($id)) {
			// delete the user notification with the given id
			$stmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE id = ? AND uid = ?");
			$stmt->execute(array($id, $uid));
			if($stmt->numRows()) {
				$deleteParams->execute(array($id));
				return 1;
			}
			return 0;
		} else {
			if(is_null($id)) {
				// delete all user notifications
				$stmt = OCP\DB::prepare("SELECT id FROM *PREFIX*notifications WHERE uid = ?");
				$deleteNotifyStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE uid = ?");
				$notifyStmtParams = array($uid);
			} else if(is_string($id)) {
				// delete all user notifications of the given app
				$stmt = OCP\DB::prepare("SELECT id FROM *PREFIX*notifications WHERE uid = ? AND appid = ?");
				$deleteNotifyStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE uid = ? AND appid = ?");
				$notifyStmtParams = array($uid, $id);
			} else if(is_bool($id)) {
				// delete all user notifications with read = $id
				$stmt = OCP\DB::prepare("SELECT id FROM *PREFIX*notifications WHERE uid = ? AND read = ?");
				$deleteNotifyStmt = OCP\DB::prepare("DELETE FROM *PREFIX*notifications WHERE uid = ? AND read = ?");
				$notifyStmtParams = array($uid, $id);
			} else {
				throw new Exception("Invalid argument!");
			}
			$result = $stmt->execute($notifyStmtParams);
			if($result) {
				while($row = $result->fetchRow()) {
					$deleteParams->execute(array($row->id));
				}
			}
			$deleteNotifyStmt->execute($notifyStmtParams);
			return $deleteNotifyStmt->numRows();
		}
	}
	
	/**
	 * @brief Add an app or notification class to the blacklist
	 */
}
