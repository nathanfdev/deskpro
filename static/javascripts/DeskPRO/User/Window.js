Orb.createNamespace('DeskPRO.User');

/**
 * User window handler
 */
DeskPRO.User.Window = new Orb.Class({

	Extends: DeskPRO.BasicWindow,

	init: function() {
		this.PAGE = null;
	},

	initPage: function() {
		if (this.PAGE) {
			this.PAGE.initPage();
		}
	},

	setPageHandler: function(page) {
		this.PAGE = page;
	},

	getPageHandler: function() {
		return this.PAGE;
	}
});