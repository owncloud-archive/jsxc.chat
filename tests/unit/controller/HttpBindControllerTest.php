<?php
namespace OCA\OJSXC\Controller;

use OCA\OJSXC\Db\Message;
use OCA\OJSXC\Db\Stanza;
use OCA\OJSXC\Db\StanzaMapper;
use OCA\OJSXC\Http\XMPPResponse;
use OCA\OJSXC\StanzaHandlers\IQ;
use OCP\AppFramework\Db\DoesNotExistException;
use PHPUnit_Framework_TestCase;
use Sabre\Xml\Writer;

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

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $messageHandler;

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $lock;

	private $userId = 'john';


	public function setUp() {
	}

    private function mockLock() {
		$this->lock->expects($this->any())
			->method('setLock')
			->will($this->returnValue(null));

		$this->lock->expects($this->any())
			->method('stillLocked')
			->will($this->returnValue(true));
    }

	/**
	 * Helper function to set up the controller. This can't be done in the setUp,
	 * since the requestBody is different for every test.
	 * @param $requestBody
	 */
	private function setUpController($requestBody) {
		$request = $this->getMockBuilder('OCP\IRequest')->disableOriginalConstructor()->getMock();
		$session = $this->getMockBuilder('OCP\ISession')->disableOriginalConstructor()->getMock();
		$this->stanzaMapper = $this->getMockBuilder('OCA\OJSXC\Db\StanzaMapper')->disableOriginalConstructor()->getMock();

		$this->iqHandler = $this->getMockBuilder('OCA\OJSXC\StanzaHandlers\IQ')->disableOriginalConstructor()->getMock();
		$this->messageHandler = $this->getMockBuilder('OCA\OJSXC\StanzaHandlers\Message')->disableOriginalConstructor()->getMock();
		$this->lock = $this->getMockBuilder('OCA\OJSXC\ILock')->disableOriginalConstructor()->getMock();

		$this->controller = new HttpBindController(
			'ojsxc',
			$request,
			$this->userId,
			$session,
			$this->stanzaMapper,
			$this->iqHandler,
			$this->messageHandler,
			'localhost',
			$this->lock,
			$requestBody,
			0,
			10
		);
	}

	/**
	 * When invalid XML, just start long polling.
	 */
	public function testInvalidXML() {
		$ex = new DoesNotExistException('');
		$expResponse = new XMPPResponse();

		$this->setUpController('<x>');
        $this->mockLock();
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
				'<iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq>',
				'<iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq><iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq><iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq>', // we ask for 3 IQ's thus return 3 values
				$this->once()
			],
			[
				'<body rid=\'897878734\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><iq from=\'admin@localhost\' to=\'localhost\' type=\'get\' xmlns=\'jabber:client\' id=\'1:sendIQ\'><query xmlns=\'http://jabber.org/protocol/disco#info\' node=\'undefined#undefined\'/></iq><iq type=\'get\' xmlns=\'jabber:client\' id=\'2:sendIQ\'><query xmlns=\'jabber:iq:roster\'/></iq><iq type=\'get\' to=\'admin@localhost\' xmlns=\'jabber:client\' id=\'3:sendIQ\'><vCard xmlns=\'vcard-temp\'/></iq></body>',
				null,
				null,
				$this->exactly(10)
			]
		];
	}

	/**
	 * @dataProvider IQProvider
	 */
	public function testIQHandlerWhenNoDbResults($body, $result, $expected, $pollCount) {
		$ex = new DoesNotExistException();
		$this->setUpController($body);
        $this->mockLock();
		$expResponse = new XMPPResponse();
		$expResponse->write($expected);

		$this->iqHandler->expects($this->any()) // FIXME
			->method('handle')
			->will($this->returnValue($result));

		$this->stanzaMapper->expects($pollCount)
			->method('findByTo')
			->with('john@localhost')
			->will($this->throwException($ex));


		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());

	}

	public function testDbResults() {
		$result = 'test';
		$this->setUpController('<body rid=\'897878797\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'/>');
        $this->mockLock();

		$expResponse = new XMPPResponse();
		$expResponse->write($result);

		$this->iqHandler->expects($this->any()) // FIXME
			->method('handle')
			->will($this->returnValue($result));

		$r1 = $this->getMockBuilder('Sabre\XML\XmlSerializable')->disableOriginalConstructor()->getMock();
		$r1->expects($this->once())
			->method('xmlSerialize')
			->will($this->returnCallback(function(Writer $writer){
				$writer->write('test');
			}));

		$this->stanzaMapper->expects($this->once())
			->method('findByTo')
			->with('john@localhost')
			->will($this->returnValue([$r1]));


		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}

	public function testMessageNoDbHandler() {
		$body = '<body rid=\'897878959\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><message to=\'derp@own.dev\' type=\'chat\' id=\'1452960296859-msg\' xmlns=\'jabber:client\'><body>abc</body><request xmlns=\'urn:xmpp:receipts\'/></message></body>';
		$ex = new DoesNotExistException();
		$this->setUpController($body);
        $this->mockLock();

		$expResponse = new XMPPResponse();

		$this->messageHandler->expects($this->any()) // FIXME
			->method('handle');

		$this->stanzaMapper->expects($this->exactly(10))
			->method('findByTo')
			->with('john@localhost')
			->will($this->throwException($ex));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}

	public function testMessageDbHandler() {
		$body = '<body rid=\'897878959\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><message to=\'derp@own.dev\' type=\'chat\' id=\'1452960296859-msg\' xmlns=\'jabber:client\'><body>abc</body><request xmlns=\'urn:xmpp:receipts\'/></message></body>';
		$this->setUpController($body);
        $this->mockLock();

		$expResponse = new XMPPResponse();
		$expResponse->write('test');

		$this->messageHandler->expects($this->any()) // FIXME
		->method('handle');

		$r1 = $this->getMockBuilder('Sabre\XML\XmlSerializable')->disableOriginalConstructor()->getMock();
		$r1->expects($this->once())
			->method('xmlSerialize')
			->will($this->returnCallback(function(Writer $writer){
				$writer->write('test');
			}));

		$this->stanzaMapper->expects($this->once())
			->method('findByTo')
			->with('john@localhost')
			->will($this->returnValue([$r1]));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}

	public function testPresenceHandler() {
		$body = '<body rid=\'897878985\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><presence xmlns=\'jabber:client\'><c xmlns=\'http://jabber.org/protocol/caps\' hash=\'sha-1\' node=\'http://jsxc.org/\' ver=\'u2kAg/CbVmVZhsu+lZrkuLLdO+0=\'/><show>chat</show></presence></body>';
		$this->setUpController($body);
        $this->mockLock();

		$this->controller->index();
	}

	public function testBodyHandler() {
		$body = '<body rid=\'897878985\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'/>';
		$this->setUpController($body);
        $this->mockLock();

		$this->controller->index();
	}

   
	
}
