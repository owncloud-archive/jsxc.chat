<?php

namespace OCA\OJSXC\Db;

use OCA\OJSXC\Db\Presence as PresenceEntity;
use OCA\OJSXC\Utility\MapperTestUtility;

/**
* @group DB
*/
class PresenceMapperTest extends MapperTestUtility {

	/**
	 * @var PresenceMapper
	 */
	protected $mapper;

	protected function setUp() {
		$this->entityName = 'OCA\OJSXC\Db\Presence';
		$this->mapperName = 'PresenceMapper';
		parent::setUp();
	}

	/**
	 * @return array
	 */
	public function presenceIfNotExitsProvider() {
		$input1 = new PresenceEntity();
		$input1->setPresence('online');
		$input1->setUserid('admin');
		$input1->setLastActive(23434);

		$input2 = new PresenceEntity();
		$input2->setPresence('unavailable');
		$input2->setUserid('derp');
		$input2->setLastActive(23434475);


		$input3 = new PresenceEntity();
		$input3->setPresence('chat');
		$input3->setUserid('derpina');
		$input3->setLastActive(23445645634);

		return [
			[
				[$input1, $input2, $input3],
				[
					[
						'id' => '11',
						'userid' => 'admin',
						'presence' => 'online',
						'last_active' => 23434,
					],
					[
						'id' => '12',
						'userid' => 'derp',
						'presence' => 'unavailable',
						'last_active' => 23434475,
					],
					[
						'id' => '13',
						'userid' => 'derpina',
						'presence' => 'chat',
						'last_active' => 23445645634
					]
				]
			]
		];
	}

	/**
	 * @dataProvider presenceIfNotExitsProvider
	 * Test setting the presence if it doesn't exits.
	 * @param PresenceEntity[] $inputs
	 * @param array $expected
	 */
	public function testSetPresenceIfNotExists($inputs, $expected) {
		foreach ($inputs as $input) {
			$this->mapper->setPresence($input);
		}
		$result = $this->fetchAllAsArray();

		$this->assertCount(count($expected), $result);

		for ($i=0; $i < count($result); $i++) {
			$this->assertEquals($expected[$i]['userid'], $result[$i]['userid']);
			$this->assertEquals($expected[$i]['presence'], $result[$i]['presence']);
			$this->assertEquals($expected[$i]['last_active'], $result[$i]['last_active']);
		}

	}

	/**
	 * @return array
	 */
	public function presenceIfExitsProvider() {
		$input1 = new PresenceEntity();
		$input1->setPresence('online');
		$input1->setUserid('admin');
		$input1->setLastActive(23434);

		$input2 = new PresenceEntity();
		$input2->setPresence('unavailable');
		$input2->setUserid('derp');
		$input2->setLastActive(23434475);


		$input3 = new PresenceEntity();
		$input3->setPresence('chat');
		$input3->setUserid('derpina');
		$input3->setLastActive(23445645634);

		$input4 = new PresenceEntity();
		$input4->setPresence('chat');
		$input4->setUserid('admin');
		$input4->setLastActive(3234343424);

		$input5 = new PresenceEntity();
		$input5->setPresence('online');
		$input5->setUserid('derp');
		$input5->setLastActive(23434353);

		return [
			[
				[$input1, $input2, $input3, $input4, $input5],
				[
					[
						'id' => '11',
						'userid' => 'admin',
						'presence' => 'chat',
						'last_active' => 3234343424,
					],
					[
						'id' => '12',
						'userid' => 'derp',
						'presence' => 'online',
						'last_active' => 23434353,
					],
					[
						'id' => '13',
						'userid' => 'derpina',
						'presence' => 'chat',
						'last_active' => 23445645634,
					]
				]
			]
		];
	}

	/**
	 * @dataProvider presenceIfExitsProvider
	 * Test setting the presence if it doesn't exits.
	 * @param PresenceEntity[] $inputs
	 * @param array $expected
	 */
	public function testSetPresenceIfExists($inputs, $expected) {
		foreach ($inputs as $input) {
			$this->mapper->setPresence($input);
		}
		$result = $this->fetchAllAsArray();

		$this->assertCount(count($expected), $result);

		for ($i=0; $i < count($result); $i++) {
			$this->assertEquals($expected[$i]['userid'], $result[$i]['userid']);
			$this->assertEquals($expected[$i]['presence'], $result[$i]['presence']);
			$this->assertEquals($expected[$i]['last_active'], $result[$i]['last_active']);
		}

	}

