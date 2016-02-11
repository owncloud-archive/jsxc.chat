<?php

namespace OCA\OJSXC\Db;

interface IPollable {

	public function setTo($to);
	public function getTo();

	public function setFrom($from);
	public function getFrom();

	public function setStanza($stanza);
	public function getStanza();
}
