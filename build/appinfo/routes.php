<?php 
/**
 * ownCloud - JavaScript XMPP Chat
 *
 * Copyright (c) 2014 Klaus Herberth <klaus@jsxc.org> <br>
 * Released under the MIT license
 *
 * @author Klaus Herberth
 */
$this->create('ojsxc_ajax_getsettings', 'ajax/getsettings.php')
	->actionInclude('ojsxc/ajax/getsettings.php');
	
$this->create('ojsxc_ajax_getturncredentials', 'ajax/getturncredentials.php')
	->actionInclude('ojsxc/ajax/getturncredentials.php');
	
$this->create('ojsxc_ajax_setsettings', 'ajax/setsettings.php')
	->actionInclude('ojsxc/ajax/setsettings.php');
	
$this->create('ojsxc_ajax_setUserSettings', 'ajax/setUserSettings.php')
	->actionInclude('ojsxc/ajax/setUserSettings.php');
	
?>