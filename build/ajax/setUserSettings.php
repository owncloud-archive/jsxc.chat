<?php
OCP\User::checkLoggedIn ();
OCP\JSON::callCheck ();

$config = \OC::$server->getConfig();
$uid = \OC::$server->getUserSession()->getUser()->getUID();

$options = $config->getUserValue($uid, 'ojsxc', 'options');
$options = json_decode($options, true);

foreach($_POST as $key => $val) {
	$options[$key] = $val;
}

$config->setUserValue($uid, 'ojsxc', 'options', json_encode($options));

echo 'true';
