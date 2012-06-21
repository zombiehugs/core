<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
if(isset($_POST["id"])) {
	$id = $_POST["id"];
} else {
	OCP\JSON::error();
}
if(isset($_POST["read"])) {
	$read = (bool)$_POST["read"];
}
if(OC_Notify::markRead(null, $id, $read)) {
	OCP\JSON::success(array("unread" => OC_Notify::getUnreadNumber()));
} else {
	OCP\JSON::error();
}
