Orb.createNamespace('DeskPRO.Agent.PageFragment');

/**
 * A generic page fragment is any kind of page we'll spit into the content
 * area of the currently loaded page. For example, tabs, lightbox content etc.
 *
 * Each page can have it's own resources that should be loaded before the HTML
 * for it is rendered (though that responsibility is up to whatever uses the PageFragment).
 */
DeskPRO.Agent.PageFragment.Basic = new Class({

	Implements: [Events, DeskPRO.Agent.Widgetable],

	pageUuid: null,
	ZONE: 'agent',
	TYPENAME: 'basic',

	/**
	 * When this is true, the loader (in Window) will allow duplicates
	 * of a tab with the same thing
	 */
	allowDupe: false,

	scripts: [],
	stylesheets: [],
	html: '',
	meta: {},
	urls: {},

	featureSelectors: {
		routes: [],
		times: []
	},

	initialize: function(html) {

		this.pageUid = Orb.uuid();

		if (html) {
			this.html = html;
		}

		this.addEvent('activate', (function() {
			DeskPRO_Window.getMessageBroker().sendMessage('page-fragment.activated', { page: this });
		}).bind(this));
		this.addEvent('deactivate', (function() {
			DeskPRO_Window.getMessageBroker().sendMessage('page-fragment.deactivated', { page: this });
		}).bind(this));

		// Auto-init
		this.addEvent('render', (function(wrapper) {
			this.initFeaturesOnCollection(wrapper);
		}).bind(this));

		if (this.getMetaData('initRoutesOn')) {
			var tmp = this.getMetaData('initRoutesOn');
			if (typeOf(tmp) == 'string') {
				tmp = [tmp];
			}

			for (var i = 0; i < tmp.length; i++) {
				this.featureSelectors.routes.push(tmp[i]);
			}
		}

		this.addEvent('render', function(el) {
			if (this.getMetaData('widgets')) {
				this.initWidgets(this.getMetaData('widgets'), {
					personId: DESKPRO_PERSON_ID,
					deskproPath: BASE_URL,
					proxyKey: DESKPRO_PROXY_KEY
				});
				this.initWidgetsDom(el);
			}
		});

		var self = this;

		// Standard hook methods
		this.addEvent('activate', this.activate);
		this.addEvent('deactivate', this.deactivate);
		this.addEvent('render', this.initPage);
		this.addEvent('destroy', this.destroyPage);

		this.init();
	},

	/**
	 * Empty hook method for children
	 */
	init: function() { },

	/**
	 * Called when the fragment has been activated (comes into view).
	 */
	activate: function() { },

	/**
	 * Called when the fragment is deactivated (hidden from view)
	 */
	deactivate: function() { },

	/**
	 * Init all standard features (using page-defined selectors) on a wrapper
	 */
	initFeaturesOnCollection: function(wrapper, featureSelectors) {

		featureSelectors = featureSelectors || this.featureSelectors;

		if (featureSelectors.routes && featureSelectors.routes.length) {
			this.initRoutesOnCollection($(featureSelectors.routes.join(', '), wrapper));
		}

		if (featureSelectors.times && featureSelectors.times.length) {
			this.initTimesOnCollection($(featureSelectors.times.join(', '), wrapper));
		}

		if (this.wrapper) {
			this.initTipsOnCollection($('.person-tip', this.wrapper));
		}
	},

	/**
	 * Init route loaders on all elements in a collection
	 */
	initRoutesOnCollection: function(els) {
		els.click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	},

	/**
	 * Init time agos on all elements in a collection
	 */
	initTimesOnCollection: function(els) {
		els.timeago();
	},


	initTipsOnCollection: function(els) {
		$(els).each(function() {
			var el = $(this);

			if (el.is('.person-tip')) {
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
			}
		});
	},



	/**
	 * Set metadata about this page.
	 *
	 * @param mixed name Either a string name to use with value, or an object of key/value pairs
	 * @param mixed value Only used if name is a string, the value to set
	 */
	setMetaData: function(name, value) {
		// Assigning multiple values from a hash
		if (value === undefined && typeOf(name) == 'object') {
			this.meta = Object.merge(this.meta, name);
		} else {
			this.meta[name] = value;
		}
	},



	/**
	 * Get a hash of all the metadata.
	 *
	 * @return {Object}
	 */
	getAllMetaData: function() {
		return this.meta;
	},



	/**
	 * Get a specific piece of metadata.
	 *
	 * @param {String} name The name of the data you want
	 * @param mixed default_value The value to return if the metadata is undefined
	 */
	getMetaData: function(name, default_value) {
		if (default_value === undefined) {
			default_value = null;
		}

		if (this.meta[name] === undefined) {
			return default_value;
		}

		return this.meta[name];
	},


	/**
	 * Get a URL pattern
	 */
	getUrl: function(name, vars) {

		if (!this.meta.urls) {
			console.error('Unknown url name %s (no urls set)', name);
			return null;
		}

		if (!this.meta.urls[name]) {
			console.error('Unknown url name %s', name);
			return null;
		}

		var url = this.meta.urls[name];
		if (vars) {
			Object.each(vars, function(v,k) {
				url = url.replace('{'+k+'}', v);
			});
		}

		return url;
	},



	/**
	 * Get the scripts required by this fragment.
	 *
	 * @return {Array}
	 */
	getScripts: function() {
		return this.scripts;
	},



	/**
	 * Get stylesheets required by this fragment
	 *
	 * @return {Array}
	 */
	getStylesheets: function() {
		return this.stylesheets;
	},



	/**
	 * Get the HTML source for this fragment.
	 *
	 * @return {String}
	 */
	getHtml: function() {
		return this.html;
	},



	/**
	 * Should be called after all resources are laoded and after the
	 * HTML is in the dom.
	 *
	 * @param {jQuery} el The wrapper element
	 */
	initPage: function(el) {

	},



	/**
	 * Called after the page should be destroyed. Any specific cleanup required can be done
	 * here if for example an element was moved during initPage etc.
	 */
	destroyPage: function() {

	},


	/**
	 * Get an element within this page by ID, using the baseId set in metadata if avail
	 *
	 * @param id
	 */
	getEl: function(id) {
		if (this.getMetaData('baseId')) {
			id = this.getMetaData('baseId') + '_' + id;
		}

		var context = null;
		if (this.wrapper) {
			context = this.wrapper;
		}

		return $('#' + id, context);
	}
});