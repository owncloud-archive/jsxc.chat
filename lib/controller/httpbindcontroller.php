<?php

namespace OCA\OJSXC\Controller;

use OCA\OJSXC\Db\StanzaMapper;
use OCA\OJSXC\Db\MessageMapper;
use OCA\OJSXC\Http\XMLResponse;
use OCA\OJSXC\StanzaHandlers\Message;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
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

	public function __construct($appName,
	                            IRequest $request,
								$userId,
								ISession $session,
								MessageMapper $messageMapper,
								StanzaMapper $stanzaMapper) {
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->pollingId = time();
		$this->session = $session;
		$this->messageMapper = $messageMapper;
		$this->stanzaMapper = $stanzaMapper;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$stanza = file_get_contents('php://input');
		$host = '33.33';
		if (!empty($stanza)){
			$reader = new Reader();
			$reader->xml($stanza);
			$reader->elementMap = [
				'{jabber:client}message' => 'Sabre\Xml\Element\KeyValue',
			];
			try {
				$stanza = $reader->parse();
			} catch (LibXMLException $e){

			}
			$stanzaType = $this->getStanzaType($stanza);
			if ($stanzaType === self::MESSAGE){
				$messageStanza = new Message($stanza, $this->userId, $host, $this->messageMapper);
				$messageStanza->handle();
			}
		}

		$this->setLock();

		// Start long polling
		$recordFound = false;
		$cicles = 0;
		do {
			try {
				$cicles++;
				$stanzas = $this->stanzaMapper->findByTo($this->userId . '@' . $host);

				$xmlWriter = new Writer();
				$xmlWriter->openMemory();
				$xmlWriter->startElement('body');

				foreach ($stanzas as $stanz) {
					$xmlWriter->write($stanz);
				}

				$xmlWriter->endElement();
				$recordFound = true;

				return new XMLResponse($xmlWriter->outputMemory());
			} Catch (DoesNotExistException $e) {
				sleep(1);
				$recordFound = false;
			}
		} while ($recordFound === false && $cicles < 10 && $this->isLocked());
		if (!$recordFound) {
			return $this->returnEmpty();
		}
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

		return new XMLResponse($xmlWriter->outputMemory());
	}

	private function getStanzaType($stanza){
		switch($stanza['value'][0]['name']){
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


	private function isLocked(){
		$sql = "SELECT `configvalue` FROM `*PREFIX*preferences` WHERE `userid` = ? AND `appid`='ojsxc' AND `configkey`='longpolling'";
		$q = \OCP\DB::prepare($sql);
		$r =$q->execute(array($this->userId));
		$r = $r->fetchRow();
		return (int) $r['configvalue'] === (int) $this->pollingId;
	}

	private function setLock(){
		\OCP\Config::setUserValue($this->userId, 'ojsxc', 'longpolling', $this->pollingId);
	}
}