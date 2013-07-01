<?php
/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Storage\Wrapper;

class Quota extends Wrapper {
	private $userQuota = array();

	/**
	 * get the quota for the user
	 *
	 * @param user
	 * @return int
	 */
	private function getQuota($user) {
		if (!isset($this->userQuota[$user])) {
			$userQuota = \OC_Preferences::getValue($user, 'files', 'quota', 'default');
			if ($userQuota == 'default') {
				$userQuota = \OC_AppConfig::getValue('files', 'default_quota', 'none');
			}
			if ($userQuota == 'none') {
				$this->userQuota[$user] = \OC\Files\SPACE_UNLIMITED;
			} else {
				$this->userQuota[$user] = \OC_Helper::computerFileSize($userQuota);
			}
		}
		return $this->userQuota[$user];
	}

	private function getUsedSpace() {
		$cache = $this->getCache();
		$data = $cache->get('');
		if (is_array($data) and isset($data['size'])) {
			return $data['size'];
		} else {
			return \OC\Files\SPACE_NOT_COMPUTED;
		}
	}

	/**
	 * Get free space as limited by the quota
	 *
	 * @param string $path
	 * @return int
	 */
	public function free_space($path) {
		$quota = $this->getQuota(\OC_User::getUsers());
		if ($quota < 0) {
			return $this->storage->free_space($path);
		} else {
			$used = $this->getUsedSpace();
			if ($used < 0) {
				return \OC\Files\SPACE_NOT_COMPUTED;
			} else {
				$free = $this->storage->free_space($path);
				return min($free, ($quota - $used));
			}
		}
	}
}
