<?php
namespace OCA\OJSXC\Http;

use OCP\AppFramework\Http\Response;
use Sabre\Xml\Writer;


class XMPPResponse extends Response {

	private $writer;

	public function __construct() {
		$this->addHeader('Content-Type', 'text/xml');
		$this->writer =  new Writer();
		$this->writer->openMemory();
		$this->writer->startElement('body');
		$this->writer->writeAttribute('xmlns', 'http://jabber.org/protocol/httpbind');
	}

	public function write($input) {
		$this->writer->write($input);
	}

	public function render() {
		$this->writer->endElement();
		return $this->writer->outputMemory();
	}

}
