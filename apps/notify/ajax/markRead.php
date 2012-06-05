<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
if(isset($_POST["id"])) {
	$id = $_POST["id"];
} else {
	OCP\JSON::error();
}
if(isset($_POST["read"])) {
	$read = ($_POST["read"] == "true");
} else {
	$read = true;
}
if(OCP\Util::setUserNotificationRead($id, $read)) {
	OCP\JSON::success(array("unread" => OC_Notify::getUnreadNumber()));
} else {
	OCP\JSON::error();
}
