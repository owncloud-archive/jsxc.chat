<?php

namespace OCA\OJSXC;

use OCP\IConfig;
use OCP\IDb;

/**
 * Class DbLock
 *
 * @package OCA\OJSXC
 */
class DbLock implements ILock {

	/**
	 * @var IDb $con
	 */
	private $con;

	/**
	 * @var IConfig $config
	 */
	private $config;

	/**
	 * @var string $userId
	 */
	private $userId;

	/**
	 * @var int $pollingId
	 */
	private $pollingId;

	/**
	 * DbLock constructor.
	 *
	 * @param string $userId
	 * @param IDb $con
	 * @param IConfig $config
	 */
	public function __construct($userId, IDb $con, IConfig $config) {
		$this->con = $con;
		$this->userId = $userId;
		$this->config = $config;
		$this->pollingId = time();
	}

	public function setLock() {
		$this->config->setUserValue($this->userId, 'ojsxc', 'longpolling', $this->pollingId);
	}

	/**
	 * @return bool
	 */
	public function stillLocked() {
		$sql = "SELECT `configvalue` FROM `*PREFIX*preferences` WHERE `userid` = ? AND `appid`='ojsxc' AND `configkey`='longpolling'";
		$q = $this->con->prepareQuery($sql);
		$r = $q->execute([$this->userId]);
		$r = $r->fetchRow();
		return (int) $r['configvalue'] === (int) $this->pollingId;
	}

}