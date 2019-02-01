define([
  'Interface/App/Routing/StateCollection',
  'Interface/App/Routing/StateConfig',
  'Reports/App/ReportsRouting',

  'Interface/App/Service/AppConfig',
  'Interface/App/Service/TemplateLoader',
  'Interface/App/Service/TemplateManager',
  'Interface/App/Service/ReportsOverview',
  'Interface/App/Service/AgentActivity',
  'Interface/App/Service/AgentHours',
  'Interface/App/Service/TicketSatisfaction',
  'Admin/Main/DataService/EntityManager',
  'Reports/App/Service/DataServiceManager',
  'Reports/App/Service/Dashboard',
  'Reports/App/Service/DashboardWidget',
  'Reports/App/Service/DashboardsInfo',

  'Reports/Main/Service/SessionPing',
  'DeskPRO/Util/Util',
  'DeskPRO/Main/Service/Growl'
], (
  StateCollection,
  StateConfig,
  ReportsRouting,

  // Services
  AppConfig,
  TemplateLoader,
  TemplateManager,
  ReportsOverview,
  AgentActivity,
  AgentHours,
  TicketSatisfaction,
  // DASHBOARDS SPECIFIC DIRECTIVES
  Admin_Main_DataService_EntityManager,
  Reports_App_Service_DataServiceManager,
  Reports_App_Service_Dashboard,
  Reports_App_Service_DashboardWidget,
  Reports_App_Service_DashboardsInfo,

  Reports_Main_Service_SessionPing,
  Util,
  DeskPRO_Main_Service_Growl
) =>
  function(Module) {
    Module.service('AppConfig', () => new AppConfig);

    Module.service('TemplateLoader', [ 'AppConfig', '$http', '$q', function(AppConfig, $http, $q) {
      window.DP_TEMPLATE_LOADER = new TemplateLoader(AppConfig.getBaseUrl() + 'agent/viewer/load-views', $http, $q);
      return window.DP_TEMPLATE_LOADER;
    }
    ]);

    Module.service('TemplateManager', ['TemplateLoader', '$templateCache', '$q', (TemplateLoader, $templateCache, $q) => new TemplateManager(TemplateLoader, $templateCache, $q)
    ]);

    Module.run(['TemplateLoader', function(TemplateLoader) {} ]);

    Module.run(['TemplateManager', function(TemplateManager) {
      const templates = [
        'ReportsInterfaceBundle:Dashboard/Modal:add-widget-variables.html',
      ];

      const reportStates = new StateCollection(StateConfig.createFactory('DeskPRO.InterfaceApp'));
      ReportsRouting(reportStates);

      // just copy paste from old-style reports
      for (let route of Array.from(reportStates.routes)) {
        if (route.tpl) {
          templates.push(route.tpl);
        }
      }

      for (let t of Array.from(templates)) {
        TemplateManager.load(t);
      }

      return TemplateManager.loadPending();
    }
    ]);

    Module.factory('HttpTemplateInterceptor', [function() {
      const isTemplateUrl = url => !!url.replace(/^\//, '').match(/^(AgentBundle|InterfaceBundle|ReportsInterfaceBundle):/);
      const getViewName = url => url.replace(/^\//, '');
      const getLoadUrl = view => window.DP_TEMPLATE_LOADER.getLoadUrl([view]) + '&intercepted=1';

      return {
        request(config) {
          if (isTemplateUrl(config.url)) {
            config.url = getLoadUrl(getViewName(config.url));
            config.dp_is_template = true;
          }

          return config;
        }
      };
    }
    ]);

    Module.factory('dpHttpInterceptor', ['$q', $q =>
      ({
        request(config) {
          if (window.DP_SESSION_ID) {
            config.headers['X-DeskPRO-Session-ID'] = window.DP_SESSION_ID;
          }
          if (window.DP_REQUEST_TOKEN) {
            config.headers['X-DeskPRO-Request-Token'] = window.DP_REQUEST_TOKEN;
          }

          return config;
        },

        response(response) {
          return response;
        },

        requestError(rejection) {
          return $q.reject(rejection);
        },

        responseError(rejection) {
          return $q.reject(rejection);
        }
      })

    ]);

    Module.config(['$provide', $provide =>
      $provide.decorator('$http', function($delegate) {

        var formatUrlObject = function(obj, baseName) {
          if (baseName == null) { baseName = false; }
          let url = '';
          return (() => {
            const result = [];
            for (let k of Object.keys(obj || {})) {
              let v = obj[k];
              if (v === null) { continue; }
              if (baseName) {
                k = baseName + '[' + encodeURIComponent(k) + ']';
              } else {
                k = encodeURIComponent(k);
              }

              if (Util.isObject(v)) {
                result.push(url += formatUrlObject(v, k));
              } else {
                v = encodeURIComponent(v);
                result.push(url += `${k}=${v}&`);
              }
            }
            return result;
          })();
        };

        $delegate.formatApiUrl = function(endpoint, params, signed) {
          if (signed == null) { signed = true; }
          endpoint = endpoint.replace(/^\//, '');
          let url = `${window.DP_BASE_API_URL}/${endpoint}`;

          if (params) {
            url += url.indexOf('?') === -1 ? '?' : '&';
            if (Util.isArray(params)) {
              for (let itm of Array.from(params)) {
                const k = encodeURIComponent(itm.name);
                const v = encodeURIComponent(itm.value);
                url += `${k}=${v}&`;
              }
            } else {
              url += formatUrlObject(params);
            }
          }

          url = url.replace(/&$/, '');

          if (signed) { url = this.signUrl(url); }

          return url;
        };

        $delegate.formatApi2Url = function(endpoint, params) {
          endpoint = endpoint.replace(/^\//, '');
          let url = `${window.DP_BASE_API_URL}/v2/${endpoint}`;

          if (params) {
            url += url.indexOf('?') === -1 ? '?' : '&';
            if (Util.isArray(params)) {
              for (let itm of Array.from(params)) {
                const k = encodeURIComponent(itm.name);
                const v = encodeURIComponent(itm.value);
                url += `${k}=${v}&`;
              }
            } else {
              url += formatUrlObject(params);
            }
          }

          url = url.replace(/&$/, '');

          return url;
        };

        $delegate.signUrl = function(url) {
          url += url.indexOf('?') === -1 ? '?' : '&';
          url += `XDEBUG_SESSION_START=PHPSTORM&API-TOKEN=${window.DP_API_TOKEN}&SESSION-ID=${window.DP_SESSION_ID}&REQUEST-TOKEN=${window.DP_REQUEST_TOKEN}`;
          return url;
        };

        return $delegate;
      })

    ]);

    /*
     * Config section
     */
    Module.config(['$httpProvider', $httpProvider => $httpProvider.interceptors.push('dpHttpInterceptor')
    ]);

    Module.config(['$httpProvider', $httpProvider => $httpProvider.interceptors.push('HttpTemplateInterceptor')
    ]);
    Module.service('em', [ () => new Admin_Main_DataService_EntityManager()
    ]);

    Module.factory('DataService', [ '$injector', $injector => new Reports_App_Service_DataServiceManager($injector)
    ]);

    Module.service('DashboardService', ['Api', 'Api2', '$q', (Api, Api2, $q) => new Reports_App_Service_Dashboard(Api, Api2, $q)
    ]);
    Module.service('DashboardWidgetService', ['Api', 'Api2', '$q', (Api, Api2, $q) => new Reports_App_Service_DashboardWidget(Api, Api2, $q)
    ]);
    Module.service('DashboardsInfo', ['Api', 'Api2', '$q', (Api, Api2, $q) => new Reports_App_Service_DashboardsInfo(Api, Api2, $q)
    ]);
    Module.service('ReportsOverviewService', ['Api', '$q', (Api, $q) => new ReportsOverview(Api, $q)
    ]);
    Module.service('AgentActivityService', ['Api', '$sce', (Api, $sce) => new AgentActivity(Api, $sce)
    ]);
    Module.service('AgentHoursService', ['Api', '$sce', '$q', (Api, $sce, $q) => new AgentHours(Api, $sce, $q)
    ]);
    Module.service('TicketSatisfactionService', ['Api', '$sce', '$q', '$timeout', (Api, $sce, $q, $timeout) => new TicketSatisfaction(Api, $sce, $q, $timeout)
    ]);

    Module.service('SessionPing', ['Api', Api => new Reports_Main_Service_SessionPing(Api)
    ]);
    Module.run(['SessionPing', SessionPing =>
      window.setTimeout(() => SessionPing.startInterval()
      , 20000)

    ]);
    Module.run(['DashboardWidgetService', DashboardWidgetService => DashboardWidgetService.loadGroupParams()
    ]);

    return Module.service('Growl', [ () => new DeskPRO_Main_Service_Growl()
    ]);
  }
);
