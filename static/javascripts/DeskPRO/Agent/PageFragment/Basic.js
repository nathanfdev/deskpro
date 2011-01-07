Orb.createNamespace('DeskPRO.Agent.PageFragment');

/**
 * A generic page fragment is any kind of page we'll spit into the content
 * area of the currently loaded page. For example, tabs, lightbox content etc.
 *
 * Each page can have it's own resources that should be loaded before the HTML
 * for it is rendered (though that responsibility is up to whatever uses the PageFragment).
 */
DeskPRO.Agent.PageFragment.Basic = new Class({

	Implements: [Events],

	TYPENAME: 'basic',

	scripts: [],
	stylesheets: [],
	html: '',
	meta: {},
	
	initialize: function(html) {
		if (html) {
			this.html = html;
		}
		
		this.init();
		
		this.addEvent('activate', (function() {
			DeskPRO_Window.getMessageBroker().sendMessage('page-fragment.activated', { page: this });
		}).bind(this));
		this.addEvent('deactivate', (function() {
			DeskPRO_Window.getMessageBroker().sendMessage('page-fragment.deactivated', { page: this });
		}).bind(this));

		this.addEvent('activate', this.activate);
		this.addEvent('deactivate', this.deactivate);
		this.addEvent('render', this.initPage);
		this.addEvent('destroy', this.destroyPage);
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
});