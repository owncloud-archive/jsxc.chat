<?php

/**
 * ownCloud - JavaScript XMPP Chat
 *
 * Copyright (c) 2014-2015 Klaus Herberth <klaus@jsxc.org> <br>
 * Released under the MIT license
 *
 * @author Klaus Herberth <klaus@jsxc.org>
 */
OCP\App::registerAdmin ( 'ojsxc', 'settings' );

$jsxc_root = (defined('JSXC_ENV') && JSXC_ENV === 'dev')? 'jsxc/dev/' : 'jsxc/';

OCP\Util::addScript ( 'ojsxc', $jsxc_root.'lib/jquery.slimscroll' );
OCP\Util::addScript ( 'ojsxc', $jsxc_root.'lib/jquery.fullscreen' );
OCP\Util::addScript ( 'ojsxc', $jsxc_root.'lib/jsxc.dep' );
OCP\Util::addScript ( 'ojsxc', $jsxc_root.'jsxc' );

OCP\Util::addScript ( 'ojsxc', 'ojsxc' );

// ############# CSS #############
OCP\Util::addStyle ( 'ojsxc', 'jquery.mCustomScrollbar' );
OCP\Util::addStyle ( 'ojsxc', 'jquery.colorbox' );
OCP\Util::addStyle ( 'ojsxc', 'jsxc.oc' );

$version = OCP\Util::getVersion();

if($version[0] <= 6)
	OCP\Util::addStyle ( 'ojsxc', 'jsxc.oc.lte6' );

?>
