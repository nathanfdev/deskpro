Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TicketSearch = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		
		$('.search-results table > tbody > tr', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});