Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');

DeskPRO.Agent.PageFragment.NavPane.Twitter = new Class({
	Extends: DeskPRO.Agent.PageFragment.NavPane.Basic,
 
	initPage: function(el) {
		this.parent(el);

		$('li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});
