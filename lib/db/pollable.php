<?php

namespace OCA\OJSXC\Db;

use OCP\AppFramework\Db\Entity;

abstract class Pollable extends Entity{
	/**
	 * @return mixed
	 */
	public function getTo() {
		return $this->to;
	}

	/**
	 * @return mixed
	 */
	public function getStanza() {
		return $this->stanza;
	}

	/**
	 * @param mixed $stanza
	 */
	public function setStanza($stanza) {
		$this->stanza = $stanza;
	}

	/**
	 * @return mixed
	 */
	public function getFrom() {
		return $this->from;
	}

	/**
	 * @param mixed $from
	 */
	public function setFrom($from) {
		$this->from = $from;
	}

	/**
	 * @param mixed $to
	 */
	public function setTo($to) {
		$this->to = $to;
	}

	public $to;

	public $from;

	public $stanza;
}