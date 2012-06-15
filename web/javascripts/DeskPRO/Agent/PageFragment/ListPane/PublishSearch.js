Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishSearch = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
	}
});