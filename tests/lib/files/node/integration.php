<?php
/**
 * Copyright (c) 2013 Robin Appelman <icewind@owncloud.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

namespace Test\Files\Node;

use OC\Files\Cache\Cache;
use OC\Files\Mount\Manager;
use OC\Files\Node\Root;
use OC\Files\NotFoundException;
use OC\Files\NotPermittedException;
use OC\User\User;

class IntegrationTests extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \OC\Files\Node\Root $root
	 */
	private $root;

	/**
	 * @var \OC\Files\Storage\Storage[]
	 */
	private $storages;

	public function setUp() {
		$manager = new Manager();
		$user = new User('', new \OC_User_Dummy);
		$this->root = new Root($manager, $user);
		$storage = new \OC\Files\Storage\Temporary(array());
		$subStorage = new \OC\Files\Storage\Temporary(array());
		$this->storages[] = $storage;
		$this->storages[] = $subStorage;
		$this->root->mount($storage, '/');
		$this->root->mount($subStorage, '/substorage/');
	}

	public function tearDown() {
		foreach ($this->storages as $storage) {
			$storage->getCache()->clear();
		}
	}

	public function testBasicFile() {
		$file = $this->root->newFile('/foo.txt');
		$this->assertTrue($this->root->nodeExists('/foo.txt'));
		$id = $file->getId();
		$this->assertInstanceOf('\OC\Files\Node\File', $file);
		$etag = $file->getEtag();
		$file->putContent('qwerty');
		$this->assertNotEquals($etag, $file->getEtag());
		$this->assertEquals('text/plain', $file->getMimeType());
		$this->assertEquals('qwerty', $file->getContent());
		$this->assertFalse($this->root->nodeExists('/bar.txt'));
		$target = $file->move('/bar.txt');
		$this->assertEquals($id, $file->getId());
		$this->assertEquals($id, $target->getId());
		$this->assertFalse($this->root->nodeExists('/foo.txt'));
		$this->assertTrue($this->root->nodeExists('/bar.txt'));
		$this->assertEquals('bar.txt', $file->getName());
		$this->assertEquals('bar.txt', $file->getInternalPath());

		$file->move('/substorage/bar.txt');
		$this->assertNotEquals($id, $file->getId());
		$this->assertEquals('qwerty', $file->getContent());
	}

	public function testBasicFolder() {
		$folder = $this->root->newFolder('/foo');
		$this->assertTrue($this->root->nodeExists('/foo'));
		$file = $folder->newFile('/bar');
		$this->assertTrue($this->root->nodeExists('/foo/bar'));
		$file->putContent('qwerty');

		$listing = $folder->getDirectoryListing();
		$this->assertEquals(1, count($listing));
		$this->assertEquals($file->getId(), $listing[0]->getId());
		$this->assertEquals($file->getStorage(), $listing[0]->getStorage());


		$rootListing = $this->root->getDirectoryListing();
		$this->assertEquals(2, count($rootListing));

		$folder->move('/asd');
		/**
		 * @var \OC\Files\Node\File $file
		 */
		$file = $folder->get('/bar');
		$this->assertInstanceOf('\OC\Files\Node\File', $file);
		$this->assertFalse($this->root->nodeExists('/foo/bar'));
		$this->assertTrue($this->root->nodeExists('/asd/bar'));
		$this->assertEquals('qwerty', $file->getContent());
		$folder->move('/substorage/foo');
		/**
		 * @var \OC\Files\Node\File $file
		 */
		$file = $folder->get('/bar');
		$this->assertInstanceOf('\OC\Files\Node\File', $file);
		$this->assertTrue($this->root->nodeExists('/substorage/foo/bar'));
		$this->assertEquals('qwerty', $file->getContent());
	}
}
