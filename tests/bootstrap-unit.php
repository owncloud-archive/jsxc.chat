<?php
require_once __DIR__ . '/../../../lib/base.php';
require_once __DIR__ . '/../vendor/autoload.php';
$version = \OC::$server->getSession()->get('OC_Version');
if (method_exists(\OC::$loader, 'addValidRoot')) {
	\OC::$loader->addValidRoot(OC::$SERVERROOT . '/tests'); // needed for the TestCase utility
}