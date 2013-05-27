<?php
/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Node;

use OC\Files\Cache\Cache;
use OC\Files\Cache\Scanner;
use OC\Files\NotFoundException;
use OC\Files\NotPermittedException;

require_once 'files/exceptions.php';

class Node {
	/**
	 * @var Root $root
	 */
	protected $root;

	/**
	 * @var \OC\Files\Storage\Storage $storage
	 */
	protected $storage;

	/**
	 * @var string $path
	 */
	protected $path;

	/**
	 * @var string $internalPath
	 */
	protected $internalPath;

	/**
	 * array of metadata from the cache
	 *
	 * @var array $data
	 */
	protected $data;

	/**
	 * @var bool $exists
	 */
	protected $exists = true;

	/**
	 * @var int[] $permissions
	 */
	protected $permissions = null;

	/**
	 * @param \OC\Files\Node\Root Root $root
	 * @param \OC\Files\Storage\Storage $storage
	 * @param string $internalPath
	 * @param string $path
	 * @param array $data array of metadata
	 */
	public function __construct($root, $storage, $internalPath, $path, $data) {
		$this->root = $root;
		$this->storage = $storage;
		$this->internalPath = $internalPath;
		$this->path = $path;
		$this->data = $data;
		if (isset($data['permissions'])) {
			$this->permissions = $data['permissions'];
		}
	}

	/**
	 * @param string[] $hooks
	 */
	protected function sendHooks($hooks) {
		foreach ($hooks as $hook) {
			$this->root->emit('\OC\Files', $hook, array($this));
		}
	}

	/**
	 * @param int $permissions
	 * @return bool
	 */
	protected function checkPermissions($permissions) {
		return ($this->getPermissions() & $permissions) == $permissions;
	}

	/**
	 * @param string $targetPath
	 * @throws \OC\Files\NotPermittedException
	 * @return \OC\Files\Node\Node
	 */
	public function move($targetPath) {
		return;
	}

	public function delete() {
		return;
	}

	/**
	 * @param string $targetPath
	 * @return \OC\Files\Node\Node
	 */
	public function copy($targetPath) {
		return;
	}

	/**
	 * @param int $mtime
	 * @throws \OC\Files\NotPermittedException
	 */
	public function touch($mtime = null) {
		if ($this->checkPermissions(\OCP\PERMISSION_UPDATE)) {
			$this->sendHooks(array('preTouch'));
			$this->storage->touch($this->internalPath, $mtime);
			$this->updateCache();
			$this->sendHooks(array('postTouch'));
		} else {
			throw new NotPermittedException();
		}
	}

