<?php
namespace OCA\OJSXC;

use OCP\AppFramework\Db\DoesNotExistException;
use Test\TestCase;
use OCA\OJSXC\AppInfo\Application;
use OCA\OJSXC\MemLock;

//function time() {
//	return DbLockTest::$time;
//}

/**
 * @group DB
 */
class MemLockTest extends TestCase {

	/**
	 * @var \OCA\OJSXC\MemLock
	 */
	private $memLock;

	/**
	 * @var \OCA\OJSXC\MemLock
	 */
	private $memLock2;

	/**
	 * @var \OCP\AppFramework\IAppContainer
	 */
	private $container;

	public static $time;

	public function setUp() {
		parent::setUp();
		$app = new Application();
		$this->container = $app->getContainer();
	}

	/**
	 * Tests the setLock and stillLocked function by setting up and lock
	 * and then setting a new lock.
	 */
	public function testLock() {
		self::$time = 4;
		$this->memLock = new MemLock(
			'john',
			$this->container->getServer()->getMemCacheFactory()
		);
		$this->memLock->setLock();


//		$result = $this->fetchLocks();
//		$this->assertCount(1, $result);
//		$this->assertEquals($result[0]['userid'], 'john');
//		$this->assertEquals($result[0]['appid'], 'ojsxc');
//		$this->assertEquals($result[0]['configkey'], 'longpolling');
//		$this->assertEquals($result[0]['configvalue'], '4');
		$this->assertTrue($this->memLock->stillLocked());
//
//
//		self::$time = 5;
//		$this->dbLock2 = new DbLock(
//			'john',
//			$this->container->getServer()->getDb(),
//			$this->container->getServer()->getConfig()
//		); // simulate new lock/request
//		$this->dbLock2->setLock();
//
//		$this->assertFalse($this->dbLock->stillLocked());
//		$this->assertTrue($this->dbLock2->stillLocked());
//		$result = $this->fetchLocks();
//		$this->assertCount(1, $result);
//		$this->assertEquals($result[0]['userid'], 'john');
//		$this->assertEquals($result[0]['appid'], 'ojsxc');
//		$this->assertEquals($result[0]['configkey'], 'longpolling');
//		$this->assertEquals($result[0]['configvalue'], '5');
//		$this->assertTrue($this->dbLock2->stillLocked());

	}

	private function  fetchLocks() {
//		$stmt = $this->con->executeQuery("SELECT * FROM `*PREFIX*preferences` WHERE `appid`='ojsxc' AND `configkey`='longpolling'");
//
//		$reuslt = [];
//
//		while($row = $stmt->fetch()){
//			$result[] = $row;
//		}
//		return $result;
	}

}