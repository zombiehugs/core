<?php
/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace OC;

/**
 * Class Lock
 *
 * Provides handling of named locks using lockfiles
 *
 * After a lock is acquired the pid of the process is written to the lockfile so we can track which process holds the lock
 *
 * When acquiring a lock, it will first check if the lock is hold by a process that is no longer alive, in that case the lockfile will be removed.
 * If the lockfile exists and the process that holds the lock is still alive the acquire process fails.
 * If the lockfile doesn't exists we create the lockfile and try to acquire the file lock, once the lock is acquired the pid is written to the lockfile.
 *
 * @package OC
 */
class Lock {
	/**
	 * @var string $name
	 */
	private $name;

	/**
	 * @var string $filename
	 */
	private $filename;

	/**
	 * @var resource $file
	 */
	private $file;

	/**
	 * @var bool $own
	 */
	private $own = false;

	/**
	 * @param string $name
	 */
	public function __construct($name) {
		$this->name = $name;
		$this->filename = get_temp_dir() . '/' . $name . '.lock';
		register_shutdown_function(array($this, '__destruct')); //release the lock (if we own it) when php dies.
	}

	private function getFileHandle() {
		if (!is_resource($this->file)) {
			$this->file = fopen($this->filename, 'w+');
		}
		return $this->file;
	}

	/**
	 * try to acquire the lock
	 *
	 * This first checks if the lock is owned by a dead process,
	 * if the lockfile exists and the process is not dead we know the lock is taken.
	 * If the lockfile doesn't exists we try and acquire the lock using flock
	 *
	 * Using flock besides file_exists is needed because flock is atomic
	 *
	 * @return bool
	 */
	public function acquire() {
		//if the process that has the lock died, remove the lockfile
		if (file_exists($this->filename)) {
			$pid = intval(file_get_contents($this->filename));
			if ($pid > 0 and !$this->pidExists($pid)) {
				if (is_resource($this->file)) {
					fclose($this->file);
				}
				unlink($this->filename);
			} else {
				return false; //since lockfiles gets removed when released we don't have to try and use flock if the file exists
			}
		}
		$file = $this->getFileHandle();
		if (!flock($file, LOCK_EX)) {
			return false;
		} else {
			ftruncate($file, 0);
			$pid = getmypid();
			if ($pid) {
				fwrite($file, $pid);
			}
			$this->own = true;
			return true;
		}
	}

	/**
	 * release the lock if we own it
	 *
	 * @return bool
	 */
	public function release() {
		if ($this->own) {
			$file = $this->getFileHandle();
			if (!flock($file, LOCK_UN)) {
				return false;
			} else {
				fclose($file);
				unlink($this->filename);
				return true;
			}
		} else {
			return false;
		}
	}

	/**
	 * check if the lock is acquired
	 *
	 * Only checks for existence of the lockfile since this is the only cross platform way to check the lock in a non blocking way
	 * Checking only the existance of the lockfile and not the actual file lock might result in false positives during the time
	 * after the lockfile is created but before to lock is acquired and after the lock is released but before the lockfile is cleaned up
	 *
	 * @return bool
	 */
	public function test() {
		return file_exists($this->filename);
	}

	public function __destruct() {
		if ($this->own) {
			$this->release();
		}
	}

	private function pidExists($pid) {
		if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
			$processes = explode("\n", shell_exec("tasklist.exe"));
			foreach ($processes as $process) {
				if ($process) {
					if (strpos("Image Name", $process) === 0 || strpos("===", $process) === 0) {
						continue;
					}
					$matches = false;
					preg_match("/([^ ]*)\s+(\d+).*$/", $process, $matches);
					if (count($matches) > 1) {
						if ($pid == intval($matches[2])) {
							return true;
						}
					}
				}
			}
			return false;
		} else {
			return file_exists('/proc/' . $pid);
		}
	}
}
