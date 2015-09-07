<?php

namespace OCA\OJSXC\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\Mapper;

use Sabre\Xml\Writer;

class StanzaMapper extends Mapper {

	public function __construct(IDb $db) {
		parent::__construct($db, 'ojsxc_stanzas');
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
		$results = $this->findEntities("SELECT stanza, id FROM *PREFIX*ojsxc_stanzas WHERE `to`=?", [$to]);
		if (count($results) === 0){
			throw new DoesNotExistException('Not Found');
		}
		foreach($results as $result){
			$this->delete($result);
		}
		return $results;
	}
}