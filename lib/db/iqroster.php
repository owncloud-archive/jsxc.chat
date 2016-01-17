<?php

namespace OCA\OJSXC\Db;

use Sabre\Xml\Reader;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlDeserializable;
use Sabre\Xml\XmlSerializable;


/**
 * This is an entity used by the IqHandler, but not stored/mapped in the database.
 * Class IQRoster
 *
 * @package OCA\OJSXC\Db
 */
class IQRoster extends Stanza implements XmlSerializable{

	public $type;
	public $qid;
	public $items;

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