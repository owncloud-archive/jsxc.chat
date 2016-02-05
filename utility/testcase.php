<?php

namespace OCA\OJSXC\Utility;

use OCA\OJSXC\AppInfo\Application;
use Sabre\Xml\Service;
use Test\TestCase as CoreTestCase;

class TestCase extends CoreTestCase {

	public static function assertSabreXmlEqualsXml($expected, $actual) {
		$service = new Service();

		$parsedExpected = $service->parse("<?xml version=\"1.0\" encoding=\"utf-8\"?><unit-wrapper>" . $expected . "</unit-wrapper>");
		$parsedActual = $service->parse("<?xml version=\"1.0\" encoding=\"utf-8\"?><unit-wrapper>" . $actual . "</unit-wrapper>");

		self::assertEquals($parsedExpected, $parsedActual, 'Failed asserting that two XML strings are equal.');

	}

	public function overwriteApplicationService(Application $app, $name, $newService) {
		$app->getContainer()->registerService($name, function () use ($newService) {
			return $newService;
		});
	}

}