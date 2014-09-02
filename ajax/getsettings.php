<?php
OCP\JSON::callCheck ();

$username = $_POST ['username'];
$password = $_POST ['password'];

$ocUser = new OCP\User ();

$auth = ($password != null) ? $ocUser->checkPassword ( $username, $password ) : OCP\User::isLoggedIn ();

if (!$auth) {
	OCP\JSON::encodedPrint ( array (
			'result' => 'noauth' 
	) );
	exit ();
}

$data = array ();
$data ['xmpp'] = array ();
$data ['xmpp'] ['url'] = OCP\Config::getAppValue ( 'ojsxc', 'boshUrl' );
$data ['xmpp'] ['domain'] = OCP\Config::getAppValue ( 'ojsxc', 'xmppDomain' );
$data ['xmpp'] ['resource'] = OCP\Config::getAppValue ( 'ojsxc', 'xmppResource' );
$data ['xmpp'] ['overwrite'] = OCP\Config::getAppValue ( 'ojsxc', 'xmppOverwrite' );
$data ['xmpp'] ['onlogin'] = 'true';

if ($data ['xmpp'] ['overwrite'] == null) {
	$data ['xmpp'] ['overwrite'] = false;
}

if ($data ['xmpp'] ['overwrite']) {
	$options = OCP\Config::getUserValue ( $username, 'ojsxc', 'options' );
	
	if ($options !== null) {
		$options = json_decode ( $options, true );
		
		foreach ( $options as $prop => $value ) {
			foreach ( $value as $key => $v ) {
				if ($v != "")
					$data [$prop] [$key] = $v;
			}
		}
	}
}

OCP\JSON::encodedPrint ( array (
		'result' => 'success',
		'data' => $data 
) );
?>