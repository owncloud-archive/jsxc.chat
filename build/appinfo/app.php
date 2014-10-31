<?php

/**
 * ownCloud - JavaScript XMPP Chat
 *
 * Copyright (c) 2014 Klaus Herberth <klaus@jsxc.org> <br>
 * Released under the MIT license
 * 
 * @author Klaus Herberth
*/
OCP\App::registerAdmin ( 'ojsxc', 'settings' );

if(DEBUG === true) {
	// ############# Javascript #############
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/jquery.colorbox-min' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/jquery.slimscroll' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/jquery.fullscreen' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe' );
	
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.muc' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.disco' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.caps' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.vcard' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.jingle/strophe.jingle' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.jingle/strophe.jingle.session' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.jingle/strophe.jingle.sdp' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/strophe.jingle/strophe.jingle.adapter' );
	
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/otr/build/dep/salsa20' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/otr/build/dep/bigint' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/otr/build/dep/crypto' );
	OCP\Util::addScript ( 'ojsxc', 'eof' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/otr/build/dep/eventemitter' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/otr/build/otr' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/jsxc.lib' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/jsxc.lib.webrtc' );
	// OCP\Util::addScript ( 'ojsxc', 'lib/jsxc.lib.muc' );
} else {
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/jquery.colorbox-min' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/jquery.slimscroll' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/jquery.fullscreen' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/lib/jsxc.dep.min' );
	OCP\Util::addScript ( 'ojsxc', 'jsxc/jsxc.min' );
}

OCP\Util::addScript ( 'ojsxc', 'ojsxc' );

// ############# CSS #############
OCP\Util::addStyle ( 'ojsxc', 'jquery.mCustomScrollbar' );
OCP\Util::addStyle ( 'ojsxc', 'jquery.colorbox' );
OCP\Util::addStyle ( 'ojsxc', '../js/jsxc/jsxc' );
OCP\Util::addStyle ( 'ojsxc', '../js/jsxc/jsxc.webrtc' );
OCP\Util::addStyle ( 'ojsxc', 'jsxc.oc' );
OCP\Util::addStyle ( 'ojsxc', 'muc' );

$version = OCP\Util::getVersion();

if($version[0] <= 6)
	OCP\Util::addStyle ( 'ojsxc', 'jsxc.oc.lte6' );

?>
