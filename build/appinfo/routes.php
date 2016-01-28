<?php 
/**
 * ownCloud - JavaScript XMPP Chat
 *
 * Copyright (c) 2014 Klaus Herberth <klaus@jsxc.org> <br>
 * Released under the MIT license
 *
 * @author Klaus Herberth
 */

use \OCA\OJSXC\AppInfo\Application;

$this->create('ojsxc_ajax_getsettings', 'ajax/getsettings.php')
	->actionInclude('ojsxc/ajax/getsettings.php');
	
$this->create('ojsxc_ajax_getturncredentials', 'ajax/getturncredentials.php')
	->actionInclude('ojsxc/ajax/getturncredentials.php');
	
$this->create('ojsxc_ajax_setsettings', 'ajax/setsettings.php')
	->actionInclude('ojsxc/ajax/setsettings.php');
	
$this->create('ojsxc_ajax_setUserSettings', 'ajax/setUserSettings.php')
	->actionInclude('ojsxc/ajax/setUserSettings.php');

$this->create('ojsxc_ajax_getUsers', 'ajax/getUsers.php')
	->actionInclude('ojsxc/ajax/getUsers.php');
	
$application = new Application();
$application->registerRoutes($this, array(
	'routes' => array(
		array('name' => 'http_bind#index', 'url' => '/http-bind', 'verb' => array('GET', 'POST')),
	)
));
?>
