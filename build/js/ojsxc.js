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

/* global jsxc, oc_appswebroots, OC, $, oc_requesttoken, dijit */

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

   // update webodf
   if (typeof dijit !== 'undefined') {
      $('#mainContainer, #odf-toolbar').animate({
         right: (roster_width) + 'px'
      }, {
         progress: function() {
            dijit.byId("mainContainer").resize();
         }
      });
   }
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

   // update webodf
   if (typeof dijit !== 'undefined') {
      $('#mainContainer, #odf-toolbar').css('right', roster_width + roster_right);
      dijit.byId("mainContainer").resize();
   }
}

// initialization
$(function() {
   "use strict";

   if (location.pathname.substring(location.pathname.lastIndexOf("/") + 1) === 'public.php') {
      return;
   }

   $(document).on('ready.roster.jsxc', onRosterReady);
   $(document).on('toggle.roster.jsxc', onRosterToggle);

   $(document).on('connected.jsxc', function() {
      // reset default avatar cache
      jsxc.storage.removeUserItem('defaultAvatars');
   });

   $(document).on('status.contacts.count status.contact.updated', function() {
      if (jsxc.restoreCompleted) {
         setTimeout(function() {
            jsxc.gui.detectEmail($('table#contactlist'));
         }, 500);
      } else {
         $(document).on('restoreCompleted.jsxc', function() {
            jsxc.gui.detectEmail($('table#contactlist'));
         });
      }
   });

   jsxc.log = "";
   jsxc.tmp = null;
   jsxc.init({
      app_name: 'Owncloud',
      loginForm: {
         form: '#body-login form',
         jid: '#user',
         pass: '#password'
      },
      logoutElement: $('#logout'),
      checkFlash: false,
      rosterAppend: 'body',
      root: oc_appswebroots.ojsxc + '/js/jsxc',
      // @TODO: don't include get turn credentials routine into jsxc
      turnCredentialsPath: OC.filePath('ojsxc', 'ajax', 'getturncredentials.php'),
      displayRosterMinimized: function() {
         return OC.currentUser != null;
      },
      otr: {
         debug: true,
         SEND_WHITESPACE_TAG: true,
         WHITESPACE_START_AKE: true
      },
      defaultAvatar: function(jid) {
         var cache = jsxc.storage.getUserItem('defaultAvatars') || {};
         var user = jid.replace(/@.+/, '');
         var ie8fix = true;

         $(this).each(function() {

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
               var url;

               if (OC.generateUrl) {
                  // oc >= 7
                  url = OC.generateUrl('/avatar/' + user + '/' + size + '?requesttoken={requesttoken}', {
                     user: user,
                     size: size,
                     requesttoken: oc_requesttoken
                  });
               } else {
                  // oc < 7
                  url = OC.Router.generate('core_avatar_get', {
                     user: user,
                     size: size
                  }) + '?requesttoken=' + oc_requesttoken;
               }

               $.get(url, function(result) {

                  var val = (typeof result === 'object') ? result : url;
                  handleResponse(val);

                  jsxc.storage.updateItem('defaultAvatars', key, val, true);
               });

            } else {
               handleResponse(cache[key]);
            }
         });
      },
      loadSettings: function(username, password) {
         var data = null;

         $.ajax({
            async: false,
            type: 'POST',
            url: OC.filePath('ojsxc', 'ajax', 'getsettings.php'),
            data: {
               username: username,
               password: password
            },
            success: function(d) {
               data = d.data;
            },
            error: function() {
               jsxc.error('XHR error on getsettings.php');
            }
         });

         return data;
      },
      saveSettinsPermanent: function(data) {
         var ret = 1;

         $.ajax({
            async: false,
            type: 'POST',
            url: OC.filePath('ojsxc', 'ajax', 'setUserSettings.php'),
            data: data,
            success: function(data) {
               if (data.trim() === 'true') {
                  ret = 0;
               }
            }
         });

         return ret;
      }
   });

   // Add submit link without chat functionality
   if (jsxc.el_exists(jsxc.options.loginForm.form) && jsxc.el_exists(jsxc.options.loginForm.jid) && jsxc.el_exists(jsxc.options.loginForm.pass)) {

      var link = $('<a/>').text(jsxc.translate('%%Log_in_without_chat%%')).attr('href', '#').click(function() {
         jsxc.submitLoginForm();
      });

      var alt = $('<p id="jsxc_alt"/>').append(link);
      $('#body-login form fieldset').append(alt);
   }
});