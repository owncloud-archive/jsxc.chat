<?php
OCP\User::checkLoggedIn ();
OCP\JSON::callCheck ();

$secret = OCP\Config::getAppValue ( 'ojsxc', 'iceSecret' );
$user = OCP\User::getUser ();

$data = array ();
$data ['ttl'] = OCP\Config::getAppValue ( 'ojsxc', 'iceTtl' ) ?  : 3600 * 24; // one day (according to TURN-REST-API)
$data ['url'] = OCP\Config::getAppValue ( 'ojsxc', 'iceUrl' ); 
$data ['username'] = OCP\Config::getAppValue ( 'ojsxc', 'iceUsername' ) ?  : ($secret ? (time () + $data ['ttl']) . ':' . $user : $user);
$data ['credential'] = OCP\Config::getAppValue ( 'ojsxc', 'iceCredential' ) ?  : ($secret ? base64_encode ( hash_hmac ( 'sha1', $data ['username'], $secret, true ) ) : '');

OCP\JSON::encodedPrint ( $data );
?>
