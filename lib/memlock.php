<?php

namespace OCA\OJSXC;

use OCP\Files\Cache\ICache;
use OCP\ICacheFactory;
use OCP\IMemcache;
use OCP\IMemcacheTTL;

class MemLock implements ILock {

	/**
	 * @var \OCP\ICache
	 */
	private $memcache;

	private $userId;

	public function __construct($userId, ICacheFactory $cache) {
		$this->userId = $userId;
		if ($cache->isAvailable()) {
			$this->memcache = $cache->create('ojsxc');
		} else {
			die('No memcache available'); // TODO
		}
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