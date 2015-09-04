<?php

namespace OCA\OJSXC\Db;

use \OCP\AppFramework\Db\Entity;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlDeserializable;
use Sabre\Xml\XmlSerializable;

class Message extends Entity implements XmlSerializable{

	protected $to;
	protected $from;
	protected $type;
	protected $msg;

	function xmlSerialize(Writer $writer) {
		$writer->write([
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
	}
}