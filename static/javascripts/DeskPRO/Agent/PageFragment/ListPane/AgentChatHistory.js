Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.AgentChatHistory = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.parent();
		this.wrapper = null;
		this.contentWrapper = null;
	},

	initPage: function(el) {

		this.wrapper = $(el);
		this.contentWrapper = $('div.content:first', this.wrapper);

		if (this.getMetaData('noResults')) {
			this.noMoreResults = true;
			$('.no-more-results', this.contentWrapper).show();
		}
	}
});
