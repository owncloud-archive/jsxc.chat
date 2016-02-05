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

	/**
	 * PresenceMapper constructor.
	 *
	 * @param IDb $db
	 * @param string $host
	 * @param null|string $userId
	 */
	public function __construct(IDb $db, $host, $userId) {
		parent::__construct($db, 'ojsxc_presence');
		$this->host = $host;
		$this->userId = $userId;
	}

	/**
	 * @brief This function sets or update the presence of a user.
	 * @param PresenceEntity $stanza
	 */
	public function setPresence(PresenceEntity $stanza) {
		$sql = "UPDATE `*PREFIX*ojsxc_presence` SET `presence`=?, `last_active`=? WHERE `userid` = ?";
		$q = $this->db->prepare($sql);
		$q->execute([$stanza->getPresence(), $stanza->getLastActive(), $stanza->getUserid()]);

		if ($q->rowCount() === 0) {
			$sql = "INSERT INTO `*PREFIX*ojsxc_presence` (`userid`, `presence`, `last_active`) VALUES(?,?,?)";
			$q = $this->db->prepare($sql);
			$q->execute([$stanza->getUserid(), $stanza->getPresence(), $stanza->getLastActive()]);
		}
	}

	/**
	 * @brief this function will fetch all the presences of users except
	 * the current user.
	 * @return array
	 */
	public function getPresences() {
		$stmt = $this->execute("SELECT * FROM `*PREFIX*ojsxc_presence` WHERE `userid` != ?", [$this->userId]);
		$results = [];
		while($row = $stmt->fetch()){
			$row['from'] = $row['userid'] . '@' . $this->host;
			$row['to'] = $this->userId . '@' . $this->host;
			$results[] = $this->mapRowToEntity($row);
		}
		$stmt->closeCursor();

		return $results;
	}

	/**
	 * @brief fetch the users who are connected with the server.
	 *  - online
	 *  - chatty
	 *  - away
	 *  - extended away
	 *  - do not disturb
	 * and return it as an array of the userids.
	 * @return array
	 */
	public function getConnectedUsers() {
		$stmt = $this->execute("SELECT `userid` FROM `*PREFIX*ojsxc_presence` WHERE `presence` != 'unavailable' AND `userid` != ?", [$this->userId]);
		$results = [];
		while($row = $stmt->fetch()){
			$results[] = $row['userid'];
		}
		$stmt->closeCursor();

		return $results;
	}


}