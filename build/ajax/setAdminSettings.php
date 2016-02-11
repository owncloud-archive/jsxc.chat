<?php

OCP\User::checkAdminUser();
OCP\JSON::callCheck();

$config = \OC::$server->getConfig();

$config->setAppValue('ojsxc', 'serverType', $_POST ['serverType']);
$config->setAppValue('ojsxc', 'boshUrl', $_POST ['boshUrl']);
$config->setAppValue('ojsxc', 'xmppDomain', $_POST ['xmppDomain']);
$config->setAppValue('ojsxc', 'xmppResource', $_POST ['xmppResource']);
$config->setAppValue('ojsxc', 'xmppOverwrite', (isset($_POST ['xmppOverwrite'])) ? $_POST ['xmppOverwrite'] : 'false');
$config->setAppValue('ojsxc', 'xmppStartMinimized', (isset($_POST ['xmppStartMinimized'])) ? $_POST ['xmppStartMinimized'] : 'false');

$config->setAppValue('ojsxc', 'iceUrl', $_POST ['iceUrl']);
$config->setAppValue('ojsxc', 'iceUsername', $_POST ['iceUsername']);
$config->setAppValue('ojsxc', 'iceCredential', $_POST ['iceCredential']);
$config->setAppValue('ojsxc', 'iceSecret', $_POST ['iceSecret']);
$config->setAppValue('ojsxc', 'iceTtl', $_POST ['iceTtl']);

echo 'true';
