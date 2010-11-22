Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');
DeskPRO.Agent.PageFragment.ListPane.Basic = new Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,
	
	initPage: function(el) {
		$('li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});