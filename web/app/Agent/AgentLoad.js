define([
  'angular',
  'AgentApp',
  'AppPlatform',
  'AppPlatformConfig',
  'Agent/AppPlatform/Context/AppContext',
  'DeskPRO/Util/Util'
], function(angular, AgentApp, AppPlatform, AppPlatformConfig, AppContext, Util) {

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
          i,
          self = this;

      for (i = 0; i < AppPlatformConfig.length; i++) {
        if (AppPlatformConfig[i].contextName != 'Agent/AppPlatform/Context/AppContext') {
          loadContexts.push(AppPlatformConfig[i].contextName);
          loadingConfigs.push(AppPlatformConfig[i]);
        } else {
          AppPlatformConfig[i].contextClass = AppContext;
        }
      }

      angular.element().ready(function() {

        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
            AgentApp.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        AgentApp.run(['$timeout', '$injector', function($timeout, $injector) {
          // Legacy vars
          AgentApp.dpInjector = $injector;
          window.AppPlatform = new AppPlatform(AgentApp);

          if (loadContexts.length) {
            require(loadContexts, function() {
              for (var i = 0; i < loadContexts.length; i++) {
                loadingConfigs[i].contextClass = arguments[i];
              }

              self.startPage();
            });
          } else {
            self.startPage();
          }
        }]);

        angular.bootstrap($html, ['AgentApp']);
        angular.resumeBootstrap();
      });
    },

    startPage: function() {
      window.DP_ONLOAD();

      for (var i = 0; i < AppPlatformConfig.length; i++) {
        window.AppPlatform.registerApp(AppPlatformConfig[i].contextClass, AppPlatformConfig[i]);
      }

      if (window.DeskPRO_Window) {
        window.DeskPRO_Window.initAppPlatform(window.AppPlatform);
      }
    }
  }
});