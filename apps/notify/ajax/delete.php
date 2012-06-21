<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
if(isset($_POST["id"])) {
	$id = $_POST["id"];
} else {
	OCP\JSON::error(array("message" => "Missing id argument"));
}
try {
	$num = OC_Notify::delete(null, $id);
	OCP\JSON::success(array("num" => $num));
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
