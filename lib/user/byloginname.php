<?php

/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\User;

interface ByLoginName {
	/**
	 * get the uid of a user by it's login name, returns false if the user does not exist on this backend
	 *
	 * @param string $loginName
	 * @return string | bool
	 */
	public function getByLoginName($loginName);
}
