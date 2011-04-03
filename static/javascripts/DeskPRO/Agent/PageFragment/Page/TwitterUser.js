Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.TwitterUser = new Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,

	el: null,
	tabs: null,

	initPage: function(el) {
		this.el = $(el);

		this._initTimeago();
		this._initTabs();

	},

	_initTimeago: function() {
		this.initTimesOnCollection($('.timeago', this.el));
	},

	_initTabs: function() {
		this.tabs = new DeskPRO.UI.SimpleTabs({
			context: $('.full-container-tabbed', this.el),
			triggerElements: $('.full-container-tabbed-tabs li', this.el)
		});
	}
});
