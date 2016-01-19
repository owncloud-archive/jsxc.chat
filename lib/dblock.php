<?php

namespace OCA\OJSXC;

use OCP\IConfig;
use OCP\IDb;

class DbLock implements ILock {

	/**
	 * @var IDb
	 */
	private $con;

	/**
	 * @var IConfig
	 */
	private $config;

	private $userId;

	private $pollingId;

	public function __construct($userId, IDb $con, IConfig $config) {
		$this->con = $con;
		$this->userId = $userId;
		$this->config = $config;
		$this->pollingId = time();
	}

	public function setLock() {
		$this->config->setUserValue($this->userId, 'ojsxc', 'longpolling', $this->pollingId);
	}

	public function stillLocked() {
		$sql = "SELECT `configvalue` FROM `*PREFIX*preferences` WHERE `userid` = ? AND `appid`='ojsxc' AND `configkey`='longpolling'";
		$q = $this->con->prepareQuery($sql);
		$r = $q->execute([$this->userId]);
		$r = $r->fetchRow();
		return (int) $r['configvalue'] === (int) $this->pollingId;
	}

}