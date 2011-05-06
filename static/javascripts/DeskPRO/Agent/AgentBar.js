Orb.createNamespace('DeskPRO.Agent');

/**
 * A limited verso
 */
DeskPRO.Agent.AgentBar = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {

		this.options = {};
		this.registry = {};

		this.messageBroker = this.messageBroker = new DeskPRO.MessageBroker();

		if (options) {
			this.setOptions(options);
		}

		this.routePrefixes = {};

		this.messageChanneler = null;
		this.poller = null;

		this.notifier = null;
	},

	initPage: function() {
		this._initBasic();
		this._initRoutes();
		this._initWindowInterface();
	},

	//#################################################################
	//# Global registry, getters
	//#################################################################

	/**
	 * Get the message broker
	 */
	getMessageBroker: function() {
		return this.messageBroker;
	},

			
	/**
	 * Get the notifier
	 */
	 getNotifier: function() {
		return this.notifier;
	},



	/**
	 * Get the AJAX poller
	 */
	getPoller: function() {
		return this.poller;
	},



	/**
	 * Get a name for some type of basic thing (department, category etc).
	 */
	getDisplayName: function(type, id) {
		if (!window.DESKPRO_NAME_REGISTRY[type] || !window.DESKPRO_NAME_REGISTRY[type][id]) {
			if (!window.DESKPRO_NAME_REGISTRY[type]) {
				console.warn('Unknown name type %s', type);
			}

			return null;
		}

		return window.DESKPRO_NAME_REGISTRY[type][id];
	},


	/**
	 * Get a URL pattern
	 */
	getUrl: function(name, vars) {
		if (!window.DESKPRO_URL_REGISTRY[name]) {
			console.warn('Unknown url name %s', name);
			return null;
		}

		var url = window.DESKPRO_URL_REGISTRY[name];
		if (vars) {
			Object.each(vars, function(v,k) {
				url = url.replace('{'+k+'}', v);
			});
		}

		return url;
	},


	/**
	 * Get data
	 * @param name
	 */
	getData: function(name) {
		if (!window.DESKPRO_DATA_REGISTRY[name]) {
			console.warn('Unknown data name %s', name);
			return null;
		}

		return window.DESKPRO_DATA_REGISTRY[name];
	},

	//#################################################################
	//# Routes and page loading
	//#################################################################

	/**
	 * Add a loader for a particular prefix.
	 *
	 * @param {String} prefix The prefix to lisen for. Eg "navpane:tickets"
	 * @param {Function} callback The function to call when the prefix is used
	 */
	addPageRouteLoader: function(prefix, callback) {
		if (this.routePrefixes[prefix] == undefined) {
			this.routePrefixes[prefix] = [];
		}

		this.routePrefixes[prefix].push(callback);
	},



	/**
	 * Loads a route.
	 *
	 * @param {String} route The route to match, like navpane:tickets:filters
	 */
	runPageRoute: function(route) {

		// Like:
		// master.masterTag:sectioninfo:moreinfo:url/here/at/end
		// (There might not be any sectioninfo)
		// Example:
		// listpane:/agent/ticket-search/filter/123

		var sections = route.split(':');
		var master = sections.shift();
		var masterTag = null;
		if (master.indexOf('.') != -1) {
			var tmp = master.split('.');
			master = tmp.shift();
			masterTag = tmp.pop();
		}

		var url = sections.pop();

		var data = {
			'route': route,
			'master': master,
			'masterTag': masterTag,
			'sections': sections,
			'url': url,
			stopListeners: false
		};

		var found_listener = false;

		Object.each(this.routePrefixes, function(listeners, prefix) {
			if (route.indexOf(prefix) == 0) {
				Array.each(listeners, function(callback) {
					callback(data);
					found_listener = true;
				});
				if (data.stopListeners) {
					return true;
				}
			}
		}, this);

		if (!found_listener) {
			console.warn('Unknown route: %s', route);
		}
	},



	/**
	 * Loads a route attached to an element. Useful for quickly assigning click events.
	 *
	 * @param {jQuery} el The element to inspect for a route
	 */
	runPageRouteFromElement: function(el) {

		el = $(el);

		if (!el.data('route')) {
			console.warn('Element has no route: %o', el);
		}

		this.runPageRoute(el.data('route'));

		if (el.data('route-alt')) {
			this.runPageRoute(el.data('route-alt'));
		}
	},



	/**
	 * Load route data into the interface.
	 *
	 * @param {Object} routeData
	 */
	loadRoute: function(routeData) {

		routeData.openInSection = routeData.master;

		// Rewrote listpane's to normal pages if listpane
		// is current collapnsed
		if (routeData.openInSection == 'listpane' && false /* layout west is closed todo */) {
			routeData.openInSection = 'page';

			// If it's an alt page, they're used to link views
			// But we don't want to open a new tab automatically
			// if we're using tabbed mode
			if (routeData.masterTag == 'alt') {
				return;
			}
		}

		switch (routeData.openInSection) {
			case 'listpane':
				this.loadListPane(routeData.url, routeData);
				break;

			default:
				this.loadPage(routeData.url, routeData);
				break;
		}
	},



	/**
	 * Load a URL and treat it as a list pane.
	 *
	 * @param {String} url The URL of the list pane.
	 */
	loadListPane: function(url, routeData, callback) {

		if ($('#pane_list_tabs li').length >= 5) {
			DeskPRO_Window.showAlert('You have too many tabs open on the left. Close one before trying to open another', 'error');
			return;
		}

		if (routeData && !routeData.ignoreExist) {
			var existTab = this.listTabStrip.getTabByRouteUrl(url);
			if (existTab && !(existTab.page && existTab.page.allowDupe)) {
				this.listTabStrip.activateTabById(existTab.id);
				return;
			}
		}

		this._doAjaxLoadRoute(url, routeData, (function(data) {
				this.stopLoadingIndicator();
				var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.ListPane.Basic');

				page.setMetaData('routeUrl', url);
				if (routeData) {
					page.setMetaData('routeData', routeData);
				}

				this.addListPage(page);

				if (callback) callback(page);
			}).bind(this)
		);
	},



	/**
	 * Load a URL and treat and put it into the tabbed pane.
	 *
	 * @param {String} url The URL of the page
	 */
	loadPage: function(url, routeData, callback) {

		if ($('#pane_tabs li').length >= 10) {
			DeskPRO_Window.showAlert('You have too many tabs open on the right. Close one before trying to open another', 'error');
			return;
		}

		if (routeData && !routeData.ignoreExist) {
			var existTab = this.pageTabStrip.getTabByRouteUrl(url);
			if (existTab && !(existTab.page && existTab.page.allowDupe)) {
				this.pageTabStrip.activateTabById(existTab.id);
				return;
			}
		}

		this._doAjaxLoadRoute(url, routeData, (function(data) {
				this.stopLoadingIndicator();
				var page = this.createPageFragment(data);

				page.setMetaData('routeUrl', url);
				if (routeData) {
					page.setMetaData('routeData', routeData);
				}

				this.addPageTab(page);

				if (callback) callback(page);
			}).bind(this)
		);
	},

	//#################################################################
	//# Inits
	//#################################################################

	_initBasic: function() {

		this.messageChanneler = new DeskPRO.MessageChanneler.AjaxChanneler(this.messageBroker, this.options.messageChanneler);
		this.messageChanneler.subscribeChannel('tickets.new-tickets');
		this.messageChanneler.subscribeChannel('tickets.new-messages');

		// todo check if we still need this
		this.poller = new DeskPRO.AjaxPoller.MessagePoller(this.messageBroker, {
			ajaxUrl: BASE_URL + 'agent/poller',
			interval: 3600000
		});

		var self = this;

		this.notifier = new DeskPRO.Agent.Notifier.Notifier({
			notifySummaryButton: $('#notify_button'),
			notifyList: $('#notify_list')
		});

		// Re-dispatch ticket messages to their specific tickets
		this.getMessageBroker().addMessageListener('tickets.new-messages', (function(data) {
			var name = 'tickets.new-messages.' + data.ticket_id;
			this.getMessageBroker().sendMessage(name, data);
		}).bind(this));
	},

	_initRoutes: function() {
		// Set ourselves up as the first route listener
		this.addPageRouteLoader('navpane', this.loadRoute.bind(this));
		this.addPageRouteLoader('listpane', this.loadRoute.bind(this));
		this.addPageRouteLoader('page', this.loadRoute.bind(this));
		this.addPageRouteLoader('ticket', this.loadRoute.bind(this));
		this.addPageRouteLoader('person', this.loadRoute.bind(this));

		var self = this;
		$('#header li a[data-route]').click(function(ev) {
			ev.preventDefault();
			self.runPageRouteFromElement(this);
		});
	},

	_initWindowInterface: function() {
		var self = this;

		var menuOpener = new DeskPRO.Agent.WindowElement.MainMenuOpener();

		// Settings is a window
		$('#user_settings_link').click(function() {

			if (screen.width > 1000) var width = 1000;
			else if (screen.width > 800) var width = 800;
			else var width = 600;

			var height = 600;

			var pos_left = (screen.width - width - 30) / 2;
			var pos_top = (screen.height - height - 100) / 2;


			var win = window.open(
				BASE_URL + 'agent/settings',
				"settings_win",
				"width="+width+",height="+height+",left="+pos_left+",top="+pos_top+",toolbar=false,locationbar=false,directories=false,status=false,menubar=false,scrollbars=true,resizable=true,copyhistory=false"
			);

			// incase it was ignored
			win.resizeTo(width, height);
			win.moveTo(pos_left, pos_top);
			win.focus();
		});
	}
});