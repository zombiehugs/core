<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
try {
	$num = OC_Notify::delete();
	OCP\JSON::success(array("num" => $num));
} catch(Exception $e) {
	OCP\JSON::error(array("message" => $e->getMessage()));
}
