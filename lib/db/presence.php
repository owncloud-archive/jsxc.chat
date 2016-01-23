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
 * @method void setUser(string $user)
 * @method void setPresence(string $presence)
 * @method void setLastActive(int $lastActive)
 * @method string getUser()
 * @method string getPresence()
 * @method int getLastActive()
 */
class Presence extends Stanza implements XmlSerializable, XmlDeserializable{

	/**
	 * @var string $user
	 */
	public $user;

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
				'name' => 'presence',
				'attributes' => [],
				'value' => ''
			]);
		} else {

		}
	}

	/**
	 * @param Reader $reader
	 * @param string $userId
	 * @return Presence
	 */
	static function createFromXml(Reader $reader, $userId){
		$newElement = self::xmlDeserialize($reader);
		$newElement->setUser($userId);
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