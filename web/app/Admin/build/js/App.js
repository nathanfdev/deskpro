(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['angular', 'DP_LANG', 'AdminRouting', 'Admin/Main/Service/AppState', 'Admin/Main/Service/DpApi', 'Admin/Main/Service/Growl', 'Admin/Main/Service/InhelpState', 'Admin/Main/Service/TemplateManager', 'Admin/Main/Translate/DpInterpolation', 'Admin/Main/Directive/Autofocus', 'Admin/Main/Directive/BgImg', 'Admin/Main/Directive/DpErrorClass', 'Admin/Main/Directive/DpHelpPage', 'Admin/Main/Directive/DpHideSpinning', 'Admin/Main/Directive/DpInhelpBody', 'Admin/Main/Directive/DpInhelpBtn', 'Admin/Main/Directive/DpNavSubnav', 'Admin/Main/Directive/DpOpenPhraseEditor', 'Admin/Main/Directive/DpPingFlash', 'Admin/Main/Directive/DpRegisterMessage', 'Admin/Main/Directive/DpServerValidation', 'Admin/Main/Directive/DpShowSpinning', 'Admin/Main/Directive/DpStateMark', 'Admin/Main/Directive/DpSubmitForm', 'Admin/Main/Directive/DpTabBody', 'Admin/Main/Directive/DpTabBtn', 'Admin/Main/Directive/DpToggleSwitch', 'Admin/Main/DataService/EntityManager', 'Admin/Main/DataService/Departments', 'Admin/TicketAccounts/DataService/TicketAccounts', 'Admin/TicketLabels/DataService/TicketLabels', 'Admin/TicketLabels/Filter/encodeURIComponent'], function(angular, DP_LANG, routing, Admin_Main_Service_AppState, Admin_Main_Service_DpApi, Admin_Main_Service_Growl, Admin_Main_Service_InhelpState, Admin_Main_Service_TemplateManager, Admin_Main_Translate_DpInterpolation, Admin_Main_Directive_Autofocus, Admin_Main_Directive_BgImg, Admin_Main_Directive_DpErrorClass, Admin_Main_Directive_DpHelpPage, Admin_Main_Directive_DpHideSpinning, Admin_Main_Directive_DpInhelpBody, Admin_Main_Directive_DpInhelpBtn, Admin_Main_Directive_DpNavSubnav, Admin_Main_Directive_DpOpenPhraseEditor, Admin_Main_Directive_DpPingFlash, Admin_Main_Directive_DpRegisterMessage, Admin_Main_Directive_DpServerValidation, Admin_Main_Directive_DpShowSpinning, Admin_Main_Directive_DpStateMark, Admin_Main_Directive_DpSubmitForm, Admin_Main_Directive_DpTabBody, Admin_Main_Directive_DpTabBtn, Admin_Main_Directive_DpToggleSwitch, Admin_Main_DataService_EntityManager, Admin_Main_DataService_Departments, Admin_TicketAccounts_DataService_TicketAccounts, Admin_TicketLabels_DataService_TicketLabels, Admin_TicketLabels_Filter_encodeURIComponent) {
    var Admin_App, _ref, _ref1;
    Admin_App = angular.module('Admin_App', ['ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable', 'pascalprecht.translate']);
    Admin_App.service('AppState', [
      '$rootScope', '$state', function($rootScope, $state) {
        return new Admin_Main_Service_AppState($rootScope, $state);
      }
    ]);
    Admin_App.service('Api', [
      '$http', function($http) {
        return new Admin_Main_Service_DpApi($http, window.DP_BASE_API_URL, window.DP_API_TOKEN);
      }
    ]);
    Admin_App.factory('$exceptionHandler', [
      '$log', function($log) {
        return function(exception, cause) {
          throw exception;
        };
      }
    ]);
    Admin_App.service('InhelpState', [
      'Api', function(Api) {
        return new Admin_Main_Service_InhelpState(Api);
      }
    ]);
    Admin_App.service('em', [
      function() {
        return new Admin_Main_DataService_EntityManager();
      }
    ]);
    Admin_App.service('DepartmentData', [
      'em', 'Api', '$q', function(em, Api, $q) {
        return new Admin_Main_DataService_Departments(em, Api, $q);
      }
    ]);
    Admin_App.service('TicketAccountsData', [
      'em', 'Api', '$q', function(em, Api, $q) {
        return new Admin_TicketAccounts_DataService_TicketAccounts(em, Api, $q);
      }
    ]);
    Admin_App.service('TicketLabelsData', [
      'Api', '$q', function(Api, $q) {
        return new Admin_TicketLabels_DataService_TicketLabels(Api, $q);
      }
    ]);
    Admin_App.filter('TicketLabelsFilterEncodeURIComponent', [
      function() {
        return new Admin_TicketLabels_Filter_encodeURIComponent();
      }
    ]);
    Admin_App.service('Growl', [
      function() {
        return new Admin_Main_Service_Growl();
      }
    ]);
    Admin_App.directive('autofocus', Admin_Main_Directive_Autofocus);
    Admin_App.directive('bgImg', Admin_Main_Directive_BgImg);
    Admin_App.directive('dpErrorClass', Admin_Main_Directive_DpErrorClass);
    Admin_App.directive('dpHelpPage', Admin_Main_Directive_DpHelpPage);
    Admin_App.directive('dpHideSpinning', Admin_Main_Directive_DpHideSpinning);
    Admin_App.directive('dpInhelpBody', Admin_Main_Directive_DpInhelpBody);
    Admin_App.directive('dpInhelpBtn', Admin_Main_Directive_DpInhelpBtn);
    Admin_App.directive('dpNavSubnav', Admin_Main_Directive_DpNavSubnav);
    Admin_App.directive('dpOpenPhraseEditor', Admin_Main_Directive_DpOpenPhraseEditor);
    Admin_App.directive('dpPingFlash', Admin_Main_Directive_DpPingFlash);
    Admin_App.directive('dpRegisterMessage', Admin_Main_Directive_DpRegisterMessage);
    Admin_App.directive('dpServerValidation', Admin_Main_Directive_DpServerValidation);
    Admin_App.directive('dpShowSpinning', Admin_Main_Directive_DpShowSpinning);
    Admin_App.directive('dpStateMark', Admin_Main_Directive_DpStateMark);
    Admin_App.directive('dpSubmitForm', Admin_Main_Directive_DpSubmitForm);
    Admin_App.directive('dpTabBody', Admin_Main_Directive_DpTabBody);
    Admin_App.directive('dpTabBtn', Admin_Main_Directive_DpTabBtn);
    Admin_App.directive('dpToggleSwitch', Admin_Main_Directive_DpToggleSwitch);
    Admin_App.factory('translateDpInterpolation', Admin_Main_Translate_DpInterpolation);
    Admin_App.config([
      '$translateProvider', function($translateProvider) {
        $translateProvider.translations('default', DP_LANG);
        $translateProvider.preferredLanguage('default');
        return $translateProvider.useInterpolation('translateDpInterpolation');
      }
    ]);
    Admin_App.config([
      '$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
        var id, makeProvider, opts, route, segs, url, views, with_lists, _i, _len, _ref, _ref1, _ref2, _results;
        $urlRouterProvider.otherwise("/");
        with_lists = {};
        makeProvider = function(view) {
          return [
            'dpTemplateManager', function(dpTemplateManager) {
              return dpTemplateManager.get(view);
            }
          ];
        };
        _results = [];
        for (_i = 0, _len = routing.length; _i < _len; _i++) {
          route = routing[_i];
          id = route.id;
          url = route.url;
          views = {};
          if (route.nav != null) {
            views['dp_section_nav'] = route.nav;
          }
          if (route.list != null) {
            views['dp_section_list@'] = route.list;
          }
          if (route.page != null) {
            views['dp_section_page@'] = route.page;
          }
          if (((_ref = route.page) != null ? _ref.templateName : void 0) != null) {
            route.page.templateProvider = makeProvider(route.page.templateName);
          }
          if (((_ref1 = route.list) != null ? _ref1.templateName : void 0) != null) {
            route.list.templateProvider = makeProvider(route.list.templateName);
          }
          if (((_ref2 = route.nav) != null ? _ref2.templateName : void 0) != null) {
            route.nav.templateProvider = makeProvider(route.nav.templateName);
          }
          opts = {
            url: url,
            views: views,
            with_nav_view: true
          };
          if (route.with_list_view) {
            opts.with_list_view = true;
          } else if (route.list != null) {
            opts.with_list_view = true;
          } else if (route.page != null) {
            segs = id.split('.');
            segs.pop();
            if (with_lists[segs.join('.')]) {
              opts.with_list_view = true;
            }
          }
          if (route.with_nav_view != null) {
            opts.with_nav_view = route.with_nav_view;
          }
          if (opts.with_list_view) {
            with_lists[id] = true;
          }
          _results.push($stateProvider.state(id, opts));
        }
        return _results;
      }
    ]);
    Admin_App.service('dpTemplateManager', [
      '$templateCache', '$http', '$q', function($templateCache, $http, $q) {
        return new Admin_Main_Service_TemplateManager($templateCache, $http, $q);
      }
    ]);
    Admin_App.config([
      '$provide', function($provide) {
        return $provide.decorator('$templateCache', [
          '$delegate', '$http', function($delegate, $http) {
            $delegate.ngGet = $delegate.get;
            $delegate.get = function(view) {
              view = view.replace(/^.*?\/adm\/load\-view\//g, '');
              return $delegate.ngGet(view);
            };
            $delegate.ngPut = $delegate.put;
            $delegate.put = function(view, value) {
              view = view.replace(/^.*?\/adm\/load\-view\//g, '');
              return $delegate.ngPut(view, value);
            };
            return $delegate;
          }
        ]);
      }
    ]);
    Admin_App.run([
      'dpTemplateManager', function(dpTemplateManager) {
        var route, t, templates, _, _i, _len;
        templates = ['Index/app-nav-setup.html', 'Index/app-nav-agents.html', 'Index/app-nav-tickets.html', 'Index/app-nav-crm.html', 'Index/app-nav-portal.html', 'Index/app-nav-chat.html', 'Index/app-nav-twitter.html', 'Index/app-nav-apps.html', 'Index/app-nav-server.html', 'Index/modal-alert.html', 'Index/modal-confirm-leavetab.html', 'Languages/modal-translate-phrase.html', 'TicketDeps/code-link.html', 'TicketDeps/code-win.html', 'TicketDeps/code-embed.html', 'Index/blank.html'];
        for (_ in routing) {
          if (!__hasProp.call(routing, _)) continue;
          route = routing[_];
          if ((route.page != null) && route.page.templateName) {
            templates.push(route.page.templateName);
          }
          if ((route.nav != null) && route.nav.templateName) {
            templates.push(route.nav.templateName);
          }
          if ((route.list != null) && route.list.templateName) {
            templates.push(route.list.templateName);
          }
        }
        for (_i = 0, _len = templates.length; _i < _len; _i++) {
          t = templates[_i];
          dpTemplateManager.load(t);
        }
        return dpTemplateManager.loadPending().then(function() {
          return window.DP_IS_BOOTED = true;
        });
      }
    ]);
    if ((_ref = window.parent) != null ? _ref.DP_FRAME_OVERLAY_admin : void 0) {
      if ((_ref1 = window.parent) != null) {
        _ref1.DP_FRAME_OVERLAY_admin.callLoaded();
      }
    }
    return Admin_App;
  });

}).call(this);

/*
//@ sourceMappingURL=App.js.map
*/