	/**
	 * @return \OC\Files\Storage\Storage
	 * @throws \OC\Files\NotFoundException
	 */
	public function getStorage() {
		return $this->storage;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getInternalPath() {
		return $this->internalPath;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->data['fileid'];
	}

	/**
	 * @return array
	 */
	public function stat() {
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function getMTime() {
		return $this->data['mtime'];
	}

	/**
	 * @return int
	 */
	public function getSize() {
		return $this->data['size'];
	}

	/**
	 * @return string
	 */
	public function getEtag() {
		return $this->data['etag'];
	}

	/**
	 * @return int
	 */
	public function getPermissions() {
		if (is_null($this->permissions)) {
			$this->permissions = $this->getCachePermissions($this->root->getUser()->getUID());
		}
		return $this->permissions;
	}

	/**
	 * @return bool
	 */
	public function isReadable() {
		return $this->checkPermissions(\OCP\PERMISSION_READ);
	}

	/**
	 * @return bool
	 */
	public function isUpdateable() {
		return $this->checkPermissions(\OCP\PERMISSION_UPDATE);
	}

	/**
	 * @return bool
	 */
	public function isDeletable() {
		return $this->checkPermissions(\OCP\PERMISSION_DELETE);
	}

	/**
	 * @return bool
	 */
	public function isShareable() {
		return $this->checkPermissions(\OCP\PERMISSION_SHARE);
	}

	/**
	 * @return Node
	 */
	public function getParent() {
		return $this->root->get(dirname($this->path));
	}

	/**
	 * @return string
	 */
	public function getName() {
		return basename($this->path);
	}

	/**
	 * @param string $path
	 * @return string
	 */
	protected function normalizePath($path) {
		if ($path == '') {
			return '/';
		}
		//no windows style slashes
		$path = str_replace('\\', '/', $path);
		//add leading slash
		if ($path[0] !== '/') {
			$path = '/' . $path;
		}
		//remove duplicate slashes
		while (strpos($path, '//') !== false) {
			$path = str_replace('//', '/', $path);
		}
		//remove trailing slash
		$path = rtrim($path, '/');

		return $path;
	}

	/**
	 * check if the requested path is valid
	 *
	 * @param string $path
	 * @return bool
	 */
	public function isValidPath($path) {
		if (!$path || $path[0] !== '/') {
			$path = '/' . $path;
		}
		if (strstr($path, '/../') || strrchr($path, '/') === '/..') {
			return false;
		}
		return true;
	}

	/**
	 * get the filesystem info
	 *
	 * @throws \OC\Files\NotFoundException
	 * @return array
	 *
	 * returns an associative array with the following keys:
	 * - size
	 * - mtime
	 * - mimetype
	 * - encrypted
	 * - versioned
	 */
	protected function getFileInfo() {
		$cache = $this->storage->getCache($this->internalPath);

		$this->checkUpdate();

		$data = $cache->get($this->internalPath);

		if ($data and $data['fileid']) {
			if ($data['mimetype'] === 'httpd/unix-directory') {
				//add the sizes of other mountpoints to the folder
				$mounts = $this->root->getMountsIn($this->path);
				foreach ($mounts as $mount) {
					$subStorage = $mount->getStorage();
					if ($subStorage) {
						$subCache = $subStorage->getCache('');
						$rootEntry = $subCache->get('');
						$data['size'] += $rootEntry['size'];
					}
				}
			}
		} else {
			throw new NotFoundException();
		}

		return $data;
	}

	protected function setData($data) {
		$this->data = $data;
	}

	/**
	 * check for outside updates
	 */
	protected function checkUpdate() {
		/**
		 * @var \OC\Files\Storage\Storage $storage
		 * @var string $internalPath
		 */
		$cache = $this->storage->getCache($this->internalPath);

		if ($cache->getStatus($this->internalPath) < Cache::COMPLETE) {
			$scanner = $this->storage->getScanner($this->internalPath);
			$scanner->scan($this->internalPath, Scanner::SCAN_SHALLOW);
		} else {
			$watcher = $this->storage->getWatcher($this->internalPath);
			$watcher->checkUpdate($this->internalPath);
		}
	}

	/**
	 * load the permissions from the cache
	 *
	 * @param string $user
	 * @return int
	 */
	protected function getCachePermissions($user) {
		$permissionsCache = $this->storage->getPermissionsCache($this->internalPath);
		$permissions = $permissionsCache->get($this->getId(), $user);
		if ($permissions === -1) {
			$permissions = $this->storage->getPermissions($this->internalPath);
			$permissionsCache->set($this->getId(), $user, $permissions);
		}
		return $permissions;
	}

	/**
	 * get the storage and internal path for a path
	 *
	 * @param $path
	 * @throws \OC\Files\NotFoundException
	 * @return array [\OC\Files\Storage\Storage, string]
	 */
	protected function resolvePath($path) {
		$mount = $this->root->getMount($path);
		if ($mount) {
			$internalPath = substr($path, strlen($mount->getMountPoint()));
			return array($mount->getStorage(), $internalPath);
		} else {
			throw new NotFoundException();
		}
	}

	/**
	 * @param \OC\Files\Storage\Storage $storage
	 * @param string $internalPath
	 * @param string $path
	 * @param array $info
	 * @return File|Folder
	 */
	protected function createNode($storage, $internalPath, $path, $info = array()) {
		if (!isset($info['mimetype'])) {
			return new Node($this->root, $storage, $internalPath, $path, $info);
		} else if ($info['mimetype'] === 'httpd/unix-directory') {
			return new Folder($this->root, $storage, $internalPath, $path, $info);
		} else {
			return new File($this->root, $storage, $internalPath, $path, $info);
		}
	}

	protected function updateCache() {
		clearstatcache();
		$scanner = $this->storage->getScanner($this->internalPath);
		$cache = $this->storage->getCache($this->internalPath);
		$scanner->scanFile($this->internalPath, true);
		$this->data = $cache->get($this->internalPath);
	}

	protected function deleteFromCache() {
		$cache = $this->storage->getCache($this->internalPath);
		$cache->remove($this->internalPath);
	}

	protected function moveInCache($sourcePath, $targetPath) {
		$cache = $this->storage->getCache($this->internalPath);
		$cache->move($sourcePath, $targetPath);
	}
}
