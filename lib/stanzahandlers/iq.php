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

	public function handle($stanza) {
		$this->to = $this->getAttribute($stanza, 'to');

		foreach($stanza['value'] as $value){
			if ($value['name'] === '{jabber:iq:roster}query'){
				$id = $stanza['attributes']['id'];
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