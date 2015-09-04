<?php

namespace OCA\OJSXC\Controller;


use OCA\OJSXC\Http\XMLResponse;
use OCA\OJSXC\StanzaHandlers\Message;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;

use OCP\ISession;
use Sabre\Xml\Writer;

class HttpBindController extends Controller {

	private $userId;

	const MESSAGE=0;
	const IQ=1;
	const PRESENCE=2;
	const BODY=2;

	private $pollingId;

	/**
	 * @var $sesion \OCP\ISession
	 */
	private $session;

	public function __construct($appName,
	                            IRequest $request,
								$userId,
								ISession $session) {
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->pollingId = time();
		$this->session = $session;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @UseSession
	 */
	public function index() {
		$stanza = file_get_contents('php://input');
		$host = '33.33';
		if (!empty($stanza)){
			$stanza = new \SimpleXMLElement($stanza);
			$stanzaType = $this->getStanzaType($stanza);
			if ($stanzaType === self::MESSAGE){
				$messageStanza = new Message($stanza, $this->userId, $host);
				$messageStanza->handle();
			}
		}

		$this->setLock();

		// Start long polling
		$recordFound = false;
		$cicles = 0;
		do {
			try {
				echo "cycle " . $cicles;
				$cicles++;
				$q = \OCP\DB::prepare('SELECT stanza, id FROM *PREFIX*ojsx_stanzas WHERE `to`=?');
				$q->execute(array($this->userId . '@' . $host));
				$results = $q->fetchAll();
				if (count($results) === 0 || $results === false) {
					throw new DoesNotExistException();
				}
				$xmlWriter = new Writer();
				$xmlWriter->openMemory();
				$xmlWriter->startElement('body');

				foreach ($results as $result) {
					$xmlWriter->writeRaw($result['stanza']);
					$q = \OCP\DB::prepare('DELETE FROM *PREFIX*ojsx_stanzas WHERE `id`=?');
					$q->execute(array($result['id']));
				}

				$xmlWriter->endElement();
				$recordFound = true;

				return new XMLResponse($xmlWriter->outputMemory());
			} Catch (DoesNotExistException $e) {
				sleep(2);
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
		if(isset($stanza->message)){
			return self::MESSAGE;
		} else if (isset($stanza->iq)){
			return self::IQ;
		} else if (isset($stanza->presence)){
			return self::PRESENCE;
		} else {
			return self::BODY;
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