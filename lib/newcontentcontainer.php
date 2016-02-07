<?php

namespace OCA\OJSXC;
use OCA\OJSXC\Db\Stanza;

/**
 * Class NewContentContainer
 * Helper class to store new stanzas which will be returned in the current request.
 * @package OCA\OJSXC
 */
class NewContentContainer {

	/**
	 * @var Stanza[]
	 */
	private static $stanzas;

	public function addStanza($stanza) {
		self::$stanzas[] = $stanza;
	}

	public function getStanzas() {
		$tmp = self::$stanzas;
		self::$stanzas = [];
		return $tmp;
	}

	public function getCount() {
		return count(self::$stanzas);
	}
	
}