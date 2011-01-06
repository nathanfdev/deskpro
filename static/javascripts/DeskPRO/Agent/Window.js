Orb.createNamespace('DeskPRO.Agent');

/**
 * The super duper Window that connects controls from all over the interface.
 *
 * Contains "shells": A shell is a major part of the interface. Right now, and perhaps
 * always, we only have the three pane interface. But it is possible to for example,
 * completely hide the three-panes and show a totally new layout. A super-tab if you will.
 *
 * Contains a registry for global app data.
 *
 * Is responsible for "routing" and loading data. The router uses strings and decides where
 * they should be loaded (and how). For example, "navpane:queues/", the first part says it'll
 * be a navpane fragment. The second part is a simple URL we can load via AJAX.
 */
DeskPRO.Agent.Window = new Class({
	
	shells: {},
	routePrefixes: {},
	registry: {},
	
	messageBroker: null,
	poller: null,

	initialize: function() {	
		
		this._initBasic();
		this._initLayout();
		this._initRoutes();
		this._initWindowInterface();

	},
	
	
	
	_initBasic: function() {
		this.messageBroker = new DeskPRO.MessageBroker();
		this.poller = new DeskPRO.AjaxPoller.MessagePoller(this.messageBroker, {
			ajaxUrl: BASE_URL + 'agent/poller'
		});
		
		// Set up listener for badge count
		this.getMessageBroker().addMessageListener('queues.counts', this._updatequeueCounts.bind(this));
	},
	
	_updatequeueCounts: function (counts) {
		var total = 0;
		Object.each(counts, function (count, queue_id) {
			total += count;
		});
		
		$('.ticket-queue-count-all').html('(' + total + ')');
	},
	
	_initLayout: function() {
		$('body').layout({
			north: {
				paneSelector: '#window_head',
				spacing_open: 0,
				spacing_closed: 0,
				size: 52
			},
			center: {
				paneSelector: '#pane_shell'
			}
		});
		
		var pane = new DeskPRO.Agent.Shells.ThreePaned();
		this.addShell('paned', pane);
	},
	
	_initRoutes: function() {
		// Set ourselves up as the first route listener
		this.addPageRouteLoader('navpane', this.loadRoute.bind(this));
		this.addPageRouteLoader('listpane', this.loadRoute.bind(this));
		this.addPageRouteLoader('ticket', this.loadRoute.bind(this));
		this.addPageRouteLoader('person', this.loadRoute.bind(this));
		
		$('#window_header_nav li[data-route]').click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},
	
	_initWindowInterface: function() {
		// Set up tabs
		$('#window_head_top ul.header-tabs li').click(function() {
			$('#window_head_top ul.header-tabs li').removeClass('on');
			$(this).addClass('on');
			
			$('#window_header_nav .group.on').removeClass('on');
			$('#window_header_nav .group.' + $(this).data('tab-name')).addClass('on');
		});
		
		// Set up create menu
		var menu = new DeskPRO.UI.Menu({
			triggerElement: $('#create_new_menu_trigger'),
			menuElement: $('#create_new_menu'),
			onItemClicked: function(info) {
				DeskPRO_Window.runPageRouteFromElement(info.itemEl);
			}
		});
		
		// Settings is a window
		$('#window_head_top .settings-link').click(function() {
			
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
	
	
	
	//#################################################################
	//# Getters
	//#################################################################
	
	getMessageBroker: function() {
		return this.messageBroker;
	},
	
	getPoller: function() {
		return this.poller;
	},
	
	addShell: function(id, shell) {
		this.shells[id] = shell;
	},
	
	getShell: function(id) {
		return this.shells[id];
	},
	
	getPanedShell: function() {
		return this.getShell('paned');
	},
	
	
	
	//#################################################################
	//# AJAX and loading
	//#################################################################
	
	loadingIndicatorEl: null,
	loadingIndicatorCount: 0,
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
	
	_globalHandleAjaxError: function(event, XMLHttpRequest, ajaxOptions, thrownError) {
		
		// We dont use this handler if there was an error handler used
		if (ajaxOptions && ajaxOptions.error) return;
		
		// Hide the loading indicator
		this.stopLoadingIndicator(10000);
		
		// Show overlay about failed
		this._showAjaxError();
	},
	
	ajaxErrorOverlay: null,
	_showAjaxError: function() {
	
		if (!this.ajaxErrorOverlay) {
			this.ajaxErrorOverlay = new DeskPRO.UI.Overlay({
				contentElement: $('#global_ajax_error'),
				zIndex: 10000000, /* this should be bigger than everything */
				onContentSet: function(eventData) {
					$('.close-trigger', eventData.wrapperEl).click((function() {
						eventData.overlay.closeOverlay();
					}).bind(this));
				}
			});
		}
		
		this.ajaxErrorOverlay.openOverlay();
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
	 * @param {String} route The route to match, like navpane:tickets:queues
	 */
	runPageRoute: function(route) {
		
		// Like:
		// master.masterTag:sectioninfo:moreinfo:url/here/at/end
		// (There might not be any sectioninfo)
		// Example:
		// listpane:/agent/ticket-search/queue/123

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
		if (routeData.openInSection == 'listpane' && this.getPanedShell().innerLayout.state.west.isClosed) {
			routeData.openInSection = 'page';
			
			// If it's an alt page, they're used to link views
			// But we don't want to open a new tab automatically
			// if we're using tabbed mode
			if (routeData.masterTag == 'alt') {
				return;
			}
		}
		
		switch (routeData.openInSection) {
			case 'navpane':
				this.loadNavPane(routeData.url, routeData);
				break;
				
			case 'listpane':
				this.loadListPane(routeData.url, routeData);
				break;
			
			default:
				// Check if its already laoded
				var tabs = DeskPRO_Window.getPanedShell().tabManager.getTabs();
				var already_loaded = false;
				Object.each(tabs, function(tab, tab_id) {
					if (tab.page.getMetaData('routeUrl') == routeData.url) {
						DeskPRO_Window.getPanedShell().tabManager.activateTab(tab_id);
						already_loaded = true;
						return false;
					}
				});
				
				if (already_loaded) return;
			
				this.loadPage(routeData.url, routeData);
				break;
		}
	},
	
	
	
	/**
	 * Load a URL and treat it as a nav pane.
	 *
	 * @param {String} url The URL of the nav pane
	 */
	loadNavPane: function(url, routeData) {
		this.startLoadingIndicator();
		$.ajax({
			dataType: 'text',
			url: url,
			success: (function(data) {
				this.stopLoadingIndicator();
				var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.NavPane.Basic');
				
				page.setMetaData('routeUrl', url);
				if (routeData) {
					page.setMetaData('routeData', routeData);
				}
				
				DeskPRO_Window.getPanedShell().setNavPanePage(page);
			}).bind(this)
		});
	},
	
	
	
	/**
	 * Load a URL and treat it as a list pane.
	 *
	 * @param {String} url The URL of the list pane.
	 */
	loadListPane: function(url, routeData) {
		this.startLoadingIndicator();
		$.ajax({
			dataType: 'text',
			url: url,
			success: (function(data) {
				this.stopLoadingIndicator();
				var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.ListPane.Basic');
				
				page.setMetaData('routeUrl', url);
				if (routeData) {
					page.setMetaData('routeData', routeData);
				}
				
				DeskPRO_Window.getPanedShell().setListPanePage(page);
			}).bind(this)
		});
	},
	
	
	
	/**
	 * Load a URL and treat and put it into the tabbed pane.
	 *
	 * @param {String} url The URL of the page
	 */
	loadPage: function(url, routeData) {
		this.startLoadingIndicator();
		$.ajax({
			dataType: 'text',
			url: url,
			success: (function(data) {
				this.stopLoadingIndicator();
				var page = this.createPageFragment(data);
				
				page.setMetaData('routeUrl', url);
				if (routeData) {
					page.setMetaData('routeData', routeData);
				}
				
				DeskPRO_Window.getPanedShell().addTabPage(page);
			}).bind(this)
		});
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
	//# Global registry
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
	}
});