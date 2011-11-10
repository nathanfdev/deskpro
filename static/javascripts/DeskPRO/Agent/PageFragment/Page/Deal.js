Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.Deal = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.allowDupe = true;
		this.TYPENAME = 'deal';
	},

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
	}
});
