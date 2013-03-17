<?php
/**
 * Copyright (c) 2012 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC;

class Session {
	public function start() {
		return session_start();
	}

	public function set($offset, $value) {
		if (is_null($offset)) {
			$_SESSION[] = $value;
		} else {
			$_SESSION[$offset] = $value;
		}
	}

	public function has($offset) {
		return isset($_SESSION[$offset]);
	}

	public function remove($offset) {
		unset($_SESSION[$offset]);
	}

	public function get($offset) {
		return isset($_SESSION[$offset]) ? $_SESSION[$offset] : null;
	}

	public function migrate($destroy = false) {
		session_regenerate_id($destroy);
	}

	public function invalidate() {
		session_unset();
		session_regenerate_id(true);
	}

	public function getName() {
		return session_name();
	}

	public function setName($id) {
		session_name($id);
	}

}
