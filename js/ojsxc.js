/* global jsxc, oc_appswebroots, OC, $, oc_requesttoken */

/**
 * Make room for the roster inside the owncloud template.
 * 
 * @param {type} event
 * @param {type} state State in which the roster is
 * @param {type} duration Time the roster needs to move
 * @returns {undefined}
 */
function onRosterToggle(event, state, duration) {
   "use strict";
   var wrapper = $('#content-wrapper');
   var control = $('#controls');

   var roster_width = (state === 'shown') ? $('#jsxc_roster').outerWidth() : 0;
   var navigation_width = $('#navigation').width();

   wrapper.animate({
      paddingRight: (roster_width) + 'px'
   }, duration);
   control.animate({
      paddingRight: (roster_width + navigation_width) + 'px'
   }, duration);
}

/**
 * Init owncloud template for roster.
 * 
 * @returns {undefined}
 */
function onRosterReady() {
   "use strict";
   var roster_width = $('#jsxc_roster').outerWidth();
   var navigation_width = $('#navigation').width();
   var roster_right = parseFloat($('#jsxc_roster').css('right'));

   $('#content-wrapper').css('paddingRight', roster_width + roster_right);
   $('#controls').css('paddingRight', roster_width + navigation_width + roster_right);
}

// initialization
$(function() {
   "use strict";

   $(document).on('ready.roster.jsxc', onRosterReady);
   $(document).on('toggle.roster.jsxc', onRosterToggle);

   jsxc.log = "";
   jsxc.tmp = null;
   jsxc.init({
      loginForm: {
         form: '#body-login form',
         jid: '#user',
         pass: '#password',
         preJid: function(jid) {
            var data = null;

            $.ajax(OC.filePath('ojsxc', 'ajax', 'getsettings.php'), {
               async: false,
               success: function(d) {
                  data = d;
               }
            });

            var resource = (data.xmppResource) ? '/' + data.xmppResource : '';
            var domain = data.xmppDomain;

            jsxc.storage.setItem('boshUrl', data.boshUrl);

            if (jid.match(/@(.*)$/)) {
               return (jid.match(/\/(.*)$/)) ? jid : jid + resource;
            }

            return jid + '@' + domain + resource;
         }
      },
      logoutElement: $('#logout'),
      checkFlash: false,
      rosterAppend: 'body',
      root: oc_appswebroots.ojsxc,
      // @TODO: don't include get turn credentials routine into jsxc
      turnCredentialsPath: OC.filePath('ojsxc', 'ajax', 'getturncredentials.php'),
      displayRosterMinimized: function() {
         return OC.currentUser != null;
      },
      otr: {
         SEND_WHITESPACE_TAG: true,
         WHITESPACE_START_AKE: true
      },
      defaultAvatar: function(jid) {
         var cache = jsxc.storage.getUserItem('defaultAvatars') || {};
         
         $(this).each(function() {
            var user = jid.replace(/@.+/, '');
            var ie8fix = true;
            var $div = $(this).find('.jsxc_avatar');
            var size = $div.width();
            var key = user + '@' + size;

            var handleResponse = function(result) {
               if (typeof (result) === 'object') {
                  if (result.data && result.data.displayname) {
                     $div.imageplaceholder(user, result.data.displayname);
                  } else {
                     $div.imageplaceholder(user);
                  }
               } else {
                  $div.show();
                  if (ie8fix === true) {
                     $div.html('<img src="' + result + '#' + Math.floor(Math.random() * 1000) + '">');
                  } else {
                     $div.html('<img src="' + result + '">');
                  }
               }
            };
            
            if (typeof cache[key] === 'undefined' || cache[key] === null) {
               OC.Router.registerLoadedCallback(function() {
                  var url = OC.Router.generate('core_avatar_get', {
                     user: user,
                     size: size
                  }) + '?requesttoken=' + oc_requesttoken;
                  
                  $.get(url, function(result){
                     handleResponse(result);
                     var val = (typeof (result) === 'object')? result: url;
                     
                     jsxc.storage.updateUserItem('defaultAvatars', key, val);
                  });
               });
            } else {
               handleResponse(cache[key]);
            }
         });
      }
   });

   // Add submit link without chat functionality
   if (jsxc.el_exists($('#body-login form'))) {

      var link = $('<a/>').text('Log in without chat').attr('href', '#').click(function() {
         jsxc.submitLoginForm();
      });

      var alt = $('<p id="jsxc_alt"/>').append(link);
      $('#body-login form fieldset').append(alt);
   }
});