	public function getPresenceProvider() {
		$input1 = new PresenceEntity();
		$input1->setPresence('online');
		$input1->setUserid('admin');
		$input1->setLastActive(23434);

		$input2 = new PresenceEntity();
		$input2->setPresence('unavailable');
		$input2->setUserid('derp');
		$input2->setLastActive(23434475);


		$input3 = new PresenceEntity();
		$input3->setPresence('chat');
		$input3->setUserid('derpina');
		$input3->setLastActive(23445645634);

		$input4 = new PresenceEntity();
		$input4->setPresence('chat');
		$input4->setUserid('admin');
		$input4->setLastActive(3234343424);

		$input5 = new PresenceEntity();
		$input5->setPresence('online');
		$input5->setUserid('derp');
		$input5->setLastActive(23434353);

		$expected1 = new PresenceEntity();
		$expected1->setUserid('derp');
		$expected1->setPresence('online');
		$expected1->setLastActive(23434353);
		$expected1->setTo('admin@localhost');
		$expected1->setFrom('derp@localhost');

		$expected2 = new PresenceEntity();
		$expected2->setUserid('derpina');
		$expected2->setPresence('chat');
		$expected2->setLastActive(23445645634);
		$expected2->setTo('admin@localhost');
		$expected2->setFrom('derpina@localhost');
		return [
			[
				[$input1, $input2, $input3, $input4, $input5],
				[$expected1, $expected2]
			]
		];
	}
	
	/**
	 * @dataProvider getPresenceProvider
	 * @param $inputs
	 * @param $expected
	 */
	public function testGetPresence($inputs, $expected) {
		foreach ($inputs as $input) {
			$this->mapper->setPresence($input);
		}

		$result = $this->mapper->getPresences();

		$this->assertCount(count($expected), $result);
		for ($i=0; $i < count($result); $i++) {
			$this->assertEquals($expected[$i]->getUserid(), $result[$i]->getUserid());
			$this->assertEquals($expected[$i]->getPresence(), $result[$i]->getPresence());
			$this->assertEquals($expected[$i]->getLastActive(), $result[$i]->getLastActive());
			$this->assertEquals($expected[$i]->getTo(), $result[$i]->getTo());
			$this->assertEquals($expected[$i]->getFrom(), $result[$i]->getFrom());
		}
	}

	public function getConnectedUsersProvider() {
		$input1 = new PresenceEntity();
		$input1->setPresence('online');
		$input1->setUserid('admin');
		$input1->setLastActive(23434);

		$input2 = new PresenceEntity();
		$input2->setPresence('unavailable');
		$input2->setUserid('derp');
		$input2->setLastActive(23434475);

		$input3 = new PresenceEntity();
		$input3->setPresence('chat');
		$input3->setUserid('derpina');
		$input3->setLastActive(23445645634);

		$input4 = new PresenceEntity();
		$input4->setPresence('chat');
		$input4->setUserid('admin');
		$input4->setLastActive(3234343424);

		$input5 = new PresenceEntity();
		$input5->setPresence('online');
		$input5->setUserid('derp');
		$input5->setLastActive(23434353);

		$input6 = new PresenceEntity();
		$input6->setPresence('unavailable');
		$input6->setUserid('herp');
		$input6->setLastActive(123);

		return [
			[
				[$input1, $input2, $input3, $input4, $input5, $input6],
				['derp', 'derpina']
			]
		];
	}

	/**
	 * @dataProvider getConnectedUsersProvider
	 */
	public function testGetConnectedUsers($inputs, $expected) {
		foreach ($inputs as $input) {
			$this->mapper->setPresence($input);
		}

		$result = $this->mapper->getConnectedUsers();

		$this->assertCount(count($expected), $result);
		$this->assertEquals($expected, $result);
	}


}