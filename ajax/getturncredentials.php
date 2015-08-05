<?php
OCP\User::checkLoggedIn ();
OCP\JSON::callCheck ();

$secret = OCP\Config::getAppValue ( 'ojsxc', 'iceSecret' );
$user = OCP\User::getUser ();

$ttl = OCP\Config::getAppValue ( 'ojsxc', 'iceTtl' ) ?  : 3600 * 24; // one day (according to TURN-REST-API)
$url = OCP\Config::getAppValue ( 'ojsxc', 'iceUrl' );
$url = $url ?  "turn:$url" : $url;
$username = OCP\Config::getAppValue ( 'ojsxc', 'iceUsername' ) ?  : ($secret ? (time () + $ttl) . ':' . $user : $user);
$credential = OCP\Config::getAppValue ( 'ojsxc', 'iceCredential' ) ?  : ($secret ? base64_encode ( hash_hmac ( 'sha1', $username, $secret, true ) ) : '');

$data = array (
   'ttl' => $ttl,
   'iceServers' => array(
      array(
         'urls' => array($url),
         'credential' => $credential,
         'username' => $username
      )
   )
);

OCP\JSON::encodedPrint ( $data );
?>
