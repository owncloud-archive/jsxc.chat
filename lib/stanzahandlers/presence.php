<?php

namespace OCA\OJSXC\StanzaHandlers;

use OCA\OJSXC\Db\PresenceMapper;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;
use OCA\OJSXC\Db\Presence as PresenceEntity;

/**
 * Class Presence
 *
 * @package OCA\OJSXC\StanzaHandlers
 */
class Presence extends StanzaHandler {

	/**
	 * @var PresenceMapper $presenceMapper
	 */
	private $presenceMapper;

	public function __construct($userId, $host, PresenceMapper $presenceMapper) {
		parent::__construct($userId, $host);
		$this->presenceMapper = $presenceMapper;
	}

	/**
	 * This function is called when a client/user updates it's presence.
	 * This function should:
	 *  - update the presence in the database
	 *  - broadcast the presence
	 * @param PresenceEntity $stanza
	 */
	public function handle(PresenceEntity $stanza) {
		$this->presenceMapper->setPresence($stanza);
	}

}