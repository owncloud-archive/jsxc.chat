<?php

namespace OCA\OJSXC\StanzaHandlers;

use Sabre\Xml\Writer;

class Message {

	/**
	 * @var \SimpleXMLElement
	 */
	private $stanza;

	private $type;

	private $to;

	private $msg;

	private $rid;

	private $sid;

	private $msgId;

	private $userId;

	private $host;

	public function __construct(\SimpleXMLElement $stanza, $userId, $host) {
		$this->stanza = $stanza;
		$this->userId = $userId;
		$this->host = $host;
	}

	public function handle() {
		$this->from = $this->userId . '@' . $this->host;
		$this->parse();
		if (!is_null($this->to)){
			$this->send($this->createStanzaToSend());
		}
	}

	private function createStanzaToSend() {
		$message = new Writer();
		$message->openMemory();
		$message->write([
			[
				'name' => 'message',
				'attributes' => [
					'to' => $this->to,
					'from' => $this->from,
					'type' => $this->type,
				],
				'value' => ['body' => $this->msg]
			]
		]);
		return $message;
	}

	/**
	 * @brief parses all attributes from the stanza and place it in the properties of this class
	 */
	private function parse() {
		$this->rid = $this->getAttribute($this->stanza, 'rid');
		$this->sid = $this->getAttribute($this->stanza, 'sid');
		$this->msg = (string) $this->stanza->message->body[0];
		$this->to = $this->getAttribute($this->stanza->message, 'to');
		$this->type = $this->getAttribute($this->stanza->message, 'type');
		$this->msgId = $this->getAttribute($this->stanza->message, 'id');
	}

	/**
	 * @param \SmpleXMLElement $el
	 * @param $attr
	 * @return null|string
	 * @brief checks if an attributes is set inside an \SimpleXMLElement element and returns the "first" element of that attribute after casting it to a string
	 */
	private function getAttribute(\SimpleXMLElement $el, $attr){
		return isset($el[$attr]) ? (string) $el[$attr][0] : null;
	}

	private function send(Writer $stanza){
		$q = \OCP\DB::prepare('INSERT INTO *PREFIX*ojsx_stanzas (`to`, `from`, `stanza`) VALUES(?,?,?)');
		$q->execute(array($this->to, $this->from, $stanza->outputMemory()));

	}
	

}