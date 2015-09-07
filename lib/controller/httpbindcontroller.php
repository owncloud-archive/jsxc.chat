<?php

namespace OCA\OJSXC\Controller;

use OCA\OJSXC\Db\StanzaMapper;
use OCA\OJSXC\Db\MessageMapper;
use OCA\OJSXC\Http\XMPPResponse;
use OCA\OJSXC\StanzaHandlers\IQ;
use OCA\OJSXC\StanzaHandlers\Message;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IRequest;


use OCP\ISession;
use Sabre\Xml\Writer;
use Sabre\Xml\Reader;
use Sabre\Xml\LibXMLException;

class HttpBindController extends Controller {

	private $userId;

	const MESSAGE=0;
	const IQ=1;
	const PRESENCE=2;
	const BODY=2;

	private $pollingId;

	private $host;

	/**
	 * @var Session OCP\ISession
	 */
	private $session;

	/**
	 * @var MessageMapper OCA\OJSXC\Db\MessageMapper
	 */
	private $messageMapper;

	/**
	 * @var StanzaMapper OCA\OJSXC\Db\StanzaMapper
	 */
	private $stanzaMapper;

	/**
	 * @var XMPPResponse
	 */
	private $response;

	public function __construct($appName,
	                            IRequest $request,
								$userId,
								ISession $session,
								MessageMapper $messageMapper,
								StanzaMapper $stanzaMapper,
								$host) {
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->pollingId = time();
		$this->session = $session;
		$this->messageMapper = $messageMapper;
		$this->stanzaMapper = $stanzaMapper;
		$this->host = $host;
		$this->response =  new XMPPResponse();
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$input = file_get_contents('php://input');
		if (!empty($input)){
			// replace invalid XML by valid XML one
			$input = str_replace("<vCard xmlns='vcard-temp'/>", "<vCard xmlns='jabber:vcard-temp'/>", $input);
			$reader = new Reader();
			$reader->xml($input);
			$reader->elementMap = [
				'{jabber:client}message' => 'Sabre\Xml\Element\KeyValue',
			];

			try {
				$stanzas = $reader->parse();
			} catch (LibXMLException $e){
				echo $e;
			}
			$stanzas = $stanzas['value'];
			$longpoll = true; // set to false when the response should directly be returned and no polling should be done
			foreach($stanzas as $stanza) {
				$stanzaType = $this->getStanzaType($stanza);
				if ($stanzaType === self::MESSAGE) {
					$messageHandler = new Message($stanza, $this->userId, $this->host, $this->messageMapper);
					$messageHandler->handle();
				} else if ($stanzaType === self::IQ){
					$iqHandler = new IQ($stanza, $this->userId, $this->host);
					$result = $iqHandler->handle();
					if (!is_null($result)){
						$longpoll = false;
						$this->response->write($result);
					}
				}
			}
		}

		// Start long polling
		$recordFound = false;
		$cicles = 0;
		do {
			try {
				$cicles++;
				$stanzas = $this->stanzaMapper->findByTo($this->userId . '@' . $this->host);
				foreach ($stanzas as $stanz) {
					$this->response->write($stanz);
				}
				$recordFound = true;
			} Catch (DoesNotExistException $e) {
				sleep(1);
				$recordFound = false;
			}
		} while ($recordFound === false && $cicles < 10 && $longpoll);
		return $this->response;
	}

	private function returnEmpty(){
		$xmlWriter = new Writer();
		$xmlWriter->openMemory();
		$xmlWriter->write([
			[
				'name' => 'body',
				'attributes' => [
					'xmlns' => 'http://jabber.org/protocol/httpbind',
				],
				'value' => '',
			]
		]);

		return new XMPPResponse($xmlWriter->outputMemory());
	}

	private function getStanzaType($stanza){
		switch($stanza['name']){
			case '{jabber:client}message':
				return self::MESSAGE;
				break;
			case '{jabber:client}iq':
				return self::IQ;
				break;
			case '{jabber:client}presence':
				return self::PRESENCE;
				break;
			default:
				return self::BODY;
				break;
		}
	}


}