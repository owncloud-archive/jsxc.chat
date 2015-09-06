<?php
namespace OCA\OJSXC\StanzaHandlers;

use OCA\OJSXC\Db\MessageMapper;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

class StanzaHandler {

	/**
	 * @var array $stanza
	 */
	protected $stanza;

	protected $userId;

	protected $host;

	protected $to;

	public function __construct(Array $stanza, $userId, $host, MessageMapper $messageMapper) {
		$this->stanza = $stanza;
		$this->userId = $userId;
		$this->host = $host;
		$this->to = $this->getAttribute($this->stanza, 'to');
		$this->from = $this->userId . '@' . $this->host;


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
