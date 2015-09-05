<?php

namespace OCA\OJSXC\StanzaHandlers;

use OCA\OJSXC\Db\MessageMapper;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

class Message {

	/**
	 * @var \SimpleXMLElement
	 */
	private $stanza;

	private $messageMapper;

	private $type;

	private $to;

	private $msg;

	private $msgId;

	private $userId;

	private $host;

	public function __construct(Array $stanza, $userId, $host, MessageMapper $messageMapper) {
		$this->stanza = $stanza;
		$this->userId = $userId;
		$this->host = $host;
		$this->messageMapper = $messageMapper;

	}

	public function handle() {
		$this->from = $this->userId . '@' . $this->host;
		$this->parse();
		$this->messageMapper->insert($this->createStanzaToSend());
	}

	private function createStanzaToSend() {
		$message = new \OCA\OJSXC\Db\Message();
		$message->setTo($this->to);
		$message->setFrom($this->from);
		$message->setMsg($this->msg);
		$message->setType($this->type);
		return $message;
	}

	/**
	 * @brief parses all attributes from the stanza and place it in the properties of this class
	 */
	private function parse() {
		$this->msg = (string) $this->stanza['value']['{jabber:client}body'];
		$this->to = $this->getAttribute($this->stanza, 'to');
		$this->type = $this->getAttribute($this->stanza, 'type');
		$this->msgId = $this->getAttribute($this->stanza, 'id');
	}

	/**
	 * @param \SmpleXMLElement $el
	 * @param $attr
	 * @return null|string
	 * @brief checks if an attributes is set inside an \SimpleXMLElement element and returns the "first" element of that attribute after casting it to a string
	 */
	private function getAttribute($stanza, $attr){
		return isset($stanza['attributes'][$attr]) ? (string) $stanza['attributes'][$attr] : null;
	}

}