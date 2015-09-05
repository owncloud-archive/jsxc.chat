/**
 * This function is a hack. Please FIXME!
 */
//jsxc.options.favicon.enable = false;
//jsxc.options.otr.enable = false;
//Strophe.getNodeFromJid = function () {
//	return OC.currentUser;
//};
//Strophe.getDomainFromJid = function () {
//	return '33.33';
//};
//
//Strophe.getBareJidFromJid = function () {
//	return OC.currentUser + '@33.3';
//};
//function ocInit(){
//	localStorage.clear();
//	jsxc.storage.setItem('debug', true);
//	jsxc.options.set('xmpp', {'url': '/index.php/apps/ojsxc/http-bind'});
//	jsxc.xmpp.login(OC.currentUser + '@33.3', 3434, 3443243); // TODO remove the hardcoded values
//	jsxc.xmpp.connected();
//	jsxc.storage.setItem('debug', true);
//}
//
//$(document).ready(function () {
//	ocInit();
//});
$(document).ready(function () {
	jsxc.options.set('otr', {'enable': false});
	jsxc.options.set('favicon', {'enable': false});
	jsxc.options.set('xmpp', {'url': '/index.php/apps/ojsxc/http-bind'});
	jsxc.storage.setItem('debug', true);
	jsxc.storage.setItem('sid', '7862');
	jsxc.storage.setItem('rid', '897878733');
});