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
    /**
     * @brief get the number of unread notifications for the logged in user
     * @return number of unread notifications, 0 if not logged in
     */
    public static function getUnreadNumber() {
        if(!OCP\User::isLoggedIn()) {
            return 0;
        }
        return OCP\DB::prepare("SELECT COUNT(*) FROM *PREFIX*notifications WHERE read = 0 AND uid = ?")
                ->execute(array(OCP\User::getUser()))
                ->fetchOne();
    }
    
    /**
     * @brief send a new notification to the given user
     * @param $appid app which sends the notification
     * @param $uid receiving user
     * @param $msg message, can contain HTML (<a>, <b>, <i>, <strong>, <em>, <span>) and placeholders (e.g. {name}) for parameters
     * @param $params keys and values for placeholders in $msg
     * @param $href target URL, relative or absolute
     * @param $img image URL, relative or absolute
     * @return id of the inserted notification, null if unsuccessful
     */
    public static function sendUserNotification($appid, $uid, $msg, $params = array(), $href = null, $img = null) {
        try {
            OCP\DB::beginTransaction();
            $notifyStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notifications (appid, uid, href, icon, content, moment) VALUES (?, ?, ?, ?, ?, NOW())");
            $notifyStmt->execute(array($appid, $uid, $href, $img, strip_tags($msg, "<a><b><i><strong><em><span>")));
            $id = OCP\DB::insertid("*PREFIX*notifications");
            if(count($params)) {
                $paramStmt = OCP\DB::prepare("INSERT INTO *PREFIX*notification_params (nid, key, value) VALUES (" . $id . ", ?, ?)");
                foreach($params as $key => $value) {
                    $paramStmt->execute(array($key, $value));
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
    
    public static function getNotifications($number = 10) {
        $notifyStmt = OCP\DB::prepare("SELECT * FROM *PREFIX*notifications WHERE uid = ? ORDER BY read ASC, moment DESC LIMIT ?");
        $result = $notifyStmt->execute(array(OCP\User::getUser(), $number));
        $notifications = $result->fetchAll();
        $paramStmt = OCP\DB::prepare("SELECT key, value FROM *PREFIX*notification_params WHERE nid = ?");
        foreach($notifications as $i => $n) {
            $l = OC_L10N::get($n["appid"]);
            $notifications[$i]["content"] = $l->t($n["content"]);
            $result = $paramStmt->execute(array($n["id"]));
            while($param = $result->fetchRow()) {
                $notifications[$i]["content"] = str_replace("{" . $param["key"] . "}", $param["value"], $notifications[$i]["content"]);
            }
        }
        return $notifications;
    }
}
