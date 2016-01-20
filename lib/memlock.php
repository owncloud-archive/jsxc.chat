<?php

namespace OCA\OJSXC;

use OCP\ICache;

class MemLock implements ILock {

	/**
	 * @var \OCP\ICache
	 */
	private $memcache;

	private $userId;

	public function __construct($userId, ICache $cache) {
		$this->userId = $userId;
		$this->memcache = $cache;
		$this->pollingId = time();
	}

	public function setLock() {
		$this->memcache->remove('-' . $this->userId . '-ojxsc-lock');
		$this->memcache->add('-' . $this->userId . '-ojxsc-lock', $this->pollingId);
	}

	public function stillLocked() {
		$r = $this->memcache->get('-' . $this->userId . '-ojxsc-lock');
		return $r == $this->pollingId;
	}

}