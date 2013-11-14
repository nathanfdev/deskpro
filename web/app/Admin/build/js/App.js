(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['angular', 'AdminRouting', 'Admin/Main/Service/AppState', 'Admin/Main/Service/DpApi', 'Admin/Main/Service/Growl', 'Admin/Main/Service/InhelpState', 'Admin/Main/Service/TemplateManager', 'Admin/Main/Service/DataServiceManager', 'Admin/OptionBuilder/TypesDef/TicketCriteria', 'Admin/OptionBuilder/TypesDef/TicketActions', 'Admin/OptionBuilder/TypesDef/TicketFilter', 'DeskPRO/Logger/Logger', 'DeskPRO/Logger/Handler/ConsoleHandler', 'Admin/Logging/InterfaceTimer', 'DeskPRO/Directive/DpTimeWithUnit', 'Admin/Main/Directive/Autofocus', 'Admin/Main/Directive/BgImg', 'Admin/Main/Directive/DpCommaSeparated', 'Admin/Main/Directive/DpErrorClass', 'Admin/Main/Directive/DpHelpPage', 'Admin/Main/Directive/DpHideSpinning', 'Admin/Main/Directive/DpInhelpBody', 'Admin/Main/Directive/DpInhelpBtn', 'Admin/Main/Directive/DpListAutoload', 'Admin/Main/Directive/DpNavSubnav', 'Admin/Main/Directive/DpOpenPhraseEditor', 'Admin/Main/Directive/DpOrderMenu', 'Admin/Main/Directive/DpPingFlash', 'Admin/Main/Directive/DpRegisterMessage', 'Admin/Main/Directive/DpServerValidation', 'Admin/Main/Directive/DpShowSpinning', 'Admin/Main/Directive/DpStateMark', 'Admin/Main/Directive/DpSubmitForm', 'Admin/Main/Directive/DpTabBody', 'Admin/Main/Directive/DpTabBtn', 'Admin/Main/Directive/DpToggleSwitch', 'Admin/Main/Directive/DpTristateCheck', 'Admin/Main/Directive/DpWorkingHours', 'Admin/TicketDeps/Directive/LayoutEditor', 'Admin/TicketDeps/Directive/LayoutEditorField', 'Admin/Main/DataService/EntityManager', 'Admin/Main/DataService/Departments', 'Admin/FeedbackStatuses/DataService/FeedbackStatuses', 'Admin/FeedbackTypes/DataService/FeedbackTypes', 'Admin/FeedbackCategories/DataService/FeedbackCategories', 'Admin/TicketAccounts/DataService/TicketAccounts', 'Admin/Labels/Service/LabelManager'], function(angular, routing, Admin_Main_Service_AppState, Admin_Main_Service_DpApi, Admin_Main_Service_Growl, Admin_Main_Service_InhelpState, Admin_Main_Service_TemplateManager, Admin_Main_Service_DataServiceManager, Admin_OptionBuilder_TypesDef_TicketCriteria, Admin_OptionBuilder_TypesDef_TicketActions, Admin_OptionBuilder_TypesDef_TicketFilter, Logger, Logger_ConsoleHandler, Admin_Logging_InterfaceTimer, DeskPRO_Directive_DpTimeWithUnit, Admin_Main_Directive_Autofocus, Admin_Main_Directive_BgImg, Admin_Main_Directive_DpCommaSeparated, Admin_Main_Directive_DpErrorClass, Admin_Main_Directive_DpHelpPage, Admin_Main_Directive_DpHideSpinning, Admin_Main_Directive_DpInhelpBody, Admin_Main_Directive_DpInhelpBtn, Admin_Main_Directive_DpListAutoload, Admin_Main_Directive_DpNavSubnav, Admin_Main_Directive_DpOpenPhraseEditor, Admin_Main_Directive_DpOrderMenu, Admin_Main_Directive_DpPingFlash, Admin_Main_Directive_DpRegisterMessage, Admin_Main_Directive_DpServerValidation, Admin_Main_Directive_DpShowSpinning, Admin_Main_Directive_DpStateMark, Admin_Main_Directive_DpSubmitForm, Admin_Main_Directive_DpTabBody, Admin_Main_Directive_DpTabBtn, Admin_Main_Directive_DpToggleSwitch, Admin_Main_Directive_DpTristateCheck, Admin_Main_Directive_DpWorkingHours, Admin_TicketDeps_Directive_LayoutEditor, Admin_TicketDeps_Directive_LayoutEditorField, Admin_Main_DataService_EntityManager, Admin_Main_DataService_Departments, Admin_FeedbackStatuses_DataService_FeedbackStatuses, Admin_FeedbackTypes_DataService_FeedbackTypes, Admin_FeedbackCategories_DataService_FeedbackCategories, Admin_TicketAccounts_DataService_TicketAccounts, Admin_Labels_Service_LabelManager) {
    var Admin_App, _ref, _ref1;
    Admin_App = angular.module('Admin_App', ['ngAnimate', 'ui.router', 'ui.bootstrap', 'ui.select2', 'ui.sortable', 'ui.ace', 'angularMoment', 'deskpro.option_builder', 'deskpro.category_builder']);
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
    Admin_App.factory('jsErrorLogger', [
      '$injector', function($injector) {
        var jsErrorLogger;
        jsErrorLogger = (function() {
          function jsErrorLogger() {}

          jsErrorLogger.prototype.getApi = function() {
            return $injector.get('Api');
          };

          jsErrorLogger.prototype.logScriptError = function(message, scriptFile, scriptLine, trace, context_data) {
            if (scriptFile == null) {
              scriptFile = '';
            }
            if (scriptLine == null) {
              scriptLine = 0;
            }
            if (trace == null) {
              trace = '';
            }
            if (context_data == null) {
              context_data = {};
            }
            if (context_data.url == null) {
              context_data.url = window.location + '';
            }
            try {
              return this.getApi().sendPostJson('/log-js-error', {
                message: message,
                script_file: scriptFile,
                script_line: scriptLine,
                trace: trace,
                context: context_data
              });
            } catch (_error) {}
          };

          jsErrorLogger.prototype.logException = function(exception, context_data) {
            var trace;
            trace = printStackTrace({
              e: exception
            });
            if (trace) {
              trace = trace.join("\n");
            }
            if (exception instanceof Error || (exception.message != null)) {
              return this.logScriptError(exception.message, exception.fileName || '', exception.lineNumber || '', trace, context_data);
            } else if (exception.sourceURL != null) {
              return this.logScriptError(exception.message, exception.sourceURL, exception.line, trace);
            }
          };

          jsErrorLogger.prototype.logErrorMessage = function(message, context_data) {
            return this.logError(message);
          };

          return jsErrorLogger;

        })();
        return new jsErrorLogger();
      }
    ]);
    Admin_App.factory('$exceptionHandler', [
      'jsErrorLogger', function(jsErrorLogger) {
        return function(exception, cause) {
          return window.setTimeout(function() {
            jsErrorLogger.logException(exception);
            exception._dpNoLog = true;
            throw exception;
          }, 1);
        };
      }
    ]);
    Admin_App.service('InhelpState', [
      'Api', function(Api) {
        return new Admin_Main_Service_InhelpState(Api);
      }
    ]);
    Admin_App.factory('dpHttpInterceptor', [
      function() {
        var updateTimes;
        updateTimes = [];
        return {
          request: function(config) {
            var next, timeEnc, _ref;
            if (((_ref = config.headers) != null ? _ref['X-DeskPRO-API-Token'] : void 0) != null) {
              config.startTime = new Date();
              next = updateTimes.pop();
              if (next) {
                if (config.url.indexOf('?') === -1) {
                  config.url += '?';
                } else {
                  config.url += '&';
                }
                timeEnc = ((next.timeTaken / 1000) + "").replace(/\./, '_');
                config.url += "__dp_reqtime=" + next.requestId + "_t" + timeEnc;
              }
            }
            return config;
          },
          response: function(response) {
            var headers, lastRequestId, lastRequestTime;
            if (response.config.startTime) {
              headers = response.headers();
              if (headers['x-deskpro-requestid'] != null) {
                lastRequestId = headers['x-deskpro-requestid'];
                lastRequestTime = ((new Date()).getTime()) - response.config.startTime.getTime();
                updateTimes.push({
                  timeTaken: lastRequestTime,
                  requestId: lastRequestId
                });
              }
            }
            return response;
          },
          requestError: function(rejection) {
            return rejection;
          },
          responseError: function(rejection) {
            return rejection;
          }
        };
      }
    ]);
    Admin_App.config([
      '$httpProvider', function($httpProvider) {
        return $httpProvider.interceptors.push('dpHttpInterceptor');
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
    Admin_App.service('FeedbackStatusesData', [
      'em', 'Api', '$q', function(em, Api, $q) {
        return new Admin_FeedbackStatuses_DataService_FeedbackStatuses(em, Api, $q);
      }
    ]);
    Admin_App.service('FeedbackTypesData', [
      'em', 'Api', '$q', function(em, Api, $q) {
        return new Admin_FeedbackTypes_DataService_FeedbackTypes(em, Api, $q);
      }
    ]);
    Admin_App.service('FeedbackCategoriesData', [
      'em', 'Api', '$q', function(em, Api, $q) {
        return new Admin_FeedbackCategories_DataService_FeedbackCategories(em, Api, $q);
      }
    ]);
    Admin_App.service('TicketAccountsData', [
      'em', 'Api', '$q', function(em, Api, $q) {
        return new Admin_TicketAccounts_DataService_TicketAccounts(em, Api, $q);
      }
    ]);
    Admin_App.service('LabelManager', [
      'Api', '$q', function(Api, $q) {
        return new Admin_Labels_Service_LabelManager(Api, $q);
      }
    ]);
    Admin_App.filter('escape_url', [
      function() {
        return function(text) {
          return encodeURIComponent(text);
        };
      }
    ]);
    Admin_App.service('Growl', [
      function() {
        return new Admin_Main_Service_Growl();
      }
    ]);
    Admin_App.factory('dpObTypesDefTicketCriteria', [
      '$q', 'Api', 'dpTemplateManager', function($q, Api, dpTemplateManager) {
        return new Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, dpTemplateManager);
      }
    ]);
    Admin_App.factory('dpObTypesDefTicketActions', [
      '$q', 'Api', 'dpTemplateManager', function($q, Api, dpTemplateManager) {
        return new Admin_OptionBuilder_TypesDef_TicketActions($q, Api, dpTemplateManager);
      }
    ]);
    Admin_App.factory('dpObTypesDefTicketFilter', [
      '$q', 'Api', 'dpTemplateManager', function($q, Api, dpTemplateManager) {
        return new Admin_OptionBuilder_TypesDef_TicketFilter($q, Api, dpTemplateManager);
      }
    ]);
    Admin_App.factory('DataService', [
      '$injector', function($injector) {
        return new Admin_Main_Service_DataServiceManager($injector);
      }
    ]);
    Admin_App.directive('dpTimeWithUnit', DeskPRO_Directive_DpTimeWithUnit);
    Admin_App.directive('autofocus', Admin_Main_Directive_Autofocus);
    Admin_App.directive('bgImg', Admin_Main_Directive_BgImg);
    Admin_App.directive('dpCommaSeparated', Admin_Main_Directive_DpCommaSeparated);
    Admin_App.directive('dpErrorClass', Admin_Main_Directive_DpErrorClass);
    Admin_App.directive('dpHelpPage', Admin_Main_Directive_DpHelpPage);
    Admin_App.directive('dpHideSpinning', Admin_Main_Directive_DpHideSpinning);
    Admin_App.directive('dpInhelpBody', Admin_Main_Directive_DpInhelpBody);
    Admin_App.directive('dpInhelpBtn', Admin_Main_Directive_DpInhelpBtn);
    Admin_App.directive('dpListAutoload', Admin_Main_Directive_DpListAutoload);
    Admin_App.directive('dpNavSubnav', Admin_Main_Directive_DpNavSubnav);
    Admin_App.directive('dpOpenPhraseEditor', Admin_Main_Directive_DpOpenPhraseEditor);
    Admin_App.directive('dpOrderMenu', Admin_Main_Directive_DpOrderMenu);
    Admin_App.directive('dpPingFlash', Admin_Main_Directive_DpPingFlash);
    Admin_App.directive('dpRegisterMessage', Admin_Main_Directive_DpRegisterMessage);
    Admin_App.directive('dpServerValidation', Admin_Main_Directive_DpServerValidation);
    Admin_App.directive('dpShowSpinning', Admin_Main_Directive_DpShowSpinning);
    Admin_App.directive('dpStateMark', Admin_Main_Directive_DpStateMark);
    Admin_App.directive('dpSubmitForm', Admin_Main_Directive_DpSubmitForm);
    Admin_App.directive('dpTabBody', Admin_Main_Directive_DpTabBody);
    Admin_App.directive('dpTabBtn', Admin_Main_Directive_DpTabBtn);
    Admin_App.directive('dpToggleSwitch', Admin_Main_Directive_DpToggleSwitch);
    Admin_App.directive('dpTristateCheck', Admin_Main_Directive_DpTristateCheck);
    Admin_App.directive('dpWorkingHours', Admin_Main_Directive_DpWorkingHours);
    Admin_App.directive('dpTicketLayoutEditor', Admin_TicketDeps_Directive_LayoutEditor);
    Admin_App.directive('dpTicketLayoutEditorField', Admin_TicketDeps_Directive_LayoutEditorField);
    Admin_App.config([
      '$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider) {
        var id, makeProvider, opts, route, url, v, viewName, _i, _len, _results;
        $urlRouterProvider.otherwise("/");
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
          if (route.templateName != null) {
            route.templateProvider = makeProvider(route.templateName);
          }
          opts = {
            url: url
          };
          if (route.views) {
            opts.views = route.views;
          } else {
            opts.views = {};
            v = {};
            if (route.templateProvider) {
              v.templateProvider = route.templateProvider;
            } else if (route.templateName) {
              v.templateName = route.templateName;
            }
            if (route.controller) {
              v.controller = route.controller;
            }
            if (route.target) {
              viewName = route.target;
            } else {
              if (id.split('.').length === 2) {
                viewName = "appbody";
              } else {
                viewName = "";
              }
            }
            opts.views[viewName] = v;
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
          '$delegate', function($delegate) {
            $delegate.ngGet = $delegate.get;
            $delegate.get = function(view) {
              view = view.replace(/^.*?\/admin\/load\-view\//g, '');
              return $delegate.ngGet(view);
            };
            $delegate.ngPut = $delegate.put;
            $delegate.put = function(view, value) {
              view = view.replace(/^.*?\/admin\/load\-view\//g, '');
              return $delegate.ngPut(view, value);
            };
            return $delegate;
          }
        ]);
      }
    ]);
    Admin_App.config([
      '$provide', function($provide) {
        $provide.decorator('$q', [
          '$delegate', function($delegate) {
            $delegate.fcall = function(fn) {
              var d;
              d = $delegate.defer();
              d.resolve(fn());
              return d.promise;
            };
            $delegate.isPromise = function(val) {
              return val.then != null;
            };
            return $delegate;
          }
        ]);
        return $provide.decorator('$state', [
          '$delegate', '$stateParams', function($delegate, $stateParams) {
            /*
             		# Checks to see if a certain state is currently active
             		#
             		# @param {String} stateId The state to check. If it begins with a leading dot, we'll cehck
             		#                         if the id exists anywhere in the current state. E.g., shorter to write '.create' than 'x.y.z.create'
             		# @param {Object} stateParams If provided, then the params specified must also match
            */

            $delegate.isStateActive = function(stateId, stateParams) {
              var k, v;
              if (stateParams == null) {
                stateParams = null;
              }
              if (!$delegate.current) {
                return false;
              }
              if (stateParams) {
                if (stateId.charAt(0) === '.') {
                  if ($delegate.current.name.indexOf(stateId) === -1) {
                    return false;
                  }
                } else {
                  if ($delegate.current.name !== stateId) {
                    return false;
                  }
                }
                if (!$delegate.$current.params) {
                  return false;
                }
                for (k in stateParams) {
                  if (!__hasProp.call(stateParams, k)) continue;
                  v = stateParams[k];
                  if (($stateParams[k] == null) || $stateParams[k] !== v) {
                    return false;
                  }
                }
                return true;
              } else {
                if (stateId.charAt(0) === '.') {
                  return $delegate.current.name.indexOf(stateId) !== -1;
                } else {
                  return $delegate.current.name === stateId;
                }
              }
            };
            return $delegate;
          }
        ]);
      }
    ]);
    Admin_App.factory('dpInterfaceTimer', [
      '$log', function($log) {
        return new Admin_Logging_InterfaceTimer($log);
      }
    ]);
    Admin_App.config([
      '$provide', function($provide) {
        return $provide.decorator('$rootScope', [
          'dpInterfaceTimer', '$delegate', function(dpInterfaceTimer, $delegate) {
            var origDigest;
            origDigest = $delegate.$digest;
            $delegate.$digest = function() {
              var ret;
              dpInterfaceTimer.startDigest();
              ret = origDigest.apply($delegate, arguments);
              dpInterfaceTimer.endDigest();
              return ret;
            };
            return $delegate;
          }
        ]);
      }
    ]);
    Admin_App.config([
      '$provide', function($provide) {
        return $provide.decorator('$log', [
          '$delegate', function($delegate) {
            var consoleHandler, logger;
            logger = new Logger('console');
            consoleHandler = new Logger_ConsoleHandler(Logger.DEBUG);
            logger.pushHandler(consoleHandler);
            return logger;
          }
        ]);
      }
    ]);
    Admin_App.run([
      'dpTemplateManager', function(dpTemplateManager) {
        var route, t, templates, _, _i, _len;
        templates = ['Index/app-nav-setup.html', 'Index/app-nav-agents.html', 'Index/app-nav-tickets.html', 'Index/app-nav-crm.html', 'Index/app-nav-portal.html', 'Index/app-nav-chat.html', 'Index/app-nav-twitter.html', 'Index/app-nav-apps.html', 'Index/app-nav-server.html', 'Index/modal-alert.html', 'Index/modal-confirm-leavetab.html', 'Languages/modal-translate-phrase.html', 'TicketDeps/code-phpapi.html', 'TicketDeps/code-link.html', 'TicketDeps/code-win.html', 'TicketDeps/code-embed.html', 'Index/blank.html', 'Common/work-hours-directive.html', 'TicketDeps/layout-editor.html', 'TicketDeps/layout-editor-field.html'];
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