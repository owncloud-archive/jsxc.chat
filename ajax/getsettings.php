<?php
function validateBoolean($val) {
	return $val === true || $val === 'true';
}

OCP\JSON::callCheck ();

$username = $_POST ['username'];
$password = $_POST ['password'];

$ocUser = new OCP\User ();

$auth = ($password !== null) ? $ocUser->checkPassword ( $username, $password ) : OCP\User::isLoggedIn ();

if (!$auth) {
	OCP\JSON::encodedPrint ( array (
			'result' => 'noauth' 
	) );
	exit ();
}

$data = array ();
$data ['xmpp'] = array ();
$data ['serverType'] = OCP\Config::getAppValue ( 'ojsxc', 'serverType' );
$data ['loginForm'] ['startMinimized'] = validateBoolean(OCP\Config::getAppValue ( 'ojsxc', 'xmppStartMinimized' ));

if ($data ['serverType'] === 'internal') {
	OCP\JSON::encodedPrint ( array (
			'result' => 'success',
			'data' => $data
	) );

	exit;
}

$data ['xmpp'] ['url'] = OCP\Config::getAppValue ( 'ojsxc', 'boshUrl' );
$data ['xmpp'] ['domain'] = OCP\Config::getAppValue ( 'ojsxc', 'xmppDomain' );
$data ['xmpp'] ['resource'] = OCP\Config::getAppValue ( 'ojsxc', 'xmppResource' );
$data ['xmpp'] ['overwrite'] = validateBoolean(OCP\Config::getAppValue ( 'ojsxc', 'xmppOverwrite' ));
$data ['xmpp'] ['onlogin'] = true;

$options = OCP\Config::getUserValue ( $username, 'ojsxc', 'options' );
	
if ($options !== null) {
	$options = json_decode ( $options, true );
		
	foreach ( $options as $prop => $value ) {
		if ($prop !== 'xmpp' || $data ['xmpp'] ['overwrite']) {
			foreach ( $value as $key => $v ) {
				if ($v !== "")
					$data [$prop] [$key] = ($v === 'false' || $v === 'true') ? validateBoolean($v) : $v;
			}
		}
	}
}

OCP\JSON::encodedPrint ( array (
		'result' => 'success',
		'data' => $data 
) );
?>
