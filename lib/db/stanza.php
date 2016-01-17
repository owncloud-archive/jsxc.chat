<?php

namespace OCA\OJSXC\Db;

use \OCP\AppFramework\Db\Entity;
use Sabre\Xml\Reader;
use Sabre\Xml\Writer;
use Sabre\Xml\XmlDeserializable;
use Sabre\Xml\XmlSerializable;

/**
 * Class Stanza
 * @package OCA\OJSXC\Db
 * @brief this class is used as the entity which is fetched from the stanza table OR extended by a specific stanza
 * for inserting into the stanza table
 */
class Stanza extends Entity implements XmlSerializable{

    public $to;
    public $from;
    public $stanza;

    public function xmlSerialize(Writer $writer) {
        $writer->writeRaw($this->getStanza());
    }
}