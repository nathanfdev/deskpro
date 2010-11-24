Orb.createNamespace('DeskPRO.Agent.PageFragment');

/**
 * A generic page fragment is any kind of page we'll spit into the content
 * area of the currently loaded page. For example, tabs, lightbox content etc.
 *
 * Each page can have it's own resources that should be loaded before the HTML
 * for it is rendered (though that responsibility is up to whatever uses the PageFragment).
 */
DeskPRO.Agent.PageFragment.Basic = new Class({

	scripts: [],
	stylesheets: [],
	destroyEls: [],
	html: '',
	meta: {},
	
	setAllMetaData: function(meta) {
		this.meta = Object.merge(this.meta, meta);
	},
	
	setMetaData: function(name, value) {
		this.meta[name] = value;
	},
	
	getAllMetaData: function() {
		return this.meta;
	},
	
	getMetaData: function(name, default_value) {
		if (default_value === undefined) {
			default_value = null;
		}
		
		if (this.meta[name] === undefined) {
			return default_value;
		}
		
		return this.meta[name];
	},
	
	initialize: function(html, scripts, stylesheets) {
		if (html) {
			this.html = html;
		}
		
		if (scripts) {
			if (typeOf(scripts) == 'string') {
				scripts = [scripts];
			}
			
			this.scripts = scripts;
		}
		
		if (stylesheets) {
			if (typeOf(stylesheets) == 'string') {
				stylesheets = [stylesheets];
			}
			
			this.stylesheets = stylesheets;
		}
	},
	
	getScripts: function() {
		return this.scripts;
	},
	
	getStylesheets: function() {
		return this.stylesheets;
	},
	
	getHtml: function() {
		return this.html;
	},
	
	loadResources: function(callback) {
		
		var batch = [];
		
		for (var i = 0; i < this.scripts.length; i++) {
			batch.push({ type: 'script', url: this.scripts[i] });
		}
		
		for (var i = 0; i < this.stylesheets.length; i++) {
			batch.push({ type: 'stylesheet', url: this.stylesheets[i] });
		}
		
		Orb.resourceLoader.loadBatch(batch, callback);
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
	 * here if for example an element was moved etc.
	 *
	 * @param {jQuery} el The wrapper element
	 */
	destroyPage: function(el) {
		var del = null;
		while (del = this.destroyEls.pop()) {
			$(del).remove();
		}
	},
});