<?php
namespace OCA\OJSXC\Http;

use OCP\AppFramework\Http\Response;
use Sabre\Xml\Writer;
use OCA\OJSXC\Db\Stanza;


class XMPPResponse extends Response {

	private $writer;

	public function __construct(Stanza $stanza=null) {
		$this->addHeader('Content-Type', 'text/xml');
		$this->writer =  new Writer();
		$this->writer->openMemory();
		$this->writer->startElement('body');
		$this->writer->writeAttribute('xmlns', 'http://jabber.org/protocol/httpbind');
		if (!is_null($stanza)) {
			$this->writer->write($stanza);
		}
	}

	public function write(Stanza $input) {
		$this->writer->write($input);
	}

	public function render() {
		$this->writer->endElement();
		return $this->writer->outputMemory();
	}

}
