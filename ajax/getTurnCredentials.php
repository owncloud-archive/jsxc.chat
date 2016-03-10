<?php

OCP\User::checkLoggedIn();
OCP\JSON::callCheck();

header('Content-Type: application/json; charset=utf-8');

$config = \OC::$server->getConfig();
$secret = $config->getAppValue('ojsxc', 'iceSecret');
$user = \OC::$server->getUserSession()->getUser()->getUID();

$ttl = $config->getAppValue('ojsxc', 'iceTtl',  3600 * 24); // one day (according to TURN-REST-API)
$url = $config->getAppValue('ojsxc', 'iceUrl');
$url = $url ?  "turn:$url" : $url;

$usernameTRA = $secret ? (time() + $ttl).':'.$user : $user;
$username = $config->getAppValue('ojsxc', 'iceUsername', '');
$username = (!empty($username)) ? $username : $usernameTRA;

$credentialTRA = ($secret) ? base64_encode(hash_hmac('sha1', $username, $secret, true)) : '';
$credential = $config->getAppValue('ojsxc', 'iceCredential', '');
$credential = (!empty($credential)) ? $credential : $credentialTRA;

$data = array(
   'ttl' => $ttl,
   'iceServers' => array(
      array(
         'urls' => array($url),
         'credential' => $credential,
         'username' => $username,
      ),
   ),
);

echo json_encode($data);
