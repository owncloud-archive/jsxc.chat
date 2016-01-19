<?php

namespace OCA\OJSXC;

interface ILock {

	public function setLock();

	public function stillLocked();

}