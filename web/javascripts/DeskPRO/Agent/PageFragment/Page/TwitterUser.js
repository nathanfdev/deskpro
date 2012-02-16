Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.TwitterUser = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,
	initPage: function(el) {
		this.el = $(el);

		this._initTimeago();
		this._initTabs();

	},

	_initTimeago: function() {
		//this.initTimesOnCollection($('.timeago', this.el));
	},

	_initTabs: function() {
		
		$('.profile-box-container.tabbed', this.wrapper).each(function() {
			var simpleTabs = new DeskPRO.UI.SimpleTabs({
				triggerElements: '> header li',
				context: this
			});
		});
		
	}
});
