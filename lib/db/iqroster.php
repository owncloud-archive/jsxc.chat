<?php

namespace OCA\OJSXC\Db;

use \OCP\AppFramework\Db\Entity;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlDeserializable;
use Sabre\Xml\XmlSerializable;

class IQRoster extends Stanza implements XmlSerializable{

	protected $type;
	protected $qid;
	protected $items;

	public function xmlSerialize(Writer $writer) {
		$writer->write([
			[
				'name' => 'iq',
				'attributes' => [
					'to' => $this->to,
					'type' => $this->type,
					'id' => $this->qid
				],
				'value' => [[
					'name' => 'query',
					'attributes' => [
						'xmlns' => 'jabber:iq:roster',
					],
					'value' => $this->items
				]]
			]
		]);
	}

	public function addItem($jid, $name){
		$this->items[] = [
			"name" => "item",
			"attributes" => [
				"jid" => $jid,
				"name" => $name
			],
			"value" => ''
		];
	}

}