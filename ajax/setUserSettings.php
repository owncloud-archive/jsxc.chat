<?php
OCP\User::checkLoggedIn ();
OCP\JSON::callCheck ();

$user = OCP\User::getUser ();
$options = OCP\Config::getUserValue($user, 'ojsxc', 'options');
$options = json_decode($options, true);

foreach($_POST as $key => $val) {
	$options[$key] = $val;
}

echo OCP\Config::setUserValue($user, 'ojsxc', 'options', json_encode($options));