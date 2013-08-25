<?php
/**
 * Copyright (c) 2013 Christopher Schäpers <christopher@schaepers.it>
 * Copyright (c) 2013 Bart Visscher <bartv@thisnet.nl>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

class Test_Appconfig extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		$query = \OC_DB::prepare('INSERT INTO `*PREFIX*appconfig` VALUES (?, ?, ?)');

		$query->execute(array('testapp', 'enabled', 'true'));
		$query->execute(array('testapp', 'installed_version', '1.2.3'));
		$query->execute(array('testapp', 'depends_on', 'someapp'));
		$query->execute(array('testapp', 'deletethis', 'deletethis'));
		$query->execute(array('testapp', 'key', 'value'));

		$query->execute(array('someapp', 'key', 'value'));
		$query->execute(array('someapp', 'otherkey', 'othervalue'));

		$query->execute(array('123456', 'key', 'value'));
		$query->execute(array('123456', 'enabled', 'false'));

		$query->execute(array('anotherapp', 'key', 'value'));
		$query->execute(array('anotherapp', 'enabled', 'false'));
	}

	public static function tearDownAfterClass() {
		$query = \OC_DB::prepare('DELETE FROM `*PREFIX*appconfig` WHERE `appid` = ?');
		$query->execute(array('testapp'));
		$query->execute(array('someapp'));
		$query->execute(array('123456'));
		$query->execute(array('anotherapp'));
	}

	public function testGetApps() {
		$query = \OC_DB::prepare('SELECT DISTINCT `appid` FROM `*PREFIX*appconfig`');
		$result = $query->execute();
		$expected = array();
		while ($row = $result->fetchRow()) {
			$expected[] = $row['appid'];
		}
		$apps = \OC_Appconfig::getApps();
		$this->assertEquals($expected, $apps);
	}

	public function testGetKeys() {
		$query = \OC_DB::prepare('SELECT `configkey` FROM `*PREFIX*appconfig` WHERE `appid` = ?');
		$result = $query->execute(array('testapp'));
		$expected = array();
		while($row = $result->fetchRow()) {
			$expected[] = $row["configkey"];
		}
		$keys = \OC_Appconfig::getKeys('testapp');
		$this->assertEquals($expected, $keys);
	}

	public function testGetValue() {
		$query = \OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*appconfig` WHERE `appid` = ? AND `configkey` = ?');
		$result = $query->execute(array('testapp', 'installed_version'));
		$expected = $result->fetchRow();
		$value = \OC_Appconfig::getValue('testapp', 'installed_version');
		$this->assertEquals($expected['configvalue'], $value);

		$value = \OC_Appconfig::getValue('testapp', 'nonexistant');
		$this->assertNull($value);

		$value = \OC_Appconfig::getValue('testapp', 'nonexistant', 'default');
		$this->assertEquals('default', $value);
	}

	public function testHasKey() {
		$value = \OC_Appconfig::hasKey('testapp', 'installed_version');
		$this->assertTrue($value);

		$value = \OC_Appconfig::hasKey('nonexistant', 'nonexistant');
		$this->assertFalse($value);
	}

	public function testSetValue() {
		\OC_Appconfig::setValue('testapp', 'installed_version', '1.33.7');
		$query = \OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*appconfig` WHERE `appid` = ? AND `configkey` = ?');
		$result = $query->execute(array('testapp', 'installed_version'));
		$value = $result->fetchRow();
		$this->assertEquals('1.33.7', $value['configvalue']);

		\OC_Appconfig::setValue('someapp', 'somekey', 'somevalue');
		$query = \OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*appconfig` WHERE `appid` = ? AND `configkey` = ?');
		$result = $query->execute(array('someapp', 'somekey'));
		$value = $result->fetchRow();
		$this->assertEquals('somevalue', $value['configvalue']);
	}

	public function testDeleteKey() {
		\OC_Appconfig::deleteKey('testapp', 'deletethis');
		$query = \OC_DB::prepare('SELECT `configvalue` FROM `*PREFIX*appconfig` WHERE `appid` = ? AND `configkey` = ?');
		$query->execute(array('testapp', 'deletethis'));
		$result = (bool)$query->fetchRow();
		$this->assertFalse($result);
	}

	public function testDeleteApp() {
		\OC_Appconfig::deleteApp('someapp');
		$query = \OC_DB::prepare('SELECT `configkey` FROM `*PREFIX*appconfig` WHERE `appid` = ?');
		$query->execute(array('someapp'));
		$result = (bool)$query->fetchRow();
		$this->assertFalse($result);
	}

	public function testGetValues() {
		$this->assertFalse(\OC_Appconfig::getValues('testapp', 'enabled'));

		$query = \OC_DB::prepare('SELECT `configkey`, `configvalue` FROM `*PREFIX*appconfig` WHERE `appid` = ?');
		$query->execute(array('testapp'));
		$expected = array();
		while ($row = $query->fetchRow()) {
			$expected[$row['configkey']] = $row['configvalue'];
		}
		$values = \OC_Appconfig::getValues('testapp', false);
		$this->assertEquals($expected, $values);

		$query = \OC_DB::prepare('SELECT `appid`, `configvalue` FROM `*PREFIX*appconfig` WHERE `configkey` = ?');
		$query->execute(array('enabled'));
		$expected = array();
		while ($row = $query->fetchRow()) {
			$expected[$row['appid']] = $row['configvalue'];
		}
		$values = \OC_Appconfig::getValues(false, 'enabled');
		$this->assertEquals($expected, $values);
	}
}

class Test_AppConfig_Object extends PHPUnit_Framework_TestCase {
	public function setUp()
	{
		$statementMock = $this->getMock('\Doctrine\DBAL\Statement', array(), array(), '', false);
		$statementMock->expects($this->any())
			->method('fetch')
			->will($this->onConsecutiveCalls(
			array('appid'=>'foo', 'configkey' => 'bar', 'configvalue' => 'moo'),
			false));
		$connectionMock = $this->getMock('\OC\DB\Connection', array(), array(), '', false);
		$connectionMock->expects($this->any())
			->method('executeQuery')
			->with($this->equalTo('SELECT * FROM `*PREFIX*appconfig`'))
			->will($this->returnValue($statementMock));
		$this->connectionMock = $connectionMock;

		$this->appconfig = new OC\AppConfig($connectionMock);
	}

	public function testLoadConfig()
	{
		$apps = $this->appconfig->getApps();
		$this->assertAttributeEquals(array('foo' => array('bar' => 'moo')), 'config', $this->appconfig);
	}

	public function testGetApps()
	{
		$apps = $this->appconfig->getApps();
		$this->assertEquals(array('foo'), $apps);
	}

	public function testGetKeys()
	{
		$keys = $this->appconfig->getKeys('foo');
		$this->assertEquals(array('bar'), $keys);
	}

	public function testGetValue()
	{
		$value = $this->appconfig->getValue('foo', 'bar');
		$this->assertEquals('moo', $value);
		$value = $this->appconfig->getValue('foo', 'red', 'def');
		$this->assertEquals('def', $value);
		$value = $this->appconfig->getValue('bla', 'red', 'def');
		$this->assertEquals('def', $value);
	}

	public function testHasKey()
	{
		$this->assertTrue($this->appconfig->hasKey('foo', 'bar'));
		$this->assertFalse($this->appconfig->hasKey('foo', 'foo'));
		$this->assertFalse($this->appconfig->hasKey('bar', 'foo'));
		$this->assertFalse($this->appconfig->hasKey('bar', 'bar'));
	}

	public function testSetValue()
	{
		$this->connectionMock->expects($this->once())
			->method('insert')
			->with($this->equalTo('*PREFIX*appconfig'),
				$this->equalTo(
					array(
						'appid' => 'bar',
						'configkey' => 'moo',
						'configvalue' => 'v1',
					)
				));
		$this->connectionMock->expects($this->once())
			->method('update')
			->with($this->equalTo('*PREFIX*appconfig'),
				$this->equalTo(
					array(
						'configvalue' => 'v2',
					)),
				$this->equalTo(
					array(
						'appid' => 'foo',
						'configkey' => 'bar',
					)
				));

		$this->appconfig->setValue('bar', 'moo', 'v1');
		$this->assertAttributeEquals(array(
				'foo' => array('bar' => 'moo'),
				'bar' => array('moo' => 'v1')
			), 'config', $this->appconfig);
		$this->appconfig->setValue('foo', 'bar', 'v2');
		$this->assertAttributeEquals(array(
				'foo' => array('bar' => 'v2'),
				'bar' => array('moo' => 'v1')
			), 'config', $this->appconfig);
	}

	public function testDeleteKey()
	{
		$this->connectionMock->expects($this->once())
			->method('delete')
			->with($this->equalTo('*PREFIX*appconfig'),
				$this->equalTo(
					array(
						'appid' => 'foo',
						'configkey' => 'bar',
					)
				));

		$this->appconfig->deleteKey('foo', 'bar');
		$this->assertAttributeEquals(array('foo' => array()), 'config', $this->appconfig);
	}

	public function testDeleteApp()
	{
		$this->connectionMock->expects($this->once())
			->method('delete')
			->with($this->equalTo('*PREFIX*appconfig'),
				$this->equalTo(
					array(
						'appid' => 'foo',
					)
				));

		$this->appconfig->deleteApp('foo');
		$this->assertAttributeEquals(array(), 'config', $this->appconfig);
	}

	public function testGetValues()
	{
		$statementMock = $this->getMock('\Doctrine\DBAL\Statement', array(), array(), '', false);
		$statementMock->expects($this->exactly(4))
			->method('fetch')
			->with(\PDO::FETCH_ASSOC)
			->will($this->onConsecutiveCalls(
				array('configvalue' =>'bar', 'configkey' => 'x'),
				false,
				array('configvalue' =>'foo', 'appid' => 'y'),
				false
			));
		$connectionMock = $this->getMock('\OC\DB\Connection', array(), array(), '', false);
		$connectionMock->expects($this->at(0))
			->method('executeQuery')
			->with($this->equalTo('SELECT `configvalue`, `configkey` FROM `*PREFIX*appconfig` WHERE `appid` = ?'),
				$this->equalTo(array('foo')))
			->will($this->returnValue($statementMock));
		$connectionMock->expects($this->at(1))
			->method('executeQuery')
			->with($this->equalTo('SELECT `configvalue`, `appid` FROM `*PREFIX*appconfig` WHERE `configkey` = ?'),
				$this->equalTo(array('bar')))
			->will($this->returnValue($statementMock));

		$appconfig = new OC\AppConfig($connectionMock);
		$values = $appconfig->getValues('foo', false);
		$this->assertEquals(array('x'=> 'bar'), $values);
		$values = $appconfig->getValues(false, 'bar');
		$this->assertEquals(array('y'=> 'foo'), $values);
		$values = $appconfig->getValues(false, false);
		$this->assertEquals(false, $values);
		$values = $appconfig->getValues('x', 'x');
		$this->assertEquals(false, $values);
	}
}
