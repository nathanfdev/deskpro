Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.Twitter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,
 
	initPage: function(el) {
		this.parent(el);

		$('li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});
