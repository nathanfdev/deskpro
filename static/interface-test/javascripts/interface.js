Orb.createNamespace('DeskPRO.Tech.Interface');

/**
 * Class that controls the basic page.
 *
 * All Pages that might need to use this class expect it to be in a variable
 * InterfaceShell.
 *
 * So to set up an app, you should do something like:
 * <code>
 * $(document).ready(function() {
 *     window.InterfaceShell = new DeskPRO.Tech.Interface.Shell();
 * });
 * </code>
 */
DeskPRO.Tech.Interface.Shell = new Class({
	page: null,
	pageLeft: null,
	
	initialize: function() {
		
		$('#page_wrap').layout({
			center: {
				paneSelector: '#page',
			},
			west: {
				paneSelector: '#page_left',
				resizable: true,
				closable: true,
				size: 500
			}
		});			

		this.setPageHtml('__nochange__');
		this.setPageLeftHtml('__nochange__');
	},
	
	
	
	/**
	 * Set the value of a new page (ie HTML returned from JSON).
	 *
	 * The new Page instance (handler class) is returned.
	 *
	 * @param {String} html
	 * @return {DeskPRO.Tech.Interface.Page}
	 */
	setPageHtml: function(html) {
		var j_page = $('#page');
		
		if (html != '__nochange__') {
			j_page.children().remove();
			j_page.html(html);
		}
		
		this.page = this._createPageObject(j_page);
		this.page.initPage();
		
		return this.page;
	},
	
	
	
	/**
	 * Set the value of a new page (ie HTML returned from JSON).
	 *
	 * The new Page instance (handler class) is returned.
	 *
	 * @param {String} html
	 * @return {DeskPRO.Tech.Interface.Page}
	 */
	setPageLeftHtml: function(html) {
		var j_page = $('#page_left');
		
		if (html != '__nochange__') {
			j_page.children().remove();
			j_page.html(html);
		}
		
		this.pageLeft = this._createPageObject(j_page);
		this.pageLeft.initPage();
	},
	
	
	
	/**
	 * Factory that decides which Page class to use based on
	 * the contents of the page.
	 */
	_createPageObject: function(j_el) {
		
		if ($('.page-tabbar', j_el)) {
			var page = new DeskPRO.Tech.Interface.PageTabbed(j_el);
		} else {
			var page = new DeskPRO.Tech.Interface.Page(j_el);
		}
		
		return page;
	},
	
	
	
	/**
	 * Get the currently set page handler object
	 *
	 * @return {DeskPRO.Tech.Interface.Page}
	 */
	getPage: function() {
		return this.page;
	},
	
	
	
	/**
	 * Get the currently set left page handler object
	 *
	 * @return {DeskPRO.Tech.Interface.Page}
	 */
	getPageLeft: function() {
		return this.pageLeft;
	}
});



//#####################################################################
//# DeskPRO.Tech.Interface.Page
//#####################################################################

/**
 * A Page is the right side of the screen.
 * HTML (ie from a JSON) call should
 */
DeskPRO.Tech.Interface.Page = new Class({
	
	el: null,
	options: {},
	
	initialize: function(j_el, options) {
		this.el = j_el;
		this.options = Object.merge(this.options, options || {});
	},
	
	initPage: function() {
		// A regular page has nothing special in it
		// It's just a page. No extra work :-)
	}
});



//#####################################################################
//# DeskPRO.Tech.Interface.Page
//#####################################################################

/**
 * A page that has a tab bar at the top.
 */
DeskPRO.Tech.Interface.PageTabbed = new Class({
	Extends: DeskPRO.Tech.Interface.Page,
	
	initPage: function() {

	}
});