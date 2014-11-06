/*!
 * ojsxc v1.0.0 - 2014-11-06
 * 
 * Copyright (c) 2014 Klaus Herberth <klaus@jsxc.org> <br>
 * Released under the MIT license
 * 
 * Please see http://www.jsxc.org/
 * 
 * @author Klaus Herberth <klaus@jsxc.org>
 * @version 1.0.0
 * @license MIT
 */

$(document).ready(function() {
   $('#ojsxc').submit(function(event) {
      event.preventDefault();

      //clear area
      $('#ojsxc .msg').html(' ');
      
      var post = $(this).serialize();
      var status =  $('<div/>').html('<img src="' + jsxc.options.root +'/img/loading.gif" alt="wait" width="16px" height="16px" />');
      
      var statusBosh = status.clone().html(status.html() + " Testing BOSH Server...");
      $('#ojsxc .msg').append(statusBosh);
      
      var rid = jsxc.storage.getItem('rid') || '123456';
      var xmppDomain = $('#xmppDomain').val();
      var fail = function() {
         statusBosh.addClass('jsxc_fail').text('BOSH server NOT reachable. Please beware of the same-origin-policy (SOP). If your XMPP server doesn\'t reside on the same host as your OwnCloud (same port/protocol), use the Apache ProxyRequest or modify the content-security-policy (CSP) by defining "custom_csp_policy" in OwnCloud\'s config.php.');
      };
      
      $.post($('#boshUrl').val(), "<body rid='"+rid+"' xmlns='http://jabber.org/protocol/httpbind' to='"+xmppDomain+"' xml:lang='en' wait='60' hold='1' content='text/xml; charset=utf-8' ver='1.6' xmpp:version='1.0' xmlns:xmpp='urn:xmpp:xbosh'/>").done(function(stanza) {
         var body = $(stanza).find('body[xmlns="http://jabber.org/protocol/httpbind"]');
         var condition = (body)? body.attr('condition'): null;
         
         if(body.length > 0 && condition != 'internal-server-error'){
            statusBosh.addClass('jsxc_success').text('BOSH Server reachable.');
         } else {
            fail();
            if(condition == 'internal-server-error'){
               statusBosh.html(statusBosh.text() + ' <br /><br /><b>Error: </b>'+body.text());
            }
         }
         
      }).fail(fail);

      var statusSet = status.clone().html(status.html() + " Saving...");
      $('#ojsxc .msg').append(statusSet);

      $.post(OC.filePath('ojsxc', 'ajax', 'setsettings.php'), post, function(data) {
         if (data)
            statusSet.addClass('jsxc_success').text('Settings saved.');
         else
            statusSet.addClass('jsxc_fail').text('Error!');
      });

   });
});