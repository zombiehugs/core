<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
if(isset($_POST["read"])) {
	$read = (strtolower($_POST["read"]) != "false" and (bool)$_POST["read"]);
} else {
	OCP\JSON::error(array("message" => "Missing argument"));
}
try {
	$num = OC_Notify::delete(null, $read);
	OCP\JSON::success(array("num" => $num));
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
