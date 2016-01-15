<?php
namespace OCA\OJSXC\Controller;

use OCA\OJSXC\Db\StanzaMapper;
use OCA\OJSXC\Http\XMPPResponse;
use OCA\OJSXC\StanzaHandlers\IQ;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit_Framework_TestCase;

class HttpBindControllerTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var HttpBindController
	 */
	private $controller;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $stanzaMapper;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $iqHandler;

	private $userId = 'john';

	public function setUp() {
	}

	/**
	 * Helper function to set up the controller. This can't be done in the setUp,
	 * since the requestBody is different for every test.
	 * @param $requestBody
	 */
	private function setUpController($requestBody) {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$session = $this->getMockBuilder('OCP\ISession')->getMock();
		$this->stanzaMapper = $this->getMockBuilder('OCA\OJSXC\Db\StanzaMapper')->getMock();

		$this->iqHandler = $this->getMockBuilder('OCA\OJSXC\StanzaHandlers\IQ')->getMock();
		$messageHandler = $this->getMockBuilder('OCA\OJSXC\StanzaHandlers\Message')->getMock();

		$this->controller = new HttpBindController(
			'ojsxc',
			$request,
			$this->userId,
			$session,
			$this->stanzaMapper,
			$this->iqHandler,
			$messageHandler,
			'localhost',
			$requestBody,
			0,
			10
		);
	}

	/**
	 * When invalid XML, just start long polling.
	 */
	public function testInvalidXML() {
		$ex = new DoesNotExistException();
		$expResponse = new XMPPResponse();

		$this->setUpController('<x>');
		$this->stanzaMapper->expects($this->exactly(10))
			->method('findByTo')
			->with('john@localhost')
			->will($this->throwException($ex));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
	}

	public function IQProvider() {
		return [
			[
				'<body rid=\'897878733\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><iq from=\'admin@localhost\' to=\'localhost\' type=\'get\' xmlns=\'jabber:client\' id=\'1:sendIQ\'><query xmlns=\'http://jabber.org/protocol/disco#info\' node=\'undefined#undefined\'/></iq><iq type=\'get\' xmlns=\'jabber:client\' id=\'2:sendIQ\'><query xmlns=\'jabber:iq:roster\'/></iq><iq type=\'get\' to=\'admin@localhost\' xmlns=\'jabber:client\' id=\'3:sendIQ\'><vCard xmlns=\'vcard-temp\'/></iq></body>',
				'<body xmlns="http://jabber.org/protocol/httpbind"><iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq></body>',
				$this->once()
			],
			[
				'<body rid=\'897878734\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><iq from=\'admin@localhost\' to=\'localhost\' type=\'get\' xmlns=\'jabber:client\' id=\'1:sendIQ\'><query xmlns=\'http://jabber.org/protocol/disco#info\' node=\'undefined#undefined\'/></iq><iq type=\'get\' xmlns=\'jabber:client\' id=\'2:sendIQ\'><query xmlns=\'jabber:iq:roster\'/></iq><iq type=\'get\' to=\'admin@localhost\' xmlns=\'jabber:client\' id=\'3:sendIQ\'><vCard xmlns=\'vcard-temp\'/></iq></body>',
				null,
				$this->exactly(10)
			]
		];
	}

	/**
	 * @dataProvider IQProvider
	 */
	public function testIQHandler($body, $result, $pollCount) {
		$ex = new DoesNotExistException();
		$this->setUpController($body);

		$expResponse = new XMPPResponse();
		$expResponse->write($result);

		$this->iqHandler->expects($this->any()) // FIXME
			->method('handle')
			->will($this->returnValue($result));

		$this->stanzaMapper->expects($pollCount)
			->method('findByTo')
			->with('john@localhost')
			->will($this->throwException($ex));


		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);

	}
	
}