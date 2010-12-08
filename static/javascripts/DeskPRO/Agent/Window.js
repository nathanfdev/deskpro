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
 * they should be loaded (and how). For example, "navpane:filters/", the first part says it'll
 * be a navpane fragment. The second part is a simple URL we can load via AJAX.
 */
DeskPRO.Agent.Window = new Class({
	
	shells: {},
	routePrefixes: {},
	registry: {},
	
	messageBroker: null,
	poller: null,

	initialize: function() {
		
		this.messageBroker = new DeskPRO.MessageBroker();
		this.poller = new DeskPRO.AjaxPoller.MessagePoller(this.messageBroker, {
			ajaxUrl: BASE_URL + 'tech/poller'
		});
		
		$('body').layout({
			north: {
				paneSelector: '#window_head',
				spacing_open: 0,
				spacing_closed: 0,
				size: 21
			},
			center: {
				paneSelector: '#pane_shell'
			}
		});
		
		var pane = new DeskPRO.Agent.Shells.ThreePaned();
		this.addShell('paned', pane);
		
		// Set ourselves up as the first route listener
		this.addPageRouteLoader('navpane', this.loadRoute.bind(this));
		this.addPageRouteLoader('listpane', this.loadRoute.bind(this));
		this.addPageRouteLoader('ticket', this.loadRoute.bind(this));
		this.addPageRouteLoader('person', this.loadRoute.bind(this));
		
		$('#window_header_nav li').click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
		
		// Set up listener for badge count
		this.getMessageBroker().addMessageListener('filters.counts', this.updateFilterCounts.bind(this));
	},
	
	updateFilterCounts: function (counts) {
		var total = 0;
		Object.each(counts, function (count, filter_id) {
			total += count;
			
		});
		
		$('.ticket-filter-count-all').html('(' + total + ')');
	},
	
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

		var sections = route.split(':');
		var master = sections.shift();
		var url = sections.pop();

		var data = {
			'route': route,
			'master': master,
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
		switch (routeData.master) {
			case 'navpane':
				this.loadNavPane(routeData.url);
				break;
				
			case 'listpane':
				this.loadListPane(routeData.url);
				break;
			
			default:
				this.loadPage(routeData.url);
				break;
		}
	},
	
	
	
	/**
	 * Load a URL and treat it as a nav pane.
	 *
	 * @param {String} url The URL of the nav pane
	 */
	loadNavPane: function(url) {
		$.ajax({
			dataType: 'text',
			url: url,
			success: (function(data) {
				var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.NavPane.Basic');
				DeskPRO_Window.getPanedShell().setNavPanePage(page);
			}).bind(this)
		});
	},
	
	
	
	/**
	 * Load a URL and treat it as a list pane.
	 *
	 * @param {String} url The URL of the list pane.
	 */
	loadListPane: function(url) {
		$.ajax({
			dataType: 'text',
			url: url,
			success: (function(data) {
				var page = this.createPageFragment(data, 'DeskPRO.Agent.PageFragment.ListPane.Basic');
				DeskPRO_Window.getPanedShell().setListPanePage(page);
			}).bind(this)
		});
	},
	
	
	
	/**
	 * Load a URL and treat and put it into the tabbed pane.
	 *
	 * @param {String} url The URL of the page
	 */
	loadPage: function(url) {
		$.ajax({
			dataType: 'text',
			url: url,
			success: (function(data) {
				var page = this.createPageFragment(data);
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
			'class': classname || 'DeskPRO.Agent.PageFragment.Basic'
		};
		
		var regex = /<script>([\s\S]*?)<\/script>/im;
		var matches = regex.exec(html);
		
		if (matches && matches.length) {
			try {
				eval(matches[1]);
			} catch (err) {
				console.error('Page fragment JS eval error: %o', err);
			}
		}
		
		console.debug('PageFragment class: %s', pageMeta.class);
		var fragment_class = Orb.getNamespacedObject(pageMeta.class);
		
		var page = new fragment_class(html);
		page.setMetaData(pageMeta);
		
		return page;
	},
	
	
	
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