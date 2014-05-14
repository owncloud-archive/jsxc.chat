__Bugtracker and issues:__ https://github.com/sualko/jsxc/issues
__Wiki__ https://github.com/sualko/jsxc/wiki

# Owncloud JavaScript Xmpp Client

__Beware! This is beta software.__

Real-time chat app for OwnCloud. This app requires external XMPP server (openfire, ejabberd etc.).

More information about features, supported protocols/browsers and planned features at [www.jsxc.org](http://www.jsxc.org/).


## User notes

Please download the latest version from https://github.com/sualko/ojsxc/releases and copy the "build" folder to your app directory and rename it to "ojsxc". Now go into your app menu and enable the "JavaScript Xmpp Chat".

### Configuration

You can configure oJSXC in the admin panel of owncloud.

<dl>
	<dt>BOSH url</dt>
	<dd>The url to your bosh server. Please beware of the SOP. If your XMPP server doesn't reside on the same host as your OwnCloud, use the Apache ProxyRequest or modify the CSP by defining 'custom_csp_policy' in OwnCloud's config.php.
	<b>Example:</b>
<pre><code>/* Custom CSP policy, changing this will overwrite the standard policy */	
"custom_csp_policy" => "default-src 'self' https://your.xmpp.server.com:PORT; script-src 'self' 'unsafe-eval'; 
	style-src 'self' 'unsafe-inline'; frame-src *; img-src *; font-src 'self' data:; media-src *",</code></pre>
	</dd>

	<dt>XMPP domain</dt>
	<dd>The domain of your Jabber ID.</dd>

	<dt>XMPP resource</dt>
	<dd>The resource of your JID. If you leaf this field blank a random resource is generated.</dd>

	<dt>TURN url</dt>
	<dd>The url to your TURN server. You get a free account on http://numb.viagenie.ca</dd>

	<dt>TURN username</dt>
	<dd>If no username is set, the TURN REST API is used.</dd>

	<dt>TURN credential</dt>
	<dd>If no credential is set, the TURN REST API is used.</dd>

	<dt>TURN secret</dt>
	<dd>Secret for TURN REST API.</dd>

	<dt>TURN ttl</dt>
	<dd>Lifetime of credentials.</dd>
</dl>

### Screenshots

![Screenshot 1](https://raw.github.com/sualko/ojsxc/master/documentation/screenshot_1.png)
![Screenshot 2](https://raw.github.com/sualko/ojsxc/master/documentation/screenshot_2.png)
![Screenshot 3](https://raw.github.com/sualko/ojsxc/master/documentation/screenshot_3.png)

## Developer notes

Please execute the following commands to get a copy of the code:

```
git clone https://github.com/sualko/ojsxc/
git submodule update --init --recursive
```

### Libaries
- jQuery (http://jquery.com/)
- Strophe.js (http://strophe.im/strophejs/)
- Strophe.js Plugins (https://github.com/strophe/strophejs-plugins)
- OTR (https://github.com/arlolra/otr)
- strophe.jingle (https://github.com/ESTOS/strophe.jingle)

### Events
coming soon...
