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
		 * @param {String/Object/HTMLElement} location
		 * @param {Function}      controller
		 * @return {promise}
		 */
		renderTemplate: function(tplName, location, ctrl) {
			var $injector = this.getApp().getPlatform().getNgInjector(),
				handler,
				locationSelector,
				locationPlace,
				self = this;

			tplName = Strings.trim(tplName);

			// Prepend the package name if it isn't already
			// eg "html/my-template.html" needs to be "com.example.app/html/my-template"
			if (tplName.indexOf(this.getApp().getPackageName()) === -1) {
				tplName = this.getApp().getPackageName() + '/html/' + tplName;
			}

			location = this.getElementLocationDef(location)
			locationSelector = location[0];
			locationPlace = location[1];

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
						locationEl = self.moveElementTo(element, locationSelector, locationPlace, true);
					}

					element.html(response.data);
					element.children().data('$ngControllerController', tplCtrl);
					$compile(element.contents())(tplScope);

					// Add with-app-contexts to the parent container,
					// as well as any parent context container
					element.parent().addClass('with-app-contexts')
						.closest('.dp-app-context-container').addClass('with-app-contexts');

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
			var locationSelector, locationPlace, placeMath;

			if (typeof location == 'string') {
				location = Strings.trim(location);

				placeMath = location.match(/^(append|prepend|after|before|replace)\s+ (.*?)$/);
				if (placeMath) {
					locationSelector = placeMath[0];
					locationPlace = placeMath[1];
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
		 * Called when the controller is being destroyed
		 */
		destroy: function() {

		}
	});
});