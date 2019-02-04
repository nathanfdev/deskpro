define([
  'angular',
  'AgentApp',
  'AppPlatform',
  'AppPlatformConfig',
  'Agent/AppPlatform/Context/AppContext',
  'DeskPRO/Util/Util',
  'angularSelect2',
  'clipboard'
], (angular, AgentApp, AppPlatformClass, AppPlatformConfig, AppContext, Util, angularSelect2, Clipboard) => {
  window.Clipboard = Clipboard;

  if (!window.console) {
    window.console = {
      log() {},
      warn() {},
      error() {}
    };
  }

  return {
    start() {
      let loadModules = [],
        self = this;
      for (let i = 0; i < AppPlatformConfig.length; i++) {
        if (AppPlatformConfig[i].moduleName) {
          loadModules.push(AppPlatformConfig[i].moduleName);
        }
      }

      if (loadModules.length) {
        require(loadModules, function () {
          let mods,
            i;
          for (i = 0; i < arguments.length; i++) {
            if (arguments[i]) {
              mods = arguments[i];
              if (!Util.isArray(mods)) {
                mods = [mods];
              }

              mods.forEach((m) => {
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

    startNg() {
      let $html = angular.element(document.getElementsByTagName('html')[0]),
        loadContexts = [],
        loadingConfigs = [],
        donePackageServices = {},
        i,
        self = this;

      AppPlatformConfig.forEach((appConfig) => {
        if (appConfig.contextName != 'Agent/AppPlatform/Context/AppContext') {
          loadContexts.push(appConfig.contextName);
          loadingConfigs.push(appConfig);
        } else {
          appConfig.contextClass = AppContext;
        }

        // Register each AppContext as a service
        if (!donePackageServices[appConfig.packageName]) {
          donePackageServices[appConfig.packageName] = true;
          AgentApp.factory(`$${appConfig.packageName}`, () => {
            if (!window.AppPlatform) {
              throw 'The app platform is not initialized yet.';
            }

            const a = window.AppPlatform.getPackageApp(appConfig.packageName);

            if (!a) {
              throw 'The app platform is not initialized yet.';
            }

            return a;
          });
        }
      });

      angular.element().ready(() => {
        window.AppPlatform = new AppPlatformClass(AgentApp);

        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          for (let x = 0; x < window.DP_CTRL_REG.length; x++) {
            AgentApp.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        AgentApp.run(['$injector', function ($injector) {
          // Legacy vars
          AgentApp.dpInjector = $injector;
          self.startPage();
        }]);

        if (loadContexts.length) {
          require(loadContexts, function () {
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

    startPage() {
      window.DP_ONLOAD();

      if (window.DeskPRO_Window) {
        window.DeskPRO_Window.initAppPlatform(window.AppPlatform);
      }
    }
  };
});
