<?php

namespace OCA\OJSXC\Db;

use OCA\OJSXC\Db\Presence as PresenceEntity;
use OCP\AppFramework\Db\Mapper;
use Sabre\Xml\Service;
use OCP\IDb;

/**
 * Class PresenceMapper
 *
 * @package OCA\OJSXC\Db
 */
class PresenceMapper extends Mapper {

	public function __construct(IDb $db) {
		parent::__construct($db, 'ojsxc_presence');
	}

	/**
	 * @param PresenceEntity $stanza
	 */
	public function setPresence(PresenceEntity $stanza) {
		$sql = "UPDATE `*PREFIX*ojsxc_presence` SET `presence`=?, `last_active`=? WHERE `userid` = ?";
		$q = $this->db->prepare($sql);
		$q->execute([$stanza->getPresence(), $stanza->getLastActive(), $stanza->getUser()]);

		if ($q->rowCount() === 0) {
			$sql = "INSERT INTO `*PREFIX*ojsxc_presence` (`userid`, `presence`, `last_active`) VALUES(?,?,?)";
			$q = $this->db->prepare($sql);
			$q->execute([$stanza->getUser(), $stanza->getPresence(), $stanza->getLastActive()]);
		}
	}

}