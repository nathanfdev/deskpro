Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishSearchLog = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	TYPENAME: 'publish_searchlog',

	initPage: function(el) {
		this.tabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('li', this.getEl('tabs'))
		});
	}
});
