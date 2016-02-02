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
use PHPUnit_Framework_MockObject_MockObject;


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

	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $presenceHandler;

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
		$this->stanzaMapper = $this->getMockBuilder('OCA\OJSXC\Db\StanzaMapper')->disableOriginalConstructor()->getMock();

		$this->iqHandler = $this->getMockBuilder('OCA\OJSXC\StanzaHandlers\IQ')->disableOriginalConstructor()->getMock();
		$this->messageHandler = $this->getMockBuilder('OCA\OJSXC\StanzaHandlers\Message')->disableOriginalConstructor()->getMock();
		$this->presenceHandler = $this->getMockBuilder('OCA\OJSXC\StanzaHandlers\Presence')->disableOriginalConstructor()->getMock();
		$this->lock = $this->getMockBuilder('OCA\OJSXC\ILock')->disableOriginalConstructor()->getMock();

		$logger = \OC::$server->getLogger();

		$this->controller = new HttpBindController(
			'ojsxc',
			$request,
			$this->userId,
			$this->stanzaMapper,
			$this->iqHandler,
			$this->messageHandler,
			'localhost',
			$this->lock,
			$logger,
			$this->presenceHandler,
			$requestBody,
			0,
			10
		);
	}

	/**
	 * When invalid XML, just start long polling.
	 * Note: this test will cause some errors in the owncloud.log:
	 * {"reqId":"HmbEV6qTWF68ii1G\/kz1","remoteAddr":"","app":"PHP","message":"XMLReader::read(): An Error Occured while reading at \/var\/www\/owncloud\/apps\/ojsxc\/vendor\/sabre\/xml\/lib\/Reader.php#66","level":0,"time":"2016-01-30T14:52:44+00:00","method":"--","url":"--"}
	 * {"reqId":"HmbEV6qTWF68ii1G\/kz1","remoteAddr":"","app":"PHP","message":"XMLReader::read(): An Error Occured while reading at \/var\/www\/owncloud\/apps\/ojsxc\/vendor\/sabre\/xml\/lib\/Reader.php#145","level":0,"time":"2016-01-30T14:52:44+00:00","method":"--","url":"--"}
	 */
	public function testInvalidXML() {
		$ex = new DoesNotExistException('');
		$expResponse = new XMPPResponse();

		$this->setUpController('<x>');
        $this->mockLock();
		$this->stanzaMapper->expects($this->exactly(10))
			->method('findByTo')
			->with('john')
			->will($this->throwException($ex));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
	}

	public function IQProvider() {
		$expStanza1 = new Stanza();
		$expStanza1->setStanza('<iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq><iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq><iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq>');

		$result1 = new Stanza();
		$result1->setStanza('<iq to="admin@localhost" type="result" id="2:sendIQ"><query xmlns="jabber:iq:roster"><item jid="derp@localhost" name="derp"></item></query></iq>');

		$result2 = new Stanza();
		$result2->setStanza(null);
		$expStanza2 = new Stanza();
		$expStanza2->setStanza(null);
		return [
			[
				'<body rid=\'897878733\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><iq from=\'admin@localhost\' to=\'localhost\' type=\'get\' xmlns=\'jabber:client\' id=\'1:sendIQ\'><query xmlns=\'http://jabber.org/protocol/disco#info\' node=\'undefined#undefined\'/></iq><iq type=\'get\' xmlns=\'jabber:client\' id=\'2:sendIQ\'><query xmlns=\'jabber:iq:roster\'/></iq><iq type=\'get\' to=\'admin@localhost\' xmlns=\'jabber:client\' id=\'3:sendIQ\'><vCard xmlns=\'vcard-temp\'/></iq></body>',
				$result1,
				$expStanza1, // we ask for 3 IQ's thus return 3 values
				$this->once(),
				$this->exactly(3)
			],
			[
				'<body rid=\'897878734\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><iq from=\'admin@localhost\' to=\'localhost\' type=\'get\' xmlns=\'jabber:client\' id=\'1:sendIQ\'><query xmlns=\'http://jabber.org/protocol/disco#info\' node=\'undefined#undefined\'/></iq><iq type=\'get\' xmlns=\'jabber:client\' id=\'2:sendIQ\'><query xmlns=\'jabber:iq:roster\'/></iq><iq type=\'get\' to=\'admin@localhost\' xmlns=\'jabber:client\' id=\'3:sendIQ\'><vCard xmlns=\'vcard-temp\'/></iq></body>',
				$result2,
				$expStanza2,
				$this->once(),
				$this->exactly(3)
			]
		];
	}

	/**
	 * @dataProvider IQProvider
	 */
	public function testIQHandlerWhenNoDbResults($body, $result, $expected, $pollCount, $handlerCount) {
		$ex = new DoesNotExistException('');
		$this->setUpController($body);
        $this->mockLock();
		$expResponse = new XMPPResponse();
		$expResponse->write($expected);

		$this->iqHandler->expects($handlerCount)
			->method('handle')
			->will($this->returnValue($result));

		$this->stanzaMapper->expects($pollCount)
			->method('findByTo')
			->with('john')
			->will($this->throwException($ex));


		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());

	}

	public function testDbResults() {
		$result = new Stanza('test');
		$this->setUpController('<body rid=\'897878797\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'/>');
        $this->mockLock();

		$expResponse = new XMPPResponse($result);

		$this->iqHandler->expects($this->never())
			->method('handle')
			->will($this->returnValue($result));

		$r1 = $this->getMockBuilder('OCA\OJSXC\Db\Stanza')->disableOriginalConstructor()->getMock();
		$r1->expects($this->once())
			->method('xmlSerialize')
			->will($this->returnCallback(function(Writer $writer){
				$writer->write('test');
			}));

		$this->stanzaMapper->expects($this->once())
			->method('findByTo')
			->with('john')
			->will($this->returnValue([$r1]));


		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}

	public function testMessageNoDbHandler() {
		$body = '<body rid=\'897878959\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><message to=\'derp@own.dev\' type=\'chat\' id=\'1452960296859-msg\' xmlns=\'jabber:client\'><body>abc</body><request xmlns=\'urn:xmpp:receipts\'/></message></body>';
		$ex = new DoesNotExistException('');
		$this->setUpController($body);
        $this->mockLock();

		$expResponse = new XMPPResponse();

		$this->messageHandler->expects($this->once())
			->method('handle');

		$this->stanzaMapper->expects($this->exactly(10))
			->method('findByTo')
			->with('john')
			->will($this->throwException($ex));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}


	public function testMultipleMessageNoDbHandler() {
		$body = <<<XML
		<body rid='897878959' xmlns='http://jabber.org/protocol/httpbind' sid='7862'>
			<message to='derp@own.dev' type='chat' id='1452960296859-msg' xmlns='jabber:client'><body>abc</body></message>
			<message to='derp@own.dev' type='chat' id='1452960296860-msg' xmlns='jabber:client'><body>abc2</body></message>
			<message to='derp@own.dev' type='chat' id='1452960296861-msg' xmlns='jabber:client'><body>abc3</body></message>
		</body>
XML;
		$ex = new DoesNotExistException('');
		$this->setUpController($body);
		$this->mockLock();

		$expResponse = new XMPPResponse();
		$this->messageHandler->expects($this->any())
			->method('handle');

		$this->messageHandler->expects($this->exactly(3))
			->method('handle')
			->withConsecutive(
				$this->equalTo(
				[	'name' => '{jabber:client}message',
				'	value' => [
					'{jabber:client}body' => 'abc',
				],
				'attributes' => [
					'to' => 'derp@own.dev',
					'type' => 'chat',
					'id' => '1452960296859-msg',
				]]),
				$this->equalTo([	'name' => '{jabber:client}message',
					'value' => [
					'{jabber:client}body' => 'abc2',
				],	'attributes' => [
					'to' => 'derp@own.dev',
					'type' => 'chat',
					'id' => '1452960296860-msg',
				]]),
				$this->equalTo([	'name' => '{jabber:client}message',
					'value' => [
						'{jabber:client}body' => 'abc3',
					],	'attributes' => [
					'to' => 'derp@own.dev',
					'type' => 'chat',
					'id' => '1452960296861-msg',
				]])

			);

		$this->stanzaMapper->expects($this->exactly(10))
			->method('findByTo')
			->with('john')
			->will($this->throwException($ex));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}

	public function testMessageDbHandler() {
		$body = '<body rid=\'897878959\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><message to=\'derp@own.dev\' type=\'chat\' id=\'1452960296859-msg\' xmlns=\'jabber:client\'><body>abc</body><request xmlns=\'urn:xmpp:receipts\'/></message></body>';
		$this->setUpController($body);
        $this->mockLock();

		$expResponse = new XMPPResponse(new Stanza('test'));

		$this->messageHandler->expects($this->once())
			->method('handle');

		$r1 = $this->getMockBuilder('OCA\OJSXC\Db\Stanza')->disableOriginalConstructor()->getMock();
		$r1->expects($this->once())
			->method('xmlSerialize')
			->will($this->returnCallback(function(Writer $writer){
				$writer->write('test');
			}));

		$this->stanzaMapper->expects($this->once())
			->method('findByTo')
			->with('john')
			->will($this->returnValue([$r1]));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}

	/**
	 * @TODO implement tests
	 */
	public function testPresenceHandler() {
		$this->markTestSkipped();
		$this->markTestIncomplete();
		$body = '<body rid=\'897878985\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'><presence xmlns=\'jabber:client\'><c xmlns=\'http://jabber.org/protocol/caps\' hash=\'sha-1\' node=\'http://jsxc.org/\' ver=\'u2kAg/CbVmVZhsu+lZrkuLLdO+0=\'/><show>chat</show></presence></body>';
		$this->setUpController($body);
        $this->mockLock();

		$this->controller->index();
	}

	public function testBodyHandler() {
		$ex = new DoesNotExistException('');
		$body = '<body rid=\'897878985\' xmlns=\'http://jabber.org/protocol/httpbind\' sid=\'7862\'/>';
		$this->setUpController($body);
        $this->mockLock();
		$expResponse = new XMPPResponse();

		$this->stanzaMapper->expects($this->exactly(10))
			->method('findByTo')
			->with('john')
			->will($this->throwException($ex));

		$response = $this->controller->index();
		$this->assertEquals($expResponse, $response);
		$this->assertEquals($expResponse->render(), $response->render());
	}

}
