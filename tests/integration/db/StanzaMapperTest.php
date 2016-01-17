<?php

namespace OCA\OJSXC\Db;

use OCA\OJSXC\Utility\MapperTestUtility;

/**
 * @group DB
 */
class StanzaMapperTest extends MapperTestUtility {

	/**
	 * @var StanzaMapper
	 */
	protected $mapper;

	protected function setUp() {
		$this->entityName = 'OCA\OJSXC\Db\Stanza';
		$this->mapperName = 'StanzaMapper';
		parent::setUp();
	}

	public function insertProvider() {
		return [
			[
				'john@localhost',
				'thomas@localhost',
				'abcd'
			]
		];
	}
	
	/**
	 * @dataProvider insertProvider
	 */
	public function testInsert($from, $to, $data) {
		$stanza = new Stanza();
		$stanza->setFrom($from);
		$stanza->setTo($to);
		$stanza->setStanza($data);

		$this->assertEquals($stanza->getFrom(), $from);
		$this->assertEquals($stanza->getTo(), $to);
		$this->assertEquals($stanza->getStanza(), $data);

		$this->mapper->insert($stanza);

		$result = $this->fetchAll();

		$this->assertCount(1, $result);
		$this->assertEquals($stanza->getFrom(), $result[0]->getFrom());
		$this->assertEquals($stanza->getTo(),  $result[0]->getTo());
		$this->assertEquals($stanza->getStanza(),  $result[0]->getStanza());
	}



}