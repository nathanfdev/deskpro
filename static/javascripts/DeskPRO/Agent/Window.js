Orb.createNamespace('DeskPRO.Agent');

/**
 * The super duper Window that connects controls from all over the interface.
 *
 * Contains a shared registry (perhaps not used?), global data like display names for agents
 * and other elements, and data for things like department-to-cat maps.
 *
 * Is also responsible for "routing" and loading page fragments. The router uses strings and decides where
 * they should be loaded (and how). For example, "navpane:filters/", the first part says it'll
 * be a navpane fragment. The second part is a simple URL we can load via AJAX.
 */
DeskPRO.Agent.Window = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {

		this.DBEUG = {};
		this.options = {};

		this.routePrefixes = {};
		this.registry = {};

		this.messageBroker = null;
		this.messageChanneler = null;
		this.poller = null;

		this.pageTabStrip = null;
		this.listTabStrip = null;

		this.layout = null;
		this.innerLayout = null;

		this.notifier = null;
		this.options = {};
		
		this._alertOverlay = null;
		this._confirmOverlay = null;

		this.loadingIndicatorEl = null;
		this.loadingIndicatorCount = 0;
		this.ajaxErrorOverlay = null;
		
		this.openTicketIds = [];
		this.releaseTicketLocks_timeout = null;
		this.releaseTicketLocks = [];

		this.winStateQueue = [];

		if (options) {
			this.setOptions(options);
		}
	},

	initPage: function() {
		this.interfaceEffects = new DeskPRO.Agent.InterfaceEffects();
		this.interfaceEffects.initPage();

		this._initBasic();
		this._initRoutes();
		this._initWindowInterface();
		this._initLayout();
	},

	windowStateUpdated: function(type) {

		if (this.DEBUG.disableSaveState) return;

		this.winStateQueue.include(type);

		if (this.saveWindowState_timeout) {
			window.clearTimeout(this.saveWindowState_timeout);
		}

		this.saveWindowState_timeout = this.saveWindowState.delay(4500, this);
	},

	saveWindowState: function() {
		var data = [];

		if (this.winStateQueue.contains('tabs')) {
			Object.each(this.pageTabStrip.getTabs(), function (tab) {
				if (tab.page && tab.page.getMetaData('routeUrl') && !tab.page.noRestoreTab) {
					data.push({'name': 'tabs[]', 'value': 'page:' + tab.page.getMetaData('routeUrl')});
				}
			});
			Object.each(this.listTabStrip.getTabs(), function (tab) {
				if (tab.page && tab.page.getMetaData('routeUrl') && !tab.page.noRestoreTab) {
					data.push({'name': 'tabs[]', 'value': 'listpane:' + tab.page.getMetaData('routeUrl')});
				}
			});
		}

		this.winStateQueue = [];

		if (!data.length) {
			return;
		}

		$.ajax({
			dataType: 'json',
			url: BASE_URL + 'agent/misc/ajax-save-state',
			type: 'POST',
			data: data,
			success: function() {},
			error: function() {}
		});
	},

	//#################################################################
	//# Global registry, getters
	//#################################################################

	/**
	 * Get a value from the registry.
	 *
	 * @param {String} id The ID of the item
	 * @return mixed
	 */
	get: function(id) {
		if (this.registry[id] === undefined) {
			return null;
		}

		return this.registry[id];
	},



	/**
	 * Add or reset a value in the registry.
	 *
	 * @param {String} id The ID of the item
	 * @param mixed value The value of the item
	 */
	set: function(id, value) {
		this.registry[id] = value;
	},



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
	//# Simple UI features
	//#################################################################

	showAlert: function(msg, class) {
		this._initAlertOverlay();
		$('#alert_overlay_msg').html(msg);

		var wrapper = this._alertOverlay.elements.wrapperOuter;
		var wrapperModel = this._alertOverlay.elements.modal;
		if (wrapper.data('added-class')) {
			wrapper.removeClass(wrapper.data('added-class'));
			wrapperModel.removeClass(wrapper.data('added-class'));
			wrapper.data('added-class', null);
		}
		if (class) {
			wrapper.addClass(class);
			wrapperModel.addClass(class);
			wrapper.data('added-class', class);
		}

		this._alertOverlay.openOverlay();
	},

	showConfirm: function(msg, callback_yes, callback_no) {
		this._initConfirmOverlay();

		this._confirmOverlay_callback_yes = callback_yes || function() { };
		this._confirmOverlay_callback_no = callback_no || function() { };

		$('#confirm_overlay_msg').html(msg);
		this._confirmOverlay.openOverlay();
	},

	_initAlertOverlay: function() {
		if (this._alertOverlay !== null) return;

		this._alertOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('#alert_overlay'),
			zIndex: 10000000, /* this should be bigger than everything */
			onContentSet: function(eventData) {
				$('.close-trigger', eventData.wrapperEl).click((function() {
					eventData.overlay.closeOverlay();
				}).bind(this));
			}
		});

		// Need to init it now because showAlert will try to set a
		// class on it sometimes, and we need the elements ready
		this._alertOverlay.initOverlay();
	},

	_initConfirmOverlay: function() {
		if (this._confirmOverlay !== null) return;

		this._confirmOverlay_callback_yes = function() { };
		this._confirmOverlay_callback_no = function() { };

		var self = this;

		this._confirmOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('#confirm_overlay'),
			zIndex: 10000000, /* this should be bigger than everything */
			onContentSet: function(eventData) {
				$('.cancel-trigger', eventData.wrapperEl).click((function() {
					eventData.overlay.closeOverlay();
					self._confirmOverlay_callback_no();
					self._confirmOverlay_callback_no = function() {};
				}).bind(this));
				$('.okay-trigger', eventData.wrapperEl).click((function() {
					eventData.overlay.closeOverlay();
					self._confirmOverlay_callback_yes();
					self._confirmOverlay_callback_yes = function() {};
				}).bind(this));
			}
		});
	},


	/**
	 * Show a status message.
	 *
	 * @param message
	 * @param options
	 */
	showStatusMessage: function(message, options) {
		options = Object.merge({
			btnCallback: null,
			btnText: 'Dismiss',
			autoClose: 4500,
			extraClasses: ''
		}, options||{});

		// undoCallback for bc, use btnCallback please
		if (options.undoCallback) {
			options.btnCallback = options.undoCallback;
			delete options.undoCallback;
		}

		var wrap = $('#status_box');

		if (wrap.data('added-classes')) {
			wrap.removeClass(wrap.data('added-classes'));
			wrap.data('added-classes', null);
		}

		if (options.extraClasses) {
			wrap.addClass(options.extraClasses);
			wrap.data('added-classes', options.extraClasses);
		}

		var timeoutId = null;
		var closeFn = function() {
			wrap.fadeOut(250);
			if (timeoutId) {
				window.clearTimeout(timeoutId);
			}
		}

		if (options.autoClose) {
			timeoutId = closeFn.delay(options.autoClose);
		}

		$('#status_message').html(message);
		$('#status_dismiss_button em').html(options.btnText);

		if (options.btnCallback) {
			$('#status_dismiss_button').one('click', function(ev) {
				closeFn();
				options.btnCallback(ev, options);
			});
		} else {
			$('#status_dismiss_button').one('click', function(ev) {
				closeFn();
			});
		}

		wrap.fadeIn(300);
	},


	
	/**
	 * Shows a status message with defaults for an 'undo' type button.
	 *
	 * @param message
	 * @param callback
	 */
	showUndoMessage: function(message, callback) {
		this.showStatusMessage(message, {
			btnCallback: callback,
			btnText: 'Undo',
			extraClasses: 'undo'
		});
	},


	//#################################################################
	//# Routes and page loading
	//#################################################################

	addListPage: function(page) {
		this.listTabStrip.addTab(page);
	},

	addPageTab: function(page) {
		this.pageTabStrip.addTab(page);
	},

	/**
	 * Checks views for a specific page and removes it
	 */
	removePage: function(page) {

		var tabId = this.pageTabStrip.findTabByPage(page);
		if (tabId) {
			this.pageTabStrip.removeTabById(tabId);
		}
		tabId = false;

		tabId = this.listTabStrip.findTabByPage(page);
		if (tabId) {
			this.listTabStrip.removeTabById(tabId);
		}
	},

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

		if (!routeData.ignoreExist) {
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

		if ($('#pane_tabs li').length >= 5) {
			DeskPRO_Window.showAlert('You have too many tabs open on the right. Close one before trying to open another', 'error');
			return;
		}

		if (!routeData.ignoreExist) {
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



	_doAjaxLoadRoute: function(url, routeData, successFn) {

		if (!url) {
			console.error('No URL provided! routeData: %o', routeData);
			return;
		}

		this.startLoadingIndicator();

		if (routeData && routeData.postData) {
			$.ajax({
				dataType: 'text',
				url: url,
				type: 'POST',
				data: routeData.postData,
				success: (function(data) {
					this.stopLoadingIndicator();
					successFn(data);
				}).bind(this)
			});
		} else {
			$.ajax({
				dataType: 'text',
				url: url,
				type: 'GET',
				success: (function(data) {
					this.stopLoadingIndicator();
					successFn(data);
				}).bind(this)
			});
		}
	},



	/**
	 * This creates a PageFragment.
	 *
	 * @param {String} html The HTML page
	 * @return {DeskPRO.Agent.PageFragment.Basic}
	 */
	createPageFragment: function (html, classname) {

		pageMeta = {
			'title': false,
			'fragmentClass': classname || 'DeskPRO.Agent.PageFragment.Basic'
		};

		var regex = /<script>([\s\S]*?)<\/script>/im;
		var matches = regex.exec(html);

		if (!matches || !matches.length) {
			var regex = /<script\s*type="text\/javascript">([\s\S]*?)<\/script>/im;
			var matches = regex.exec(html);
		}

		if (matches && matches.length) {
			try {
				eval(matches[1]);
			} catch (err) {
				console.error('Page fragment JS eval error: %o', err);
			}
		}

		// Hard switch that prevents page fragments from
		// rendering a login page into the interface
		// - The login page is redirected to within the code when session expires,
		// so in the template we set this metadata to force this redirect
		if (pageMeta && pageMeta.goToLogin) {
			window.location = BASE_URL + 'agent/';

			var page = new DeskPRO.Agent.PageFragment.Basic('');
			return page;
		}

		//console.debug('PageFragment class: %s', pageMeta.fragmentClass);
		var fragment_class = Orb.getNamespacedObject(pageMeta.fragmentClass);

		var page = new fragment_class(html);
		page.setMetaData(pageMeta);

		return page;
	},



	//#################################################################
	//# AJAX and loading
	//#################################################################

	startLoadingIndicator: function(n) {
		if (!n) n = 1;
		this.loadingIndicatorCount += n;
		this._showHideLoadingIndicator();
	},
	stopLoadingIndicator: function(n) {
		if (!n) n = 1;
		this.loadingIndicatorCount -= n;
		if (this.loadingIndicatorCount < 0) this.loadingIndicatorCount = 0;
		this._showHideLoadingIndicator();
	},
	_showHideLoadingIndicator: function() {
		if (this.loadingIndicatorCount > 0) {
			if (!this.loadingIndicatorEl) {
				this.loadingIndicatorEl = $('<div class="window-loading-indicator" style="display:none" />');
				this.loadingIndicatorEl.appendTo('body');
			}

			this.loadingIndicatorEl.css({
				'position': 'absolute',
				'top': 0,
				'left': ($(document).width()/2) - this.loadingIndicatorEl.outerWidth(),
				'z-index': 100000
			});

			this.loadingIndicatorEl.slideDown(250);
		} else {
			if (this.loadingIndicatorEl) {
				this.loadingIndicatorEl.stop().slideUp(150);
			}
		}
	},

	_globalHandleAjaxError: function(event, XMLHttpRequest, ajaxOptions, errorThrown) {

		this.stopLoadingIndicator(1000);

		// We dont care about aborts
		if (XMLHttpRequest.statusText && XMLHttpRequest.statusText == 'abort') {
			return;
		}

		var data = XMLHttpRequest.responseText;
		try {
			data = $.parseJSON(data);
		} catch (e) {
			data = null;
		}

		if (XMLHttpRequest && XMLHttpRequest.status && XMLHttpRequest.status == '403') {

			if (data && data.error && data.error == 'session_expired') {
				window.location = data.redirect_login;
				ajaxOptions.error = null;
				ajaxOptions.complete = null;

				this.showStatusMessage('Your session has timed out, you must log in');

				return;
			}

			if (data && data.error && data.error == 'not_allowed') {
				this.showStatusMessage('The action you attempted to execute is not allowed:<br />' + data.errorMessage);
				return;
			}
		}

		// We dont use this handler if there was an error handler used
		if (ajaxOptions && ajaxOptions.error) return;

		var sn = null;
		if (data && data.sn) {
			sn = data.sn;
		} else {
			var match = /\[\[SN:(.*?)\]\]/.exec(XMLHttpRequest.responseText);
			if (match) {
				sn = match[1];
			}
		}

		console.log(sn);

		// Show overlay about failed
		if (sn) {
			this._showAjaxError('If the error persists, give your administrator this code: SN' + sn);
		} else {
			this._showAjaxError();
		}
	},

	_showAjaxError: function(message) {

		$('#global_ajax_error_info').empty();
		if (message) {
			$('#global_ajax_error_info').html(message);
		}

		if (!this.ajaxErrorOverlay) {
			this.ajaxErrorOverlay = new DeskPRO.UI.Overlay({
				contentElement: $('#global_ajax_error'),
				zIndex: 10000000 /* this should be bigger than everything */
			});
		}

		this.ajaxErrorOverlay.initOverlay(); // needed so we can access wrapperOuter next
		this.ajaxErrorOverlay.elements.wrapperOuter.addClass('error');

		this.ajaxErrorOverlay.openOverlay();
	},



	//#################################################################
	//# Inits
	//#################################################################

	_initBasic: function() {
		this.messageBroker = new DeskPRO.MessageBroker();

		this.messageChanneler = new DeskPRO.MessageChanneler.AjaxChanneler(this.messageBroker, this.options.messageChanneler);
		this.messageChanneler.subscribeChannel('tickets.new-tickets');
		this.messageChanneler.subscribeChannel('tickets.new-messages');
		this.messageChanneler.subscribeChannel('tickets.updated');

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

		// Set up listener for badge count
		this.getMessageBroker().addMessageListener('filters.counts', this._updatefilterCounts.bind(this));

		// When a ticket is open or closed, apply style
		this.getMessageBroker().addMessageListener('ui.ticket.opened', function (data) {
			$('table.list tr.ticket-'+data.ticketId, $('#pane_list')).addClass('open');
			self.openTicketIds.push(data.ticketId);
		});
		this.getMessageBroker().addMessageListener('ui.ticket.closed', (function (data) {
			$('table.list tr.ticket-'+data.ticketId, $('#pane_list')).removeClass('open');
			self.openTicketIds.erase(data.ticketId);

			this.releaseTicketLocks.push(data.ticketId);
			if (this.releaseTicketLocks_timeout) {
				window.clearTimeout(this.releaseTicketLocks_timeout);
			}

			this.releaseTicketLocks_timeout = (function() {

				if (!this.releaseTicketLocks.length) {
					return;
				}

				var data = [];
				Array.each(this.releaseTicketLocks, function(id) {
					data.push( {
						name: 'ticket_ids[]',
						value: id
					});
				});
				this.releaseTicketLocks = [];

				$.ajax({
					url: BASE_URL + 'agent/ticket-search/ajax-release-locks',
					type: 'GET',
					data: data,
					dataType: 'json',
					success: function(data) {

					}
				});
			}).delay(3500, this);
		}).bind(this));

		// Re-dispatch ticket messages to their specific tickets
		this.getMessageBroker().addMessageListener('tickets.new-messages', (function(data) {
			var name = 'tickets.new-messages.' + data.ticket_id;
			this.getMessageBroker().sendMessage(name, data);
		}).bind(this));
		this.getMessageBroker().addMessageListener('tickets.updated', (function(data) {
			var name = 'tickets.updated.' + data.ticket_id;
			this.getMessageBroker().sendMessage(name, data);
		}).bind(this));
	},

	runOpenTicketStateOnElement: function(el) {
		var self = this;

		$('tr', el).each(function() {
			var tr = $(this);
			var ticketId = tr.data('ticket-id');
			if (self.openTicketIds.indexOf(ticketId) !== -1) {
				tr.addClass('open');
			}
		})
	},

	_updatefilterCounts: function (counts) {
		var total = 0;
		Object.each(counts, function (count, filter_id) {
			total += count;
		});

		$('.ticket-filter-count-all').html('(' + total + ')');
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

		// Global AJAX handler for errors if no error handler is attached
		$(document).ajaxError(this._globalHandleAjaxError.bind(this));
	},
	
	_initLayout: function() {
		
		this.layout = new DeskPRO.Agent.Layout.WindowLayout();
	
		this.pageTabStrip = new DeskPRO.Agent.TabStrip(
			$('#pane_tabs'),
			new DeskPRO.Agent.TabManager('#page')
		);

		this.pageTabStrip.tabManager.addEvent('addTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});
		this.pageTabStrip.tabManager.addEvent('removeTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});

		this.listTabStrip = new DeskPRO.Agent.TabStrip(
			$('#pane_list_tabs'),
			new DeskPRO.Agent.TabManager('#pane_list')
		);

		this.listTabStrip.tabManager.addEvent('addTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});
		this.listTabStrip.tabManager.addEvent('removeTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});
	}
});