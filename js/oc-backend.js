/**
 * This function is a hack. Please FIXME!
 */
$(document).ready(function () {
	localStorage.clear()
	jsxc.options.set('favicon', {enable: false});
	jsxc.options.set('otr', {enable: false});
	jsxc.options.set('favicon', {enable: false});
	jsxc.options.set('xmpp', {'url': '/index.php/apps/ojsxc/http-bind'});
	jsxc.storage.setItem('debug', true);
	jsxc.storage.setItem('sid', '7862');
	jsxc.storage.setItem('rid', '897878733');
	jsxc.storage.setItem('lastActivity', (new Date()).getTime());
	jsxc.storage.setItem('jid', OC.currentUser + '@33.33');
});