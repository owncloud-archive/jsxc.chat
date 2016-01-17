<?php

namespace OCA\OJSXC\Db;

use \OCP\AppFramework\Db\Entity;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlDeserializable;
use Sabre\Xml\XmlSerializable;

class Message extends Stanza implements XmlSerializable{

	public $type;
	public $values;

	public function xmlSerialize(Writer $writer) {
		$writer->write([
			[
				'name' => 'message',
				'attributes' => [
					'to' => $this->to,
					'from' => $this->from,
					'type' => $this->type,
					'xmlns' => 'jabber:client',
					'id' => uniqid() . '-msg'
				],
				'value' => $this->values
			]
		]);
	}

}