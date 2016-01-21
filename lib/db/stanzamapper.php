<?php

namespace OCA\OJSXC\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\Mapper;
use OCP\IDb;
use Sabre\Xml\Writer;

class StanzaMapper extends Mapper {

	private $host;

	public function __construct(IDb $db, $host) {
		parent::__construct($db, 'ojsxc_stanzas');
		$this->host = $host;
	}

	public function insert(Entity $entity) {
		$writer = new Writer();
		$writer->openMemory();
		$writer->write($entity);
		$xml = $writer->outputMemory();
		$sql = "INSERT INTO `*PREFIX*ojsxc_stanzas` (`to`, `from`, `stanza`) VALUES(?,?,?)";
		$q = $this->db->prepare($sql);
		$q->execute([$entity->getTo(), $entity->getFrom(), $xml]);
	}


	public function findByTo($to){
		$stmt = $this->execute("SELECT stanza, id FROM *PREFIX*ojsxc_stanzas WHERE `to`=?", [$to]);
		$results = [];
		while($row = $stmt->fetch()){
			$row['stanza'] =preg_replace('/to="([a-zA-z]*)"/', "to=\"$1@" .$this->host ."\"", $row['stanza']);
			$row['stanza'] = preg_replace('/from="([a-zA-z]*)"/', "from=\"$1@" .$this->host ."\"", $row['stanza']);
			$results[] = $this->mapRowToEntity($row);
		}
		$stmt->closeCursor();

		if (count($results) === 0){
			throw new DoesNotExistException('Not Found');
		}

		foreach($results as $result){
			$this->delete($result);
		}
		return $results;
	}

	private function replaceHostname($input) {
		return str_replace('@{hostPlaceholder}', '@' . $this->host, $input);
	}
}