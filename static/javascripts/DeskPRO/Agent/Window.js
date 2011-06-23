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

	Extends: DeskPRO.BasicWindow,

	init: function() {

		this.routePrefixes = {};

		this.messageChanneler = null;
		this.poller = null;

		this.sections = {};
		this.openSection = null;

		this.listPage = null;
		this.pageTabStrip = null;

		this.innerLayout = null;

		this.notifier = null;

		this._alertOverlay = null;
		this._confirmOverlay = null;

		this.loadingIndicatorEl = null;
		this.loadingIndicatorCount = 0;
		this.ajaxErrorOverlay = null;
		
		this.openTicketIds = [];
		this.releaseTicketLocks_timeout = null;
		this.releaseTicketLocks = [];

		this.winStateQueue = [];
	},

	initPage: function() {
		this._initBasic();
		this._initSections();
		this._initRoutes();
		this._initWindowInterface();
		this._initLayout();

		$('#page_loading').remove();
		$('#loading_css').remove();
	},

	windowStateUpdated: function(type) {

		return;
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

	showAlert: function(msg, classname) {
		this._initAlertOverlay();
		$('#alert_overlay_msg').html(msg);

		var wrapper = this._alertOverlay.elements.wrapperOuter;
		var wrapperModel = this._alertOverlay.elements.modal;
		if (wrapper.data('added-class')) {
			wrapper.removeClass(wrapper.data('added-class'));
			wrapperModel.removeClass(wrapper.data('added-class'));
			wrapper.data('added-class', null);
		}
		if (classname) {
			wrapper.addClass(classname);
			wrapperModel.addClass(classname);
			wrapper.data('added-class', classname);
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
		console.warn('Invalid call to addListPage for %o', page);
		this.setListPage(page);
	},

	setListPage: function(page) {

		// Route a list page fragment into the proper
		// section

		var testcl = function(x) {
			return page.getMetaData('fragmentClass', '').indexOf(x) != -1;
		};
		var handler = null;
		if (testcl('.Kb')) {
			handler = this.sections['kb_section'];
		} else if (testcl('.Ticket')) {
			handler = this.sections['tickets_section'];
		} else if (testcl('.People') || testcl('.Org')) {
			handler = this.sections['people_section'];
		} else if (testcl('.AgentChat')) {
			handler = this.sections['agent_chat_section'];
		} else if (testcl('.OpenChats')) {
			handler = this.sections['chat_section'];
		} else if (testcl('.Idea')) {
			handler = this.sections['ideas_section'];
		}

		if (!handler) {
			console.error('List page fragment has no section: %s: %o', page.getMetaData('fragmentClass', ''), page);
			return;
		}

		handler.setListPageFragment(page);
	},

	getListPage: function() {
		return this.listPage;
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

		this._doAjaxLoadRoute(url, routeData, (function(data) {
			this.stopLoadingIndicator();
			var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.ListPane.Basic');

			page.setMetaData('routeUrl', url);
			if (routeData) {
				page.setMetaData('routeData', routeData);
			}

			this.setListPage(page);

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

		// Add a temporary tab to the tabstrip
		routeData.tabPlaceholderId = this.pageTabStrip.addTabPlaceholder(url, routeData);

		
		this._doAjaxLoadRoute(url, routeData, (function(data) {
				var page = this.createPageFragment(data);

				page.setMetaData('routeUrl', url);
				if (routeData) {
					page.setMetaData('routeData', routeData);
					if (routeData.tabPlaceholderId) {
						page.setMetaData('tabPlaceholderId', routeData.tabPlaceholderId);
					}
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

		// If we have a placeholder tab, it itself is a loading indicator,
		// so dont use the window one
		if (!routeData || !routeData.tabPlaceholderId) {
			this.startLoadingIndicator();
		}

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


	/**
	 * Reloads the currently selected tab if it has the proper rotueData metadata.
	 * This is usually only used for dev, reloading a tab rather than the full page,
	 * or re-clicking a link.
	 */
	reloadSelectedTab: function() {
		var tab = this.pageTabStrip.getActiveTab();
		if (!tab) return;

		var route = tab.page.meta.routeData.route;

		// Delete current page so its not just deteceted as already loaded
		this.pageTabStrip.removeTabById(tab.id);

		this.runPageRoute(route);
	},
	

	/**
	 * Get the message channeler
	 */
	getMessageChanneler: function() {
		return this.messageChanneler;
	},


	/**
	 * Dismiss a help message. This removes the help element, and sends an ajax
	 * request to the server to record the dismiss so it doesnt show again.
	 *
	 * The element must have a data-message-id attribute.
	 * 
	 * @param el
	 */
	dismissHelpMessage: function(el) {
		el = $(el);

		var messageId = el.data('message-id');

		el.remove();

		if (!messageId) {
			return;
		}

		$.ajax({
			dataType: 'json',
			url: BASE_URL + 'agent/misc/dismiss-help-message/' + escape(messageId),
			type: 'GET'
		});
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

	_globalHandleAjaxComplete: function(event, xhr, ajaxOptions) {

		var is_success = false;
		if (xhr.status && xhr.status == 200) {
			is_success = true;
		} else if (xhr.status == '0' || (xhr.statusText && xhr.statusText == 'abort')) {
			is_success = true;
		}

		if (is_success) {
			$('#network_status_indicator').addClass('active');
			$('#network_status_indicator span').html('0');
		} else {
			$('#network_status_indicator').removeClass('active');
			var spanEl = $('#network_status_indicator span');
			var num = parseInt(spanEl.html()) || 0;
			num++;

			spanEl.html(num);
		}
	},

	_globalHandleAjaxError: function(event, xhr, ajaxOptions, errorThrown) {
		
		this.stopLoadingIndicator(1000);

		// We dont care about aborts
		// This is caused when the user navigates away from a page, any running
		// ajax requests are aborted by the browser. Without this the user
		// would see the error popup briefly before the page went away
		if (xhr.status == '0' || (xhr.statusText && xhr.statusText == 'abort')) {
			return;
		}

		var data = xhr.responseText;
		try {
			data = $.parseJSON(data);
		} catch (e) {
			data = null;
		}

		if (xhr && xhr.status && xhr.status == '403') {

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
			var match = /\[\[SN:(.*?)\]\]/.exec(xhr.responseText);
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

		this.messageChanneler = new DeskPRO.MessageChanneler.AjaxChanneler(this.messageBroker, this.options.messageChanneler);
		//this.messageChanneler = new DeskPRO.MessageChanneler.AbstractChanneler(this.messageBroker, this.options.messageChanneler);
		this.messageChanneler.subscribeChannel('tickets.new-tickets');
		this.messageChanneler.subscribeChannel('tickets.new-messages');
		this.messageChanneler.subscribeChannel('tickets.updated');

		// todo check if we still need this
		this.poller = new DeskPRO.AjaxPoller.MessagePoller(this.messageBroker, {
			ajaxUrl: BASE_URL + 'agent/poller',
			interval: 5000
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
		this.addPageRouteLoader('kb_article_view', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_new', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_edit', this.loadRoute.bind(this));

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
			var overlay = new DeskPRO.UI.Overlay({
				contentMethod: 'iframe',
				iframeUrl: BASE_URL + 'agent/settings'
			});

			overlay.openOverlay();
		});

		// Global AJAX handler for errors if no error handler is attached
		$(document).ajaxError(this._globalHandleAjaxError.bind(this));
		$(document).ajaxComplete(this._globalHandleAjaxComplete.bind(this));

		// The favicon count
		this.faviconBadge = new DeskPRO.FaviconBadge({
			favicon: '#favicon'
		});

		if (this.options.faviconCount) {
			this.getMessageBroker().addMessageListener('agent.ui.badge_updated', function(data) {

				if (self.optionsfaviconCount == 'tickets') {
					if (data.sectionId != 'tickets') return;
					var count = data.count;
				} else {
					var count = 0;
					Object.each(self.sections, function(section) {
						count += section.getBadgeCount();
					});
				}

				self.faviconBadge.updateBadge(count);
			});
		}
	},

	_initSections: function() {

		var self = this;
		var first = null;
		$('#deskpro_sections [data-section-handler]').each(function() {
			var el = $(this);
			if (!el.attr('id')) {
				el.attr('id', Orb.getUniqueId('section_'));
			}

			var handlerClassName = el.data('section-handler');

			if (DeskPRO_Window.DEBUG.disableSectionHandlers) {
				if (!DeskPRO_Window.DEBUG.enableSectionHandlers || DeskPRO_Window.DEBUG.enableSectionHandlers.indexOf(handlerClassName) === -1) {
					return;
				}
			}

			if (!first) {
				first = el;
			}

			var handlerClass = Orb.getNamespacedObject(handlerClassName);
			var handler = new handlerClass();

			self.sections[el.attr('id')] = handler;

			if (!el.is('.no-click-switch')) {
				el.click(function() { self.switchToSection(el.attr('id')) });
			}
		});

		$('#deskpro_outline').delegate('[data-route]', 'click', function(ev) {
			DeskPRO_Window.runPageRouteFromElement(this);
		});

		if (first) {
			this.switchToSection(first.attr('id'));
		}
	},

	switchToSection: function(section_id) {

		console.debug('Switching to %s', section_id);

		var handler = this.sections[section_id];
		var btn = $('#' + section_id);

		// Already on
		if (btn.is('.on')) {
			return;
		}

		if (this.openSection) {
			this.openSection.fireEvent('hide');
		}

		$('#deskpro_sections li.on').removeClass('on');
		btn.addClass('on');

		$('#deskpro_outline > section.on').removeClass('on');
		$('#deskpro_list > section.on').removeClass('on');
		if (this.openSection) {
			this.openSection.fireEvent('afterhide');
		}

		handler.fireEvent('show');
		var sectionEl = handler.getSectionElement();
		if (sectionEl) {
			sectionEl.addClass('on');
		}
		var listEl = handler.getListElement();
		if (listEl) {
			listEl.addClass('on');
		}
		handler.fireEvent('aftershow');

		this.openSection = handler;
	},

	getOpenSection: function() {
		return this.openSection;
	},
	
	_initLayout: function() {

		this.layout = new DeskPRO.Agent.Layout.DeskproWindow();
		this.layout.doResize();

		this.pageTabStrip = new DeskPRO.Agent.TabStrip(
			$('#deskpro_tabstrip > ul:first'),
			new DeskPRO.Agent.TabManager('#deskpro_viewport')
		);

		this.pageTabStrip.tabManager.addEvent('addTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});
		this.pageTabStrip.tabManager.addEvent('removeTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});
	}
});