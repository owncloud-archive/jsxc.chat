<?php

namespace OCA\OJSXC\StanzaHandlers;

use OCA\OJSXC\Db\MessageMapper;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

class Message extends StanzaHandler {

	private $messageMapper;

	private $type;

	private $values;

	private $msgId;

	public function __construct($userId, $host, MessageMapper $messageMapper) {
		parent::__construct($userId, $host);
		$this->messageMapper = $messageMapper;
	}

	public function handle($stanza) {
		$to = $this->getAttribute($stanza, 'to');
		$pos = strpos($to, '@');
		$this->to = substr($to, 0, $pos);
		foreach($stanza['value'] as $keyRaw=>$value) {
			// remove namespace from key as it is unneeded and cause problems
			$key = substr($keyRaw, strpos($keyRaw, '}') + 1, strlen($keyRaw));
			// fetch namespace from key to readd it
			$ns = substr($keyRaw, 1, strpos($keyRaw, '}')-1);

			$this->values[] = [
				"name" => $key,
				"value" => (string)$value,
				"attributes" => ["xmlns" => $ns]
			];
		}
		$this->type = $this->getAttribute($stanza, 'type');
		$this->msgId = $this->getAttribute($stanza, 'id');

		$message = new \OCA\OJSXC\Db\Message();
		$message->setTo($this->to);
		$message->setFrom($this->from);
		$message->setValues($this->values);
		$message->setType($this->type);
		$this->messageMapper->insert($message);
		$this->values = [];
	}

}