define(['angular', 'DeskPRO/Util/Strings'], function(angular, Strings) {
  return new Orb.Class({
    initialize: function(contextParams, params) {
      this._appContext  = contextParams.appContext;
      this._fragment    = contextParams.fragment;
      this._params      = params || {};
      this.init();
    },


    /**
     * Called when the controller is initiated
     */
    init: function() {

    },


    /**
     * Gets the app contexr
     * @returns {AppContext}
     */
    getApp: function() {
      return this._appContext;
    },


    /**
     * Gets the fragment
     * @returns {Object}
     */
    getFragment: function() {
      return this._fragment;
    },


    /**
     * Adds an event handler to a fragment event.
     *
     * @param event_name
     * @param fn
     */
    addFragmentEventHandler: function(event_name, fn) {
      this.getFragment().addEvent(event_name, fn);
    },


    /**
     * Gets the fragment root element
     * @returns {HTMLElement}
     */
    getFragmentElement: function() {
      return this._fragment.fragmentElement;
    },


    /**
     * Gets a passed param. This is not the same as app settings (use getApp().getSetting() for that).
     *
     * @param {String} name
     * @param {mixed} defaultValue
     * @returns {mixed}
     */
    getParameter: function(name, defaultValue) {
      if (typeof this._params.packageName[name] == 'undefined') {
        return defaultValue;
      }
      return this._params.packageName[name];
    },


    /**
     * Loads a template. If the template has been loaded before, it is fetched from the template cache.
     *
     * @param {String} tplName
     * @return {promise} Promise that resolves to the template contents
     */
    loadTemplate: function (tplName) {
      var $injector = this.getApp().getPlatform().getNgInjector(),
        runner;

      tplName = Strings.trim(tplName);

      // Prepend the package name if it isn't already
      // eg "html/my-template.html" needs to be "com.example.app/html/my-template"
      if (tplName.indexOf(this.getApp().getPackageName()) === -1) {
        tplName = this.getApp().getPackageName() + '/html/' + tplName;
      }

      runner = $injector.instantiate(['$http', '$templateCache', '$q', function($http, $templateCache, $q) {
        var deferred = $q.defer();
        this.deferred = deferred;

        $http.get(tplName, { cache: $templateCache } ).then(function(response) {
          deferred.resolve(response.data);
        }, function() {
          deferred.reject();
        });
      }]);

      return runner.deferred.promise;
    },


    /**
     * Render a template to the tab location defined by the location description in loc.
     *
     * If you want to use an actual template for the title (eg it has complex scope vars etc),
     * prefix the string with template: and then specify the template name. Use html: if the string is HTML (otherwise the string will be escaped)
     * For example:
     *
     * <code>
     * // Renders the tab with title '&lt;My Widget&gt;'
     * renderTemplateTab(..., "<My Widget>", ...);
     *
     * // Renders the tab with title '<em>My Widget</em>'
     * renderTemplateTab(..., "html:<em>My Widget</em>", ...);
     *
     * // Renders the tab with the title from widget-tab.html
     * renderTemplateTab(..., "template:widget-tab.html", ...);
     *
     * // Renders
     * </code>
     *
     * The tab title scope is injected into your controller as $tabScope. For simpler cases, the scope value `tab` is
     * syncned between the content box and the tab. That is, setting $scope.tab.abc will set that on the tab scope as $scope.tab.abc as well.
     * (So, useful for things like counts).
     *
     * @param {String}                    tabTitle
     * @param {String}                    tplName
     * @param {String/Object/HTMLElement} location
     * @param {Function}                  ctrl
     * @param {Object}                    ctrlLocals
     * @return {promise}
     */
    renderTemplateTab: function(location, tabTitle, tplName, ctrl, ctrlLocals) {
      var self = this,
        $injector = this.getApp().getPlatform().getNgInjector(),
        tabTplName,
        tabTplNameMatch,
        tabTplHtml,
        runner;

      tabTplNameMatch = tabTitle.match(/^template:(.*?)$/);
      if (tabTplNameMatch) {
        tabTplName = tabTplNameMatch[1];
      } else {
        tabTplNameMatch = tabTitle.match(/^html:(.*?)$/);
        if (tabTplNameMatch) {
          tabTplHtml = tabTplNameMatch[1];
        } else {
          tabTplHtml = Strings.escapeHtml(tabTitle);
        }
      }

      runner = $injector.instantiate(['$rootScope', '$controller', '$compile', '$q', function($rootScope, $controller, $compile, $q) {
        var tplDeferred, tplPromise,
          deferred = $q.defer();

        this.deferred = deferred;

        // Load both templates
        // We call this twice, the first time will do
        // the actual load so our tplDeferred resolves
        // when we have both templates.
        // The secondtime they are cached, so their loads will be
        // instant. We just do it so we can syncronise the loading
        if (tabTplName) {
          tplDeferred = $q.defer();
          tplDeferred.all([
            self.loadTemplate(tabTplName),
            self.loadTemplate(tplName)
          ]);
          tplPromise = tplDeferred.promise;
        } else {
          tplPromise = self.loadTemplate(tplName);
        }

        tplPromise.then(function() {
          var containerId = Orb.getUniqueId('app_context_');

          var createTab = function(tplSource) {
            var tplScope,
              tplCtrl,
              tabElement;

            tplScope = $rootScope.$new();
            tplCtrl = $controller(function() {}, { $scope: tplScope });

            tabElement = angular.element('<li data-tab-for="#'+containerId+'"></li>');

            tabElement.html(tplSource);
            tabElement.children().data('$ngControllerController', tplCtrl);
            $compile(tabElement.contents())(tplScope);

            // Then render the usual content box
            ctrlLocals = ctrlLocals || {};
            ctrlLocals.containerElementId = containerId;
            ctrlLocals.hiddenByDefault = true;
            ctrlLocals.$tabScope   = tplScope;

            self.renderTemplate(location, tplName, ctrl, ctrlLocals).then(function(info) {
              var nav = info.element.closest('.dp-simpletab-container').find('.dp-with-simpletabs').first();
              if (nav[0]) {
                nav.find('ul').append(tabElement);
                nav.data('simpletabs').addTriggerElement(tabElement);
              }

              tplScope.content = info.scope;
              info.scope.$watch('tab', function(newVal) {
                tplScope.tab = newVal;
              }, true);

              if (!info.scope.tab) {
                info.scope.tab = {};
              }

              deferred.resolve(info);
            }, function() { deferred.reject(); });
          };

          // Always render tab itself first
          // because its scope is passed as an injectable to the main content controller
          if (tabTplName) {
            self.loadTemplate(tabTplName).then(function(tabHtml) {
              createTab(tabHtml);
            }, function() { deferred.reject(); });
          } else {
            createTab(tabTplHtml);
          }
        }, function() {
          deferred.reject();
        })
      }]);

      return runner.deferred.promise;
    },


    /**
     * Render a template to the standard 'app' sidebar.
     *
     * @param {String}                    tplName
     * @param {Function}                  ctrl
     * @param {Object}                    ctrlLocals
     * @return {promise}
     */
    renderAppTemplate: function(tplName, ctrl, ctrlLocals) {
      var self = this,
        $injector = this.getApp().getPlatform().getNgInjector(),
        runner;

      runner = $injector.instantiate(['$rootScope', '$controller', '$compile', '$q', '$timeout', function($rootScope, $controller, $compile, $q, $timeout) {
        var tplDeferred, tplPromise,
          deferred = $q.defer();

        this.deferred = deferred;

        self.loadTemplate(tplName).then(function(tplSource) {
          var containerId = Orb.getUniqueId('app_context_'),
            tplScope,
            tplCtrl,
            tabElement,
            updateClassFn;

          tplScope = $rootScope.$new();

          // proxy DeskPRO App events
          $rootScope.$on('deskpro_app', function($event, name, data){
            [].splice.call(arguments, 0, 2, name);
            tplScope.$broadcast.apply(tplScope, arguments);
          });

          tplScope.btnClass   = {'is-enabled': true};
          tplScope.btnImg     = null;
          tplScope.btnBadge   = null;
          tplScope.btnText    = null;
          tplScope.enabled    = true;
          tplCtrl = $controller(function() {}, { $scope: tplScope });

          tabElement = angular.element('<li data-for="#'+containerId+'" id="'+containerId+'_tab"><span ng-if="btnBadge !== null" class="badge">{{btnBadge}}</span><img ng-if="btnImg !== null" ng-src="{{btnImg}}" /><span ng-if="btnText !== null" ng-bind="btnText"></span></li>');

          tplScope.$watch('enabled', function(n) {
            tplScope.btnClass['is-enabled'] = !!n;
            $timeout(function() { self.getFragment().updateAppsSidebar(); });
          });

          // Need to do this 'manually' because the scope is being applied to children, not the el itself
          updateClassFn = function(btnClass) {
            tabElement.removeClass();
            for (var i in btnClass) {
              if (btnClass.hasOwnProperty(i)) {
                if (btnClass[i]) {
                  tabElement.addClass(i);
                }
              }
            }
          };

          tplScope.$watch('btnClass', updateClassFn, true);

          tabElement.children().data('$ngControllerController', tplCtrl);
          $compile(tabElement.contents())(tplScope);

          // Then render the usual content box
          ctrlLocals = ctrlLocals || {};
          ctrlLocals.containerElementId = containerId;
          ctrlLocals.$tabScope = tplScope;

          self.renderTemplate('#TAB_layout_sidebar', tplName, ctrl, ctrlLocals).then(function(info) {
            updateClassFn(tplScope.btnClass);
            $('#' + self.getFragment().meta.baseId + '_layout_sidebar_icons').find('> ul').first().append(tabElement);
            deferred.resolve(info);
            $timeout(function() { self.getFragment().updateAppsSidebar(); });
          }, function() { deferred.reject(); });
        }, function() {
          deferred.reject();
        });
      }]);

      return runner.deferred.promise;
    },


    /**
     * Render a template to the location defined by the location description in loc
     *
     * @param {String}                    tplName
     * @param {String/Object/HTMLElement} location
     * @param {Function}                  ctrl
     * @param {Object}                    ctrlLocals
     * @return {promise}
     */
    renderTemplate: function(location, tplName, ctrl, ctrlLocals) {
      var $injector = this.getApp().getPlatform().getNgInjector(),
        runner,
        locationSelector,
        locationPlace,
        self = this;

      location = this.getElementLocationDef(location)
      locationSelector = location[0];
      locationPlace = location[1];

      console.log("[TabContext] Rendering %s into %s<%s>", tplName, locationSelector, locationPlace);

      if (!ctrl) {
        ctrl = function() { };
      }

      runner = $injector.instantiate(['$rootScope', '$controller', '$compile', '$q', function($rootScope, $controller, $compile, $q) {
        var tplScope,
          tplCtrl,
          element,
          locationEl,
          injectables,
          i,
          deferred = $q.defer();

        this.deferred = deferred;

        self.loadTemplate(tplName).then(function(tplSource) {
          tplScope = $rootScope.$new();

          // proxy DeskPRO App events
          $rootScope.$on('deskpro_app', function($event, name){
            [].splice.call(arguments, 0, 2, name);
            tplScope.$broadcast.apply(tplScope, arguments);
          });

          ctrlLocals = ctrlLocals || {}
          if (!ctrlLocals.containerElementId) {
            ctrlLocals.containerElementId = Orb.getUniqueId('app_context_');
          }

          ctrlLocals.$scope = tplScope;

          ctrlLocals.$context  = self;
          ctrlLocals.$app      = self.getApp();
          ctrlLocals.$platform = self.getApp().getPlatform();

          injectables = self.getInjectables();
          if (injectables) {
            for (i = 0; i < injectables.length; i++) {
              ctrlLocals[injectables[i][0]] = injectables[i][1];
            }
          }

          element = angular.element('<div class="dp-app-context"></div>');
          element.attr('id', ctrlLocals.containerElementId)

          ctrlLocals.$el = $(element)

          tplCtrl = $controller(ctrl, ctrlLocals);

          if (ctrlLocals.hiddenByDefault) {
            element.hide();
          }

          // Automatically insert the widget into the DOM
          if (locationSelector) {
            locationEl = self.moveElementTo(element, locationSelector, locationPlace, true);
          }

          element.html(tplSource);
          element.children().data('$ngControllerController', tplCtrl);
          $compile(element.contents())(tplScope);

          // Add with-app-contexts to the parent container,
          // as well as any parent context container
          element.parent().addClass('with-app-contexts')
            .closest('.dp-app-context-container').addClass('with-app-contexts');

          deferred.resolve({ template: tplName, controller: tplCtrl, scope: tplScope, element: element, locationEl: locationEl });
        }, function() {
          deferred.reject();
        });
      }]);

      return runner.deferred.promise;
    },


    /**
     * Get a location def.
     *
     * Supported syntax:
     * - Array: ['#someSelector', 'after']
     * - Array with element/jquery: [HTMLElement, 'after']
     * - String: '#someSelector' (always means 'append' mode)
     * - String: 'after #someSelector' (first word is append, prepend, after, before, replace)
     * - HTMLElement/jquery (always means 'append')
     *
     * @param {String/Array} location
     * @returns {Array}
     */
    getElementLocationDef: function(location) {
      var locationSelector, locationPlace, placeMatch;

      if (typeof location == 'string') {
        location = Strings.trim(location);

        placeMatch = location.match(/^(append|prepend|after|before|replace)\s+(.*?)$/);
        if (placeMatch) {
          locationSelector = placeMatch[2];
          locationPlace = placeMatch[1];
        } else {
          locationSelector = location;
          locationPlace = 'append';
        }

        locationSelector = this.cleanElementLocationSelector(locationSelector);
      } else if (typeof location.jquery != 'undefined' || typeof location.tagName != 'undefined') {
        locationSelector = location;
        locationPlace = 'append';
      } else {
        locationSelector = this.cleanElementLocationSelector(location[0]);
        locationPlace = location[1];
      }

      return [locationSelector, locationPlace];
    },


    /**
     * Cleans/modifies the location selector so its valid. Override this method in a sub-class to
     * add easy naming locations.
     *
     * @param locationSelector
     * @returns {XML|string}
     */
    cleanElementLocationSelector: function(locationSelector) {
      locationSelector = Strings.trim(locationSelector);

      // @some.location is shorthand for named positions in the source
      locationSelector = locationSelector.replace(/(?:^|\b)@([a-zA-Z0-9\-\._]+)\b/g, function (match, aliasName) {
        return '#TAB_' + aliasName.replace(/[^a-zA-Z0-9_]/g, '_')
      });

      // If it's using an ID, we need to prefix the tab uid to it
      // eg #TAB_page_header is really #dp_rs00ey5_page_header
      locationSelector = locationSelector.replace(/(?:^|\b)#TAB_(.*?)\b/g, '#' + this.getFragment().meta.baseId + '_$1');
      locationSelector = locationSelector.replace(/(?:^|\b)#TAB\b/g, '#' + this.getFragmentElement().attr('id'));

      return locationSelector;
    },


    /**
     * Move an element to a named position within the tab
     * @param element
     * @param locationSelector
     * @param locationPlace
     * @param fallbackToBody
     * @returns {*}
     */
    moveElementTo: function(element, locationSelector, locationPlace, fallbackToBody) {
      var locationEl = angular.element(locationSelector).first(), realLocationEl;
      if (locationEl[0]) {

        // - context containers might optionally have an app target
        //   this allows, for example, an app location to have surrounding markup (eg box, buttons etc)
        // - this matters because the container itself is often hidden by default, and then displayed
        //   when there are contexts. so this way we have the same logic of a container being hidden/shown
        //   but allowed to specify a sub element as the actual target.
        if (locationEl.hasClass('dp-app-context-container')) {
          realLocationEl = locationEl.find('.dp-app-context-target').first();
          if (!realLocationEl[0]) {
            realLocationEl = locationEl;
          }
        } else {
          realLocationEl = locationEl;
        }

        switch (locationPlace) {
          case 'append':
            realLocationEl.append(element);
            break;
          case 'prepend':
            realLocationEl.prepend(element);
            break;
          case 'after':
            realLocationEl.after(element);
            break;
          case 'before':
            realLocationEl.before(element);
            break;
          case 'replace':
            realLocationEl.replaceWith(element);
            break;
          default:
            console.warn("Invalid locationPlace in %s<%s> (will append)", locationSelector, locationPlace);
            realLocationEl.append(element);
        }
        return locationEl;
      } else {
        if (fallbackToBody) {
          console.warn("Invalid locationSelector in %s<%s> (will append to body)", locationSelector, locationPlace);
          locationEl = angular.element('body');
          locationEl.append(element);
          return locationEl;
        } else {
          return null;
        }
      }
    },


    /**
     * Get an array of [name, object] to inject on created angular controllers
     * @returns {Array}
     */
    getInjectables: function() {
      return null;
    },


    /**
     * Called when the controller is being destroyed
     */
    destroy: function() {

    }
  });
});