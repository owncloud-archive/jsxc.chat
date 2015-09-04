<?php
namespace OCA\OJSXC\Http;

use OCP\AppFramework\Http\Response;

class XMLResponse extends Response {

	private $xml;

	public function __construct($xml) {
		$this->addHeader('Content-Type', 'text/xml');
		$this->xml = $xml;
	}

	public function render() {
		return $this->xml;
	}

}
