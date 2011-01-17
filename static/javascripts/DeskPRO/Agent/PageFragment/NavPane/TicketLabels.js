Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.TicketLabels = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
	
	wrapper: null,

	initPage: function(el) {
		this.parent(el);	
		this.wrapper = el;
		
		$('ul.tag-cloud li').click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});