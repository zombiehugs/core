<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled("notify");
if(OC_Notify::markRead()) {
	OCP\JSON::success();
} else {
	OCP\JSON::error();
}
