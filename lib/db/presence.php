<?php

namespace OCA\OJSXC\Db;

use Sabre\Xml\XmlSerializable;
use Sabre\Xml\XmlDeserializable;
use Sabre\Xml\Writer;
use Sabre\Xml\Reader;
use Sabre\Xml\Element\Base;
use Sabre\Xml\Element\keyValue;

/**
 * Class Presence
 *
 * This class is used for input AND output! It can be inserted into the ojsxc_presence table
 * and the ojsxc_stanza table. Use the presenceMapper and Stanzamapper respective.
 *
 * @package OCA\OJSXC\Db
 * @method void setUserid(string $userid)
 * @method void setPresence(string $presence)
 * @method void setLastActive(int $lastActive)
 * @method string getUserid()
 * @method string getPresence()
 * @method int getLastActive()
 */
class Presence extends Stanza implements XmlSerializable, XmlDeserializable{

	/**
	 * @var string $userid
	 */
	public $userid;

	/**
	 * @var string $presence
	 */
	public $presence;

	/**
	 * @var int last_active
	 */
	public $lastActive;

	public function xmlSerialize(Writer $writer) {
		if ($this->presence === 'online' || $this->presence === '') {
			$writer->write([
				[
					'name' => 'presence',
					'attributes' => [
						'xmlns' => 'jabber:client',
						'from' => $this->from,
						'to' => $this->to,
					],
					'value' => null
				]
			]);
		} else if ($this->presence === 'unavailable') {
			$writer->write([
				[
					'name' => 'presence',
					'attributes' => [
						'type' => 'unavailable',
						'from' => $this->from,
						'to' => $this->to,
						'xmlns' => 'jabber:client',
					],
					'value' => null
				]
			]);
		} else {
			$writer->write([
				[
					'name' => 'presence',
					'attributes' => [
							'from' => $this->from,
							'to' => $this->to,
							'xmlns' => 'jabber:client',
					],
					'value' => [ [
						'name' => 'show',
						'attributes' => [],
						'value' => $this->presence
					]]
				]
			]);
		}
	}

	/**
	 * @param Reader $reader
	 * @param string $userId
	 * @return Presence
	 */
	static function createFromXml(Reader $reader, $userId){
		$newElement = self::xmlDeserialize($reader);
		$newElement->setUserid($userId);
		return $newElement;
	}

	static function xmlDeserialize(Reader $reader) {
		$newElement = new self();
		$attributes = $reader->parseAttributes();
		$children = $reader->parseInnerTree();

		if (key_exists('type', $attributes) && $attributes['type'] === 'unavailable') {
			$newElement->presence = 'unavailable';
		} else if (is_null($children)) {
			$newElement->presence = 'online';
		} else {
			$foundShow = false;
			foreach ($children as $child) {
				if ($child['name'] === '{jabber:client}show') {
					$newElement->presence = $child['value'];
					$foundShow = true;
				}
			}
			if (!$foundShow) {
				$newElement->presence = 'online';
			}
		}

		$newElement->lastActive = time();
		return $newElement;
	}


}