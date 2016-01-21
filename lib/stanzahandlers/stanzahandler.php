<?php
namespace OCA\OJSXC\StanzaHandlers;

use OCA\OJSXC\Db\MessageMapper;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

class StanzaHandler {

	protected $userId;

	protected $host;

	protected $to;

	public function __construct($userId, $host) {
		$this->userId = $userId;
		$this->host = $host;
		$this->from = $this->userId;
	}

	/**
	 * @param $stanza
	 * @param $attr
	 * @return null|string
	 */
	protected function getAttribute($stanza, $attr){
		return isset($stanza['attributes'][$attr]) ? (string) $stanza['attributes'][$attr] : null;
	}


}
