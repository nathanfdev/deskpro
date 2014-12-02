define([
  'angular',
  'AgentApp',
  'AppPlatform',
  'AppPlatformConfig',
  'Agent/AppPlatform/Context/AppContext',
  'DeskPRO/Util/Util'
], function(angular, AgentApp, AppPlatformClass, AppPlatformConfig, AppContext, Util) {

  if (!window.console) {
    window.console = {
      log: function(){},
      warn: function(){},
      error: function(){}
    }
  }

  return {
    start: function() {
      var loadModules = [], self = this;
      for (var i = 0; i < AppPlatformConfig.length; i++) {
        if (AppPlatformConfig[i].moduleName) {
          loadModules.push(AppPlatformConfig[i].moduleName);
        }
      }

      if (loadModules.length) {
        require(loadModules, function() {
          var mods, i;
          for (i = 0; i < arguments.length; i++) {
            if (arguments[i]) {
              mods = arguments[i];
              if (!Util.isArray(mods)) {
                mods = [mods];
              }

              mods.forEach(function(m) {
                AgentApp.requires.push(m.name);
              });
            }
          }

          self.startNg();
        });
      } else {
        this.startNg();
      }
    },

    startNg: function() {
      var $html = angular.element(document.getElementsByTagName('html')[0]),
          loadContexts = [],
          loadingConfigs = [],
          donePackageServices = {},
          i,
          self = this;

      AppPlatformConfig.forEach(function(appConfig) {
        if (appConfig.contextName != 'Agent/AppPlatform/Context/AppContext') {
          loadContexts.push(appConfig.contextName);
          loadingConfigs.push(appConfig);
        } else {
          appConfig.contextClass = AppContext;
        }

        // Register each AppContext as a service
        if (!donePackageServices[appConfig.packageName]) {
          donePackageServices[appConfig.packageName] = true;
          AgentApp.factory('$' + appConfig.packageName, function() {
            if (!window.AppPlatform) {
              throw "The app platform is not initialized yet.";
            }

            var a = window.AppPlatform.getPackageApp(appConfig.packageName);

            if (!a) {
              throw "The app platform is not initialized yet.";
            }

            return a;
          });
        }
      });

      angular.element().ready(function() {

        window.AppPlatform = new AppPlatformClass(AgentApp);

        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
            AgentApp.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        AgentApp.run(['$injector', function($injector) {
          // Legacy vars
          AgentApp.dpInjector = $injector;
          self.startPage();
        }]);

        if (loadContexts.length) {
          require(loadContexts, function() {
            for (var i = 0; i < loadContexts.length; i++) {
              loadingConfigs[i].contextClass = arguments[i];
            }

            for (var i = 0; i < AppPlatformConfig.length; i++) {
              AppPlatform.registerApp(AppPlatformConfig[i].contextClass, AppPlatformConfig[i]);
            }

            angular.bootstrap($html, ['AgentApp']);
            angular.resumeBootstrap();
          });
        } else {
          angular.bootstrap($html, ['AgentApp']);
          angular.resumeBootstrap();
        }
      });
    },

    startPage: function() {
      window.DP_ONLOAD();

      if (window.DeskPRO_Window) {
        window.DeskPRO_Window.initAppPlatform(window.AppPlatform);
      }
    }
  }
});