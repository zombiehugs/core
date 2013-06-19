<?php
/**
 * Copyright (c) 2011, Raghu Nayyar <icewind1991@gmail.com>
 * This file is licensed under the Affero General Public License version 3 or later.
 * See the COPYING-README file.
 */
namespace OCP\Settings\Users\Controller;

class Users_PageController extends Controller {
	public function index() {
		$this->render('users/main');
	}
}
?>