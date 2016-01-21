/* global OC */
$(document).ready(function () {
	jsxc.options.xmpp.url = OC.generateUrl('apps/ojsxc/http-bind');
	jsxc.start(OC.currentUser + '@' + OC.getHost(), Math.floor(Math.random() * (1000 - 1)) + 1, Math.floor(Math.random() * (1000 - 1)) + 1);
	jsxc.xmpp.conn.resume();
	jsxc.onMaster();
});