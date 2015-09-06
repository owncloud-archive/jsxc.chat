<?php

namespace OCA\OJSXC\StanzaHandlers;

use OCA\OJSXC\Db\IQRoster;
use OCA\OJSXC\Db\MessageMapper;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;

class IQ extends StanzaHandler {

	private $type;

	private $id;

	private $query;

	public function __construct(Array $stanza, $userId, $host, MessageMapper $messageMapper) {
		parent::__construct($stanza, $userId, $host);
		$this->from = $this->userId . '@' . $this->host;

	}

	public function handle() {
		foreach($this->stanza['value'] as $value){
			if ($value['name'] === '{jabber:iq:roster}query'){
				$id = $this->stanza['attributes']['id'];
				$iqRoster = new \OCA\OJSXC\Db\IQRoster();
				$iqRoster->setType('result');
				$iqRoster->setTo($this->from);
				$iqRoster->setQid($id);
				foreach(\OCP\User::getUsers() as $user){
					if($user !== $this->userId) {
						$iqRoster->addItem($user . '@' . $this->host, \OCP\User::getDisplayName($user));
					}
				}
				return $iqRoster;
			}
		}

	}

}