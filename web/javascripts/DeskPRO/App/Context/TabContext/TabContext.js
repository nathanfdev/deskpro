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
		 * Render a template to the location defined by the location description in loc
		 * @param {String}        tplName
		 * @param {String/Object} loc
		 * @param {Function}      controller
		 * @return {promise}
		 */
		renderTemplate: function(tplName, location, ctrl) {
			var $injector = this.getApp().getPlatform().getNgInjector(),
				handler,
				locationSelector,
				locationPlace;

			tplName = Strings.trim(tplName);

			// Prepend the package name if it isn't already
			// eg "html/my-template.html" needs to be "com.example.app/html/my-template"
			if (tplName.indexOf(this.getApp().getPackageName()) === -1) {
				tplName = this.getApp().getPackageName() + '/html/' + tplName;
			}

			if (typeof location == 'string') {
				locationSelector = location;
				locationPlace = 'append';
			} else {
				locationSelector = location[0];
				locationPlace = location[1];
			}

			// If it's using an ID, we need to prefix the tab uid to it
			// eg #TAB_page_header is really #dp_rs00ey5_page_header
			locationSelector = Strings.trim(locationSelector);
			locationSelector = locationSelector.replace(/#TAB_(.*?)\b/g, '#' + this.getFragment().meta.baseId + '_$1');
			locationSelector = locationSelector.replace(/#TAB\b/g, '#' + this.getFragmentElement().attr('id'));

			console.log("[TabContext] Rendering %s into %s<%s>", tplName, locationSelector, locationPlace);

			if (!ctrl) {
				ctrl = function() { };
			}

			handler = $injector.instantiate(['$http', '$templateCache', '$rootScope', '$controller', '$compile', '$q', function($http, $templateCache, $rootScope, $controller, $compile, $q) {
				var tplScope,
					tplCtrl,
					element,
					locationEl,
					deferred = $q.defer();

				$http.get(tplName, { cache: $templateCache } ).then(function(response) {
					tplScope = $rootScope.$new();
					tplCtrl = $controller(ctrl, { $scope: tplScope });

					element = angular.element('<div class="dp-app-context"></div>')

					// Automatically insert the widget into the DOM
					if (locationSelector) {
						locationEl = angular.element(locationSelector);
						if (locationEl[0]) {
							switch (locationPlace) {
								case 'append':
									locationEl.append(element);
									break;
								case 'prepend':
									locationEl.prepend(element);
									break;
								case 'after':
									locationEl.after(element);
									break;
								case 'before':
									locationEl.before(element);
									break;
								case 'replace':
									locationEl.replaceWith(element);
									break;
								default:
									console.warn("Invalid locationPlace in %s<%s> (will append)", locationSelector, locationPlace);
									locationEl.append(element);
							}
						} else {
							console.warn("Invalid locationSelector in %s<%s> (will append to body)", locationSelector, locationPlace);
							angular.element('body').append(element);
						}
					}

					element.html(response.data);
					element.children().data('$ngControllerController', tplCtrl);
					$compile(element.contents())(tplScope);

					deferred.resolve({ template: tplName, controller: tplCtrl, scope: tplScope, element: element, location: location });
				}, function() {
					deferred.reject();
				});

				this.getPromise = function() {
					return deferred.promise;
				}
			}]);

			return handler.getPromise();
		},


		/**
		 * Called when the controller is being destroyed
		 */
		destroy: function() {

		}
	});
});