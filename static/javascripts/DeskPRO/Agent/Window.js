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

		this.winStateQueue = [];

		this.util = {
			modCountEl: function(el, op, num) {

				el = $(el);

				if (!num) num = 1;

				var count = parseInt(el.text().trim());

				if (op == '-' || op == 'rem' || op == 'del' || op == 'sub') {
					count -= num;
					if (count < 0) count = 0;
				} else {
					count += num;
				}

				el.text(count);
			},

			/**
			 * Get a "plain" article. ie of type="text/x-deskpro-plain"
			 *
			 * @param el
			 * @return {String}
			 */
			getPlainTpl: function(el) {
				var el = $(el);
				var html = el.get(0).innerHTML;

				html = html.replace(/%startScript%/g, '<script>');
				html = html.replace(/%endScript%/g, '</script>');

				return html;
			},

			showSavePuff: function(overEl) {
				var el = $('<div class="load-puff" style="display: none; opacity: 0" />');
				el.appendTo('body');

				var pos = overEl.offset();
				el.css({
					top: pos.top + 15,
					left: pos.left + overEl.width() - 4
				});

				var endPos1 = pos.top - 5;
				var endPos2 = pos.top - 15;

				el.show();
				el.animate({
					top: endPos1,
					opacity: 1
				}, 200, 'swing', function() {
					window.setTimeout(function() {
						el.animate({
							top: endPos2,
							opacity: 0
						}, 200, 'swing', function() {
							el.remove();
						});
					}, 225);
				});
			},

			slugify: function(str) {
				str = str.replace(/[^a-zA-Z0-9\-]/g, '-');
				str = str.replace(/\-{2,}/g, '-');
				str = str.replace(/^\-/, '');
				str = str.replace(/\-$/, '');

				return str;
			}
		};
	},

	initPage: function() {

		$('#dp_loading').remove();

		this._initBasic();
		this._initSections();
		this._initRoutes();
		this._initWindowInterface();
		this._initLayout();

		this._initInterfaceServices();

		$('#page_loading').remove();
		$('#loading_css').remove();

		this.fragmentRouter = window.DeskPRO_FragmentRouter;
		this.fragmentRouter.setBaseUrl(BASE_URL);

		var self = this;

		$.history.init(function(hash){
			self.loadHashPath(hash);
		},
		{ unescape: ",/:" });

		if (!this.openSection) {
			this.switchToSection($('#dp_nav [data-section-handler]').first().attr('id'));
		}

		/**
		 * After everything is init'ed we'll start our GC
		 */
		Orb.Class_GC_Start(5000);
		Orb.Class_GC_Callbacks.push(function(obj, id) {
			self.messageBroker.removeTaggedEvents(id);
		});
	},

	loadHashPath: function(browserHash) {

		if (this.DEBUG.disableUrlFragments) return;

		// This is sometimes set to prevent any of the below loading
		// to happen when the hash is updated to reflect an already-set
		// URL state
		if (this.cancelHashLaod) {
			this.cancelHashLaod = false;
			return;
		}

		// Hashes are #keyword.tabid:arg1:arg2
		// tabid part is for non-unique pages (ie newticket) and
		// a user is clicking between tabs. It is optional,
		// and ignored if the tabid doesn't exist.

		// Hash segments are separated by commas. Each segment is a different
		// page. The first segment should be the list pane, but this is enforced
		// anyway by the fragment_type in routing.yml

		var segments = browserHash.split(',');
		var activateSection = null;
		var activateTabId = null;

		Array.each(segments, function (hash, i) {

			var m;
			if (m = hash.match(/app\.([a-zA-Z]+)/)) {
				activateSection = m[1]
				return;
			}

			// Active tab has .o on it, like ticket.o:1234
			// So detect that, and then remove the .o
			var isOpen = false;
			if (hash.match(/\.o:/)) {
				isOpen = true;
				hash = hash.replace(/\.o:/, ':');
			}

			var tabId = this.pageTabStrip.findTabByFragment(hash);
			if (tabId) {
				if (!activateTabId || isOpen) {
					activateTabId = tabId;
				}
				return;
			}

			var listPage = this.getCurrentListPage();
			if (listPage && listPage.getMetaData('url_fragment') == hash) {
				return;
			}

			var parts = hash.match(/^(.*?)(\.(.*?))?:(.*?)$/);

			if (!parts) {
				// Invalid
				return;
			}

			var tabId = null;
			if (parts[3]) {
				var tabId = parts[3];
			}

			var fragmentName = parts[1];
			var args = parts[4];

			args = args.split(':');

			if (!this.fragmentRouter.hasFragment(fragmentName)) {
				return;
			}

			var url = this.fragmentRouter.getUrl(fragmentName, args);
			var type = this.fragmentRouter.getFragmentType(fragmentName);

			if (type == 'list') {
				this.loadingListFragment = hash;
				this.loadListPane(url, { url_fragment: hash });
			} else {
				this.loadingPageFragment = hash;
				this.loadPage(url, { url_fragment: hash });
			}
		}, this);

		if (activateTabId) {
			this.pageTabStrip.activateTabById(activateTabId);
		}
		if (activateSection) {
			var activateSectionId = null;
			Object.each(this.sections, function(section, id) {
				if (section.urlFragmentName && section.urlFragmentName == activateSection) {
					activateSectionId = id;
					return false;
				}
			});

			if (activateSectionId) {
				this.switchToSection(activateSectionId);
			}
		}
	},

	updateWindowUrlFragment: function() {

		if (this.DEBUG.disableUrlFragments) return;

		this.cancelHashLaod = true;

		var segments = [];

		if (this.openSection) {
			if (this.openSection.urlFragmentName) {
				segments.push('app.' + this.openSection.urlFragmentName);
			}
			if (this.openSection.listPage && this.openSection.listPage.getMetaData('url_fragment')) {
				segments.push(this.openSection.listPage.getMetaData('url_fragment'));
			}
		}

		if (this.pageTabStrip) {
			var currentTab = this.pageTabStrip.getActiveTab();

			// No current tab means there are no tabs open at all
			if (!currentTab) {
				jQuery.history.load('');
				return;
			}

			var tabs = this.pageTabStrip.getTabs();
			Object.each(tabs, function(tab, id) {
				var tabPage = tab.page;
				var hash = tabPage.getMetaData('url_fragment');

				if (hash) {
					if (tab.id == currentTab.id) {
						if (hash.indexOf(':') !== -1) {
							// ticket:123 to ticket.o:123
							hash = hash.replace(/:/, '.o:');
						} else {
							// somename to somename.o
							hash = hash + '.o';
						}
					}

					segments.push(hash);
				}
			});
		}

		var browserHash = '';
		browserHash = segments.join(',');

		jQuery.history.load(browserHash);
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
	 * Get the ID of the last client message.
	 */
	getLastClientMessageId: function() {
		if (this.messageChanneler.lastMessageId) {
			return this.messageChanneler.lastMessageId;
		}

		return 0;
	},

	forwardClientMessageData: function(data) {
		if (this.messageChanneler.handleMessageAjax) {
			this.messageChanneler.handleMessageAjax(data);
		}
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
	 * Get the tab watcher
	 */
	getTabWatcher: function() {
		return this.tabWatcher;
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
	 * Get information about an agent like their name, initials, picture URL.
	 *
	 * @param {Integer} agent_id
	 * @return {Object}
	 */
	getAgentInfo: function(agent_id) {
		// offline list always has all agents and their info
		var agentEl = $('#agent_offline_list .agent-' + agent_id);

		if (!agentEl.length) {
			console.error('Unknow agent %i', agent_id);
			return null;
		}

		return {
			id: agent_id,
			name: agentEl.data('agent-name'),
			email: agentEl.data('email'),
			shortName: agentEl.data('agent-short-name'),
			pictureUrl: agentEl.data('picture-url'),
			pictureUrlSizable: agentEl.data('picture-url-sizable')
		};
	},


	/**
	 * Get information about a team
	 *
	 * @param {Integer} agent_team_id
	 * @return {Object}
	 */
	getTeamInfo: function(team_id) {

		team_id = parseInt(team_id);

		// chat list always has teams and info
		var teamEl = $('#agent_team_list .team-' + team_id);

		if (!teamEl.length) {
			console.error('Unknow team %i', team_id);
			return null;
		}

		return {
			id: team_id,
			name: teamEl.data('team-name'),
			pictureUrl: teamEl.data('picture-url'),
			pictureUrlSizable: teamEl.data('picture-url-sizable')
		};
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

		if (typeof msg == 'string') {
			var msgEl = $('<div/>');
			msgEl.text(msg);
			msg = msgEl;
		}

		$('#alert_overlay_msg').empty().append(msg);

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

	showPrompt: function(msg, callback_ok, callback_cancel) {
		this._initPromptOverlay();

		this._promptOverlay_callback_yes = callback_ok || function() { };
		this._promptOverlay_callback_no = callback_cancel || function() { };

		$('#prompt_overlay_msg').html(msg);
		this._promptOverlay.openOverlay();
	},

	_initAlertOverlay: function() {
		if (this._alertOverlay) return;

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
		if (this._confirmOverlay) return;

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

	_initPromptOverlay: function() {
		if (this._promptOverlay) return;

		this._promptOverlay_callback_yes = function() { };
		this._promptOverlay_callback_no = function() { };

		var self = this;

		this._promptOverlay = new DeskPRO.UI.Overlay({
			contentElement: $('#prompt_overlay'),
			zIndex: 10000000, /* this should be bigger than everything */
			onContentSet: function(eventData) {
				$('.cancel-trigger', eventData.wrapperEl).click((function() {
					eventData.overlay.closeOverlay();
					self._promptOverlay_callback_no($('#prompt_overlay_input').val(), $('#prompt_overlay'));
					self._promptOverlay_callback_no = function() {};
					$('#prompt_overlay_input').val('');
				}).bind(this));
				$('.okay-trigger', eventData.wrapperEl).click((function() {
					eventData.overlay.closeOverlay();
					self._promptOverlay_callback_yes($('#prompt_overlay_input').val(), $('#prompt_overlay'));
					self._promptOverlay_callback_yes = function() {};
					$('#prompt_overlay_input').val('');
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

	setListPage: function(page, noswitch) {

		// Route a list page fragment into the proper
		// section

		var testcl = function(x) {
			return page.getMetaData('fragmentClass', '').indexOf(x) != -1;
		};
		var handler = null;
		if (testcl('.Kb') || testcl('.News') || testcl('.Download') || testcl('.Publish')) {
			handler = this.sections['publish_section'];
		} else if (testcl('.Ticket') || testcl('.NewCustomFilter')) {
			handler = this.sections['tickets_section'];
		} else if (testcl('.People') || testcl('.Org')) {
			handler = this.sections['people_section'];
		} else if (testcl('.AgentChat')) {
			handler = this.sections['agent_chat_section'];
		} else if (testcl('.OpenChats')) {
			handler = this.sections['chat_section'];
		} else if (testcl('.Idea')) {
			handler = this.sections['ideas_section'];
		} else if (testcl('.TicketFilter') || testcl('.RecycleBin')) {
			handler = this.sections['tickets_section'];
		}else if (testcl('.Task')) {
			handler = this.sections['tasks_section'];
		}

		if (!handler && this.DEBUG.useTestSection) {
			handler = this.sections['test_section'];
		}

		if (!handler) {
			console.error('List page fragment has no section: %s: %o', page.getMetaData('fragmentClass', ''), page);
			return;
		}

		handler.setListPageFragment(page, noswitch);
		this.listPage = page;

		if (!noswitch) {
			this.updateWindowUrlFragment();
		}
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
	runPageRoute: function(route, extraData) {
		var found_listener = false;

		var data = this.parseRoute(route);
		if (extraData) {
			data = Object.merge(extraData, data);
		}

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
	 * Parse a route into its parts
	 *
	 * @param {String} route
	 * @return {Object}
	 */
	parseRoute: function(route) {
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

		return data;
	},



	/**
	 * Loads a route attached to an element. Useful for quickly assigning click events.
	 *
	 * @param {jQuery} el The element to inspect for a route
	 */
	runPageRouteFromElement: function(el) {

		el = $(el);

		if (el.is('.cancel-route')) {
			return;
		}

		if (!el.data('route')) {
			console.warn('Element has no route: %o', el);
			return;
		}

		var extraData = {};
		extraData.routeTriggerEl = el;
		if (el.data('route-title')) {
			extraData.title = el.data('route-title');
			if (extraData.title == '@text') {
				extraData.title = el.text().trim().replace(/[\n\r]/g, ' ').replace(/\s+/g, ' ');
			} else if (extraData.title == '@title') {
				extraData.title = el.attr('title');
			} else if (extraData.title.test(/^@selector\((.*?)\)$/)) {
				var sel = extraData.title.match(/^@selector\((.*?)\)$/)[1];
				var titleEl = null;
				if (sel[0] == "#") {
					titleEl = $(sel);
				} else {
					titleEl = $(sel, el);
				}

				if (titleEl && titleEl.length) {
					extraData.title = titleEl.text().trim().replace(/[\n\r]/g, ' ').replace(/\s+/g, ' ');
				} else {
					delete extraData.title;
				}
			}
		}
		if (el.data('route-openclass')) {
			extraData.toggleOpenClass = el.data('route-openclass');
		}

		this.runPageRoute(el.data('route'), extraData);
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

		if (routeData && !routeData.isBackgroundLoad) {
			if (this.loadingListPage) {
				this.loadingListPage.abort();
				this.loadingListPage = null;
			}
		}

		if (routeData && !routeData.isBackgroundLoad) {
			$('#dp_list > section').removeClass('on');
			$('#dp_list_loading').addClass('on');
		}

		var xhr = this._doAjaxLoadRoute(url, routeData, (function(data) {

			if (routeData && !routeData.isBackgroundLoad) {
				this.loadingListPage = null;
			}

			$('#dp_list_loading').removeClass('on');

			var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.ListPane.Basic');

			page.setMetaData('routeUrl', url);
			if (routeData) {
				page.setMetaData('routeData', routeData);
			}

			this.setListPage(page, routeData.isBackgroundLoad || false);

			if (callback) callback(page);
		}).bind(this));

		if (routeData && !routeData.isBackgroundLoad) {
			this.loadingListPage = xhr;
		}
	},



	/**
	 * Load a URL and treat and put it into the tabbed pane.
	 *
	 * @param {String} url The URL of the page
	 */
	loadPage: function(url, routeData, callback) {

		if (!routeData || (!routeData.ignoreExist)) {
			var existTab = this.pageTabStrip.getTabByRouteUrl(url);
			if (existTab && !(existTab.page.allowDupe && existTab.page.TYPENAME != 'loading')) {
				this.pageTabStrip.removeTabById(existTab.id);
				if (routeData.routeTriggerEl && routeData.toggleOpenClass) {
					routeData.routeTriggerEl.removeClass(routeData.toggleOpenClass);
				}
				return;
			}
		}

		// Add a temporary tab to the tabstrip
		routeData.tabPlaceholderId = this.pageTabStrip.addTabPlaceholder(url, routeData);

		if (routeData.routeTriggerEl && routeData.toggleOpenClass) {
			routeData.routeTriggerEl.addClass(routeData.toggleOpenClass);
		}

		this._doAjaxLoadRoute(url, routeData, (function(data) {
			var page = this.createPageFragment(data);

			page.setMetaData('routeUrl', url);
			if (routeData) {
				page.setMetaData('routeData', routeData);
				if (routeData.tabPlaceholderId) {
					page.setMetaData('tabPlaceholderId', routeData.tabPlaceholderId);
				}
			}
			if (routeData.fragment) {
				page.setMetaData('fragment', routeData.fragment);
			}

			this.addPageTab(page);

			if (callback) callback(page);
		}).bind(this)
		);
	},



	_doAjaxLoadRoute: function(url, routeData, successFn) {

		routeData = routeData || {};
		if (!url) {
			console.error('No URL provided! routeData: %o', routeData);
			return;
		}

		var self = this;
		if (routeData.tabPlaceholderId) {
			var errorFn = function() { self.pageTabStrip.removeTabById(routeData.tabPlaceholderId); };
		} else {
			var errorFn = function() {};
		}

		if (routeData && routeData.postData) {
			var xhr = $.ajax({
				dataType: 'text',
				url: url,
				type: 'POST',
				data: routeData.postData,
				success: (function(data) {
					successFn(data);
				}).bind(this),
				error: errorFn,
				noErrorOverride: true
			});

			routeData.xhr = xhr;
		} else {
			var xhr = $.ajax({
				dataType: 'text',
				url: url,
				type: 'GET',
				success: (function(data) {
					successFn(data);
				}).bind(this),
				error: errorFn,
				noErrorOverride: true
			});

			routeData.xhr = xhr;
		}

		return xhr;
	},



	/**
	 * This creates a PageFragment.
	 *
	 * @param {String} html The HTML page
	 * @return {DeskPRO.Agent.PageFragment.Basic}
	 */
	createPageFragment: function (html, classname, force_classname) {

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

			eval(matches[1]);

			// Cut out the pageMeta from the HTML string
			html = html.replace(matches[0], '');
		}

		if (force_classname) {
			pageMeta.fragmentClass = classname;
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


	getCurrentListPage: function() {
		return this.listPage;
	},

	getCurrentTabPage: function() {
		var tab = this.pageTabStrip.getActiveTab();
		if (!tab) return null;

		return tab.page;
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


	/**
	 * Plays a sound through HTML5 audio element.
	 *
	 * @param files A file or array of file sources (MP3 and OGG for best cross-browser)
	 * @param options
	 * @return jQuery
	 */
	playSound: function(files, setOptions) {

		setOptions = setOptions || {};

		options = $.extend({}, {
			'autoplay': true,
			'volume': false,
			'loop': false,
			'destroyAfter': true
		}, setOptions);

		if (this.volume == 0) {
			return null;
		}

		if (typeof files == 'string') {
			files = [files];
		}

		var volume = this.volume;
		if (options.volume) {
			volume = options.volume;
		}

		volume = volume + 0.0;

		var html = [];
		html.push('<audio ');
		if (volume != 1) {
			html.push(' volume="' + volume + '" ');
		}
		if (options.loop) {
			html.push(' loop="loop" ');
		}
		html.push('>');

		Array.each(files, function(f) {
			html.push('<source src="' + f + '" />');
		});

		html.push('</audio>');
		html = html.join('');

		var el = $(html);
		el.get(0).volume = volume;

		if (options.destroyAfter) {
			el.bind('ended', function() {
				$(this).remove();
			});
		}

		if (options.appendTo) {
			$(options.appendTo).append(el);
		} else {
			el.appendTo('body');
		}

		if (options.autoplay) {
			el.get(0).play();
		}

		return el;
	},


	/**
	 * Plays a standard sound from the static dir. This assumes an MP3
	 * and OGG version of the file exists.
	 *
	 * @param name
	 * @param options
	 */
	playLibrarySound: function(name, options) {
		var files = [
			ASSETS_BASE_URL + '/sounds/' + name + '.mp3',
			ASSETS_BASE_URL + '/sounds/' + name + '.ogg'
		];

		this.playSound(files, options);
	},

	handleSoundElements: function(el) {
		var self = this;
		if ($(el).is('[data-play-sound]')) {
			self.playLibrarySound($(el).data('play-sound'), {appendTo: el});
		} else {
			$('[data-play-sound]', el).each(function() {
				self.playLibrarySound($(this).data('play-sound'), {appendTo: el});
			});
		}
	},

	//#################################################################
	//# AJAX and loading
	//#################################################################

	_globalHandleAjaxComplete: function(event, xhr, ajaxOptions) {

		var is_success = false;
		if (xhr.status && xhr.status == 200) {
			is_success = true;
		} else if (xhr.status == '0' || (xhr.statusText && xhr.statusText == 'abort')) {
			is_success = true;
		}

		// Only polling-type requests really dictate the "network" status
		if (ajaxOptions && ajaxOptions.dpIsPolling) {
			if (is_success) {
				$('#network_status_indicator').addClass('active');
				$('#network_status_indicator span').html('0');
				$('#network_status_tip').removeClass('error');
			} else {
				$('#network_status_indicator').removeClass('active');
				var spanEl = $('#network_status_indicator span');
				var num = parseInt(spanEl.html()) || 0;
				num++;

				spanEl.html(num);

				$('#network_status_tip').addClass('error');
			}
		}
	},

	_globalHandleAjaxError: function(event, xhr, ajaxOptions, errorThrown) {

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
				var url = data.redirect_login;
				url += '?return=' + encodeURIComponent(window.location.href);

				window.location = url;
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
		if (ajaxOptions && ajaxOptions.error && !ajaxOptions.noErrorOverride) return;

		// We dont show the error popup if it was just an error with polling
		if (ajaxOptions && ajaxOptions.dpIsPolling) return;

		// We dont know if the request was JSON or HTML,
		// so we have to sniff the raw responseText to see about any embedded SN code
		var sn = null;
		if (data && data.sn) {
			sn = data.sn;
		} else {
			var match = /\[SN([0-9A-Z]{8})\]/.exec(xhr.responseText);
			if (match) {
				sn = match[1];
			}
		}

		console.log(sn);

		// Show overlay about failed
		if (sn) {
			var showsn = 'SN' + sn;
			if (DESKPRO_PERSON_ISADMIN) {
				showsn = '<a href="' + BASE_URL + 'admin/logs/sn/SN' + sn + '">SN' + sn + '</a>';
			}

			this._showAjaxError('<div>If the error persists, give your administrator this code: ' + showsn + '</div>');
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
		this.messageChanneler.subscribeChannel('agent-notification');
		this.messageChanneler.subscribeChannel('agent-notify');
		this.messageChanneler.subscribeChannel('agent-notify.tickets');

		// todo check if we still need this
		this.poller = new DeskPRO.AjaxPoller.MessagePoller(this.messageBroker, {
			ajaxUrl: BASE_URL + 'agent/poller',
			interval: 5000
		});
	},

	_initRoutes: function() {
		// Set ourselves up as the first route listener
		this.addPageRouteLoader('listpane', this.loadRoute.bind(this));
		this.addPageRouteLoader('page', this.loadRoute.bind(this));
		this.addPageRouteLoader('ticket', (function(routeData) {

			routeData.forTypename = 'ticket';

			var m = routeData.url.match(/tickets\/([0-9]+)/);
			var ticketId = m[1];

			routeData.tabLoad = function() {
				DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.opened', { ticketId: ticketId });
			};
			routeData.tabUnload = function() {
				DeskPRO_Window.getMessageBroker().sendMessage('ui.ticket.closed', { ticketId: ticketId });
			};
			this.loadRoute(routeData);
		}).bind(this));
		this.addPageRouteLoader('person', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_view', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_new', this.loadRoute.bind(this));
		this.addPageRouteLoader('kb_article_edit', this.loadRoute.bind(this));

		var self = this;
	},

	_initWindowInterface: function() {
		var self = this;

		this.notifications = new DeskPRO.Agent.Notifications();
		this.keyboardShortcuts = new DeskPRO.Agent.KeyboardShortcuts();

		// Settings is a window
		$('#user_settings_link').click(function() {
			$('#settingswin').trigger('dp_open');
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

		this.volume = 0.8;

		var updateVolumeUi = function() {
			if (self.volume == 0 || self.volume == 0.0) {
				self.volume = 0;
				$('#sound_icon').addClass('off');
				$('#sound_icon_in').addClass('off');
			} else {
				$('#sound_icon').removeClass('off');
				$('#sound_icon_in').removeClass('off');
			}

			$('audio').each(function() {
				this.volume = self.volume;
			});

			$('#volume_controls .slider').slider('value', self.volume * 100);
		}

		// Volume slider
		$('#volume_controls .slider').slider({
			orientation: "vertical",
			range: "min",
			min: 0,
			max: 100,
			value: 80,
			slide: function(event, ui) {
				self.volume = parseInt(ui.value) / 100;
				$('#sound_icon_in').data('last-value', $('#volume_controls .slider').slider('value'));
				updateVolumeUi();
			}
		});

		$('#sound_icon_in').data('last-value', $('#volume_controls .slider').slider('value'));

		$('#sound_icon_in').click(function(ev) {
			ev.stopPropagation();
			if ($(this).is('.off')) {
				var last = $(this).data('last-value');
				if (last == 0 || last == 0.0) {
					last = 80;
				}
				self.volume = parseInt(last) / 100;
				updateVolumeUi();
			} else {
				self.volume = 0;
				updateVolumeUi();
			}
		});

		var closeSoundMenu = function() {
			$('#volume_controls_back').hide();
			$('#volume_controls').fadeOut();
		};

		// Use of a backdrop here ensures we can handle the click and not
		// fire anything else by accident on bubbling
		// Also the document click is unreliable since there may be other
		// elements that also stop bubbling.
		$('#volume_controls_back').click(function(ev) {
			ev.stopPropagation();
			closeSoundMenu();
		});

		var showSoundMenu = function() {
			$('#volume_controls_back').show();
			$('#volume_controls').css({
				'top': 30,
				'left': $('#sound_icon').offset().left - 9
			});

			$('#volume_controls').fadeIn();
		};

		$('#sound_icon').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			showSoundMenu();
		});

		// Create menu
		this.createMenu = new DeskPRO.UI.Menu({
			triggerElement: '#create_content_trigger',
			menuElement: '#create_content_menu'
		});

		this.newTicketLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/tickets/new',
			tabRoute: 'page:' + BASE_URL + 'agent/tickets/new'
		});
		this.newPersonLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/people/new',
			tabRoute: 'page:' + BASE_URL + 'agent/people/new'
		});
		this.newOrganizationLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/organizations/new',
			tabRoute: 'page:' + BASE_URL + 'agent/organizations/new'
		});
		this.newArticleLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/kb/article/new',
			tabRoute: 'page:' + BASE_URL + 'agent/kb/article/new'
		});
		this.newNewsLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/news/new',
			tabRoute: 'page:' + BASE_URL + 'agent/news/new'
		});
		this.newDownloadLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/downloads/new',
			tabRoute: 'page:' + BASE_URL + 'agent/news/new'
		});
		this.newIdeaLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/ideas/new',
			tabRoute: 'page:' + BASE_URL + 'agent/ideas/new'
		});
                this.newTaskLoader = new DeskPRO.Agent.Widget.BackgroundPopout({
			loadUrl: BASE_URL + 'agent/tasks/new',
			tabRoute: 'page:' + BASE_URL + 'agent/tasks/new'
		});

		$('#create_ticket_btn').click(function() {
			DeskPRO_Window.newTicketLoader.toggle();
		});
		$('#create_person_btn').click(function() {
			DeskPRO_Window.newPersonLoader.toggle();
		});
		$('#create_organization_btn').click(function() {
			DeskPRO_Window.newOrganizationLoader.toggle();
		});
		$('#create_article_btn').click(function() {
			DeskPRO_Window.newArticleLoader.toggle();
		});
		$('#create_news_btn').click(function() {
			DeskPRO_Window.newNewsLoader.toggle();
		});
		$('#create_download_btn').click(function() {
			DeskPRO_Window.newDownloadLoader.toggle();
		});
		$('#create_idea_btn').click(function() {
			DeskPRO_Window.newIdeaLoader.toggle();
		});
                $('#create_task_btn').click(function() {
                        $('form#newTaskForm input, form#newTaskForm select').val('');
                        DeskPRO_Window.newTaskLoader.toggle();
                });

		this.omnisearch = new DeskPRO.Agent.OmniSearchBox();

		// Create toggle
		$('#createContentTrigger').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var pos = $(this).offset();
			var w = $(this).outerWidth();
			var h= $(this).outerHeight();

			var list = $('#createTicketToggle');
			list.hide().detach().appendTo('body');
			list.css({
				top: pos.top,
				left: pos.left
			});
			list.show();

			var backdrop = $('<div class="backdrop" />').appendTo('body');

			var close = function() {
				list.hide();
				backdrop.remove();
			};
			backdrop.click(close);
			list.click(close);
		});


		// Interface toggle
		$('#DP-InterfaceSwitcher > .DP-adminSwitch > .adminSwitcher').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var pos = $(this).offset();
			var w = $(this).outerWidth();
			var h= $(this).outerHeight();

			var list = $('#interfacesToggle');
			list.hide().detach().appendTo('body');
			list.css({
				top: pos.top,
				left: pos.left
			});
			list.show();

			var backdrop = $('<div class="backdrop" />').appendTo('body');

			var close = function() {
				list.hide();
				backdrop.remove();
			};
			backdrop.click(close);
			list.click(close);
			$('ul', list).click(function(ev) {
				ev.stopPropagation();
			});
		});

		// Status
		$('#agent_status_trigger').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var pos = $(this).offset();
			var w = $(this).outerWidth();
			var h= $(this).outerHeight();

			var list = $('#agent_status_menu');
			list.hide().detach().appendTo('body');
			list.css({
				top: pos.top,
				left: pos.left
			});
			list.show();

			var backdrop = $('<div class="backdrop" />').appendTo('body');

			var close = function() {
				list.hide();
				backdrop.remove();
			};
			backdrop.click(close);
			list.click(close);
			$('#agent_status_away_overlay').click(close);
		});

		$('#agent_status_menu .status_go_available').click(function() {
			self.toggleAgentStatus('available');
		});
		$('#agent_status_menu .status_go_away').click(function() {
			self.toggleAgentStatus('away');
		});
		$('#agent_status_menu .status_go_dnd').click(function() {
			self.toggleAgentStatus('dnd');
		});

		$('#agent_status').data('status', 'available');
	},

	toggleAgentStatus: function(status) {
		var statusEl = $('#agent_status');

		if (statusEl.data('status') == status) {
			return;
		}
		statusEl.data('status', status);

		$('#agent_status_away_overlay').remove();

		if (status == 'available') {
			statusEl.removeClass('away').removeClass('dnd');

			$.ajax({
				url: BASE_URL + 'agent/misc/set-agent-status/available',
				type: 'GET'
			});

		} else if (status == 'away') {
			var overlayEl = $('<div id="agent_status_away_overlay" />').appendTo('body');

			statusEl.addClass('away').removeClass('dnd');

			$.ajax({
				url: BASE_URL + 'agent/misc/set-agent-status/away',
				type: 'GET'
			});
		} else if (status == 'dnd') {
			statusEl.addClass('dnd').removeClass('away');

			$.ajax({
				url: BASE_URL + 'agent/misc/set-agent-status/away',
				type: 'GET'
			});
		}
	},

	_initSections: function() {

		var self = this;
		var count = -1;

		var secttimeout = 2500;

		$('#dp_nav [data-section-handler]').each(function() {
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

			var handlerClass = Orb.getNamespacedObject(handlerClassName);
			var handler = new handlerClass();

			if (++count) {
				handler.addEvent('sectionInit', function() {
					window.setTimeout(function() { handler._loadAutoLoadRoutes(true); }, secttimeout);
					secttimeout += (400 * count);
				});
			} else {
				// First one, load it for real
				handler.addEvent('sectionInit', function() {
					handler._loadAutoLoadRoutes();
				});
			}

			self.sections[el.attr('id')] = handler;

			if (!el.is('.no-click-switch')) {
				el.click(function() { self.switchToSection(el.attr('id')) });
			}
		});
	},

	switchToSection: function(section_id, no_load_list) {

		console.debug('Switching to %s', section_id);

		var handler = this.sections[section_id];
		if (!handler) {
			console.error('Invalid section: %s', section_id);
			return;
		}
		var btn = $('#' + section_id);

		// Already on
		if (btn.is('.on')) {
			return;
		}

		if (this.openSection) {
			this.openSection.fireEvent('hide');
		}

		$('#dp_nav li.dpNavActive').removeClass('dpNavActive');
		btn.addClass('dpNavActive');

		$('#dp_source > section.on').removeClass('on');
		$('#dp_list > section.on').removeClass('on');

		$('#dp_list_loading, #dp_source_loading').addClass('on');

		if (this.openSection) {
			this.openSection.fireEvent('afterhide');
		}

		$('#dp_list_loading').removeClass('on');

		handler.fireEvent('show', [no_load_list]);
		var sectionEl = handler.getSectionElement();
		if (sectionEl) {
			sectionEl.addClass('on');
		}
		var listEl = handler.getListElement();
		if (listEl) {
			listEl.addClass('on');
		}
		handler.fireEvent('aftershow', [no_load_list]);

		this.openSection = handler;

		this.updateWindowUrlFragment();
	},

	getOpenSection: function() {
		return this.openSection;
	},

	_initLayout: function() {

		this.layout = new DeskPRO.Agent.Layout.DeskproWindow();
		this.layout.doResize();

		this.pageTabStrip = new DeskPRO.Agent.TabStrip(
			$('#tabNavigationPane > .deskproTabList > ul'),
			new DeskPRO.Agent.TabManager('#dp_content_wrap')
		);
		this.tabManager = this.pageTabStrip.tabManager;

		this.tabWatcher = new DeskPRO.Agent.TabWatcher({
			tabManager: this.tabManager
		});

		this.tabWatcher.addTabTypeWatcher('ticket', new DeskPRO.Agent.WindowElement.TabWatcher.Tickets());

		this.pageTabStrip.tabManager.addEvent('addTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});
		this.pageTabStrip.tabManager.addEvent('removeTab', function() { DeskPRO_Window.windowStateUpdated('tabs');	});
	},

	_initInterfaceServices: function() {
		var self = this;

		this.popover_inited = {};

		this.initInterfaceLayerEvents(document);
	},

	_initInterfacePopover: function(el, opennow) {
		var self = this;
		var popover_inited = this.popover_inited;

		var route = el.data('route');
		var routeData = self.parseRoute(route);
		var popover;

		if (!popover_inited[route]) {

			popover = new DeskPRO.Agent.PageHelper.Popover({
				pageUrl: routeData.url,
				tabRoute: route,
				loadTimeout: (el.is('.preload') ? 1500 : 0)
			});

			popover_inited[route] = {
				count: 0,
				popover: popover
			};

			popover.addEvent('close', function() {
				if (popover_inited[route].count < 1) {
					popover_inited[route].popover.destroy();
					delete popover_inited[route];
				}
			});

			popover.addEvent('destroy', function() {
				delete popover_inited[route];
			});
		} else {
			popover = popover_inited[route].popover;
		}

		popover_inited[route].count++;

		var tabWrapper = el.closest('.with-page-fragment');
		if (tabWrapper.length) {
			var page = tabWrapper.data('page-fragment');
			page.addEvent('destroy', function() {
				if (popover_inited[route]) {
					popover_inited[route].count--;
					if (popover_inited[route].count < 1) {
						popover_inited[route].popover.destroy();
						delete popover_inited[route];
					}
				}
			});
		} else {
			popover.options.destroyOnClose = true;
		}

		return popover;
	},

	/**
	 * Attaches central handlers on a layer. These handlers are added to the document,
	 * but if you have a new layer that prevents propagation up to the document,
	 * then you'll need to init it as a new layer with its own handlers.
	 *
	 * @param context
	 */
	initInterfaceLayerEvents: function(context) {
		var self = this;

		window.setTimeout(function() {
			// Accept clicks on routes
			$(context).delegate('[data-route]', 'click', function(ev) {
				if ($(this).is('.as-popover')) {
					return;
				}

				ev.stopPropagation();

				if ($(this).is('.row-item') && (!$(ev.target).is('.click-through') && $(ev.target).is('input, a, button, textarea'))) {
					return;
				}

				ev.preventDefault();

				self.runPageRouteFromElement($(this));
			});
		}, 120);

		// Accept clicks on popovers
		// Keeps track of which tabs have them open so they can be reused
		window.setTimeout(function() {
			$(context).delegate('.as-popover', 'click', function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				self._initInterfacePopover($(this)).toggle();
			});
		}, 60);

		window.setTimeout(function() {
			$(context).delegate('.person-tip', 'mouseover', function() {
				var el = $(this);
				var tipUrl = BASE_URL + 'agent/person/' + el.data('person-id') + '/tip';
				el.addClass('tipped');
				el.attr('data-tipped', tipUrl);
				el.attr('data-tipped-options', 'ajax:true, showOn: "click", hideOn: { element: "target", event: "click" }, hideOnClickOutside: true ');

				el.click(function(ev) {
					Tipped.toggle(this);
				});

				if (el.is('.with-route')) {
					el.addClass('cancel-route')
				}
				if (el.parent().is('.with-route')) {
					el.parent().addClass('cancel-route')
				}
			});
		}, 70);

		window.setTimeout(function() {
			$(context).delegate('.tipped', 'mouseover', function() {
				if ($(this).is('.tipped-inited')) {
					return;
				}
				var options = {};
				if ($(this).data('tipped-options')) {
					eval('options = {' + $(this).data('tipped-options') + '}');
				}

				Tipped.create(this, $(this).data('tipped') || $(this).attr('title'), options);
				$(this).addClass('tipped-inited');
			});
		}, 80);

		window.setTimeout(function() {
			DeskPRO.ElementHandler_Exec(context);
		}, 5);

		window.setTimeout(function() {
			$('.timeago', context).timeago();
		}, 10);
	},

	initInterfaceServices: function(context) {
		var self = this;
		var page = false;

		if (context.is('.with-page-fragment')) {
			page = context.data('page-fragment');
		} else {
			var tabWrapper = context.closest('.with-page-fragment');
			page = tabWrapper.data('page-fragment');
		}

		$('.as-popover.preload', context).each(function() {
			var p = self._initInterfacePopover($(this));
		});

		if (page) {
			var scrollEl = $('.with-scrollbar', context).first();
			if (scrollEl.length) {
				this.scrollerHandler = new DeskPRO.Agent.ScrollerHandler(page, scrollEl, {
					showEvent: 'show',
					hideEvent: 'hide'
				});
			}
		}

		window.setTimeout(function() {
			DeskPRO.ElementHandler_Exec(context);
		}, 5);

		window.setTimeout(function() {
			$('.timeago', context).timeago();
		}, 10);
	}
});

// TODO get rid of this from the designer and replace with proper opener
function toggle_visibility(elId, parentId) {
	var el = $('#' + elId);
	if (el.is(':visible')) {
		el.hide();
	} else {
		if (!el.is('.has-init')) {
			el.addClass('has-init');

			if (parentId) {
				var trigger = $('#' + parentId);
				var pos = trigger.offset();
				el.detach().appendTo('body');

				el.css({
					'position': 'absolute',
					'top': pos.top,
					'left': pos.left,
					'width': 200,
					'height': 'auto',
					'z-index': 10001
				});
			}
		}

		var back = $('<div class="backdrop" />');
		back.css('z-index', 10000);
		back.appendTo('body');

		back.click(function() {
			el.hide();
			back.remove();
		});

		el.show();
	}
}
