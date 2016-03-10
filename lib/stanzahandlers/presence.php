<?php

namespace OCA\OJSXC\StanzaHandlers;

use OCA\OJSXC\Db\PresenceMapper;
use OCA\OJSXC\Db\StanzaMapper;
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

	/**
	 * @var StanzaMapper $stanzaMapper
	 */
	private $stanzaMapper;

	/**
	 * Presence constructor.
	 *
	 * @param $userId
	 * @param string $host
	 * @param PresenceMapper $presenceMapper
	 * @param StanzaMapper $stanzaMapper
	 */
	public function __construct($userId, $host, PresenceMapper $presenceMapper, StanzaMapper $stanzaMapper) {
		parent::__construct($userId, $host);
		$this->presenceMapper = $presenceMapper;
		$this->stanzaMapper = $stanzaMapper;
	}

	/**
	 * This function is called when a client/user updates it's presence.
	 * This function should:
	 *  - update the presence in the database
	 *  - broadcast the presence
	 *  - return the active presence if the type isn't equal to unavailable
	 * @param PresenceEntity $presence
	 * @return PresenceEntity[]
	 */
	public function handle(PresenceEntity $presence) {
		// update the presence
		$this->presenceMapper->setPresence($presence);

		// broadcast the presence
		$connectedUsers = $this->presenceMapper->getConnectedUsers(); // fetch connected users

		// build stanza to send to the users
		$presenceToSend = new PresenceEntity();
		$presenceToSend->setPresence($presence->getPresence());
		$presenceToSend->setFrom($this->userId);
		foreach ($connectedUsers as $user) {
			$presenceToSend->setTo($user);
			$this->stanzaMapper->insertStanza($presenceToSend);
		}

		if ($presence->getPresence() !== 'unavailable') {
			// return other users presence
			return $this->presenceMapper->getPresences();
		} else {
			return [];
		}
	}

}