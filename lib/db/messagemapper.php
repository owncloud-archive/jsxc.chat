<?php

namespace OCA\OJSXC\Db;

use OCP\AppFramework\Db\Mapper;
use OCP\IDb;

use OCA\OJSXC\Db\Message;
use Sabre\Xml\Writer;

class MessageMapper extends Mapper {

	public function __construct(IDb $db) {
		parent::__construct($db, 'ojsxc_stanzas');
	}

	public function insert(Message $message) {
		$writer = new Writer();
		$writer->openMemory();
		$writer->write($message);
		$xml = $writer->outputMemory();
		$sql = "INSERT INTO `' . $this->tableName . '` (`to`, `from`, `stanza`) VALUES(?,?,?)";

		$q = $this->db->prepare($sql);
		$q->execute([$this->to, $this->from, $xml]);

	}



}