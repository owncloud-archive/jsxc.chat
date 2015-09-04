/**
 * This function is a hack. Please FIXME!
 */
jsxc.options.favicon.enable = false;
jsxc.options.otr.enable = false;
function ocInit(){
	localStorage.clear();
	jsxc.storage.setItem('debug', true);
	jsxc.options.set('xmpp', {'url': '/index.php/apps/ojsxc/http-bind'});
	jsxc.xmpp.login(OC.currentUser + '@33.3', 3434, 3443243); // TODO remove the hardcoded values
	jsxc.xmpp.connected();
	jsxc.storage.setItem('debug', true);
}

$(document).ready(function () {
	ocInit();
});