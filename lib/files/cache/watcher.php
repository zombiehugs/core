<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC\Files\Cache;

/**
 * check the storage backends for updates and change the cache accordingly
 */
class Watcher {
	/**
	 * @var \OC\Files\Storage\Storage $storage
	 */
	private $storage;

	/**
	 * @var Cache $cache
	 */
	private $cache;

	/**
	 * @var Scanner $scanner;
	 */
	private $scanner;

	/**
	 * @var array $checked list of paths already checked for all storages
	 */
	static private $checked = array();

	/**
	 * @param \OC\Files\Storage\Storage $storage
	 */
	public function __construct(\OC\Files\Storage\Storage $storage) {
		$this->storage = $storage;
		$this->cache = $storage->getCache();
		$this->scanner = $storage->getScanner();
		if (!isset(self::$checked[$this->storage->getId()])) {
			self::$checked[$this->storage->getId()] = array();
		}
	}

	/**
	 * check $path for updates
	 *
	 * @param string $path
	 */
	public function checkUpdate($path) {
		if (array_search($path, self::$checked[$this->storage->getId()]) === false) {
			$cachedEntry = $this->cache->get($path);
			if ($this->storage->hasUpdated($path, $cachedEntry['mtime'])) {
				if ($this->storage->is_dir($path)) {
					$this->scanner->scan($path, Scanner::SCAN_SHALLOW);
				} else {
					$this->scanner->scanFile($path);
				}
				if ($cachedEntry['mimetype'] === \OC\Files\FOLDER_MIMETYPE) {
					$this->cleanFolder($path);
				}
				$this->cache->correctFolderSize($path);
			}
			self::$checked[$this->storage->getId()][] = $path;
		}
	}

	/**
	 * remove deleted files in $path from the cache
	 *
	 * @param string $path
	 */
	public function cleanFolder($path) {
		$cachedContent = $this->cache->getFolderContents($path);
		foreach ($cachedContent as $entry) {
			if (!$this->storage->file_exists($entry['path'])) {
				$this->cache->remove($entry['path']);
			}
		}
	}
}
