Orb.createNamespace('DeskPRO.Agent.PageFragment.NavPane');
DeskPRO.Agent.PageFragment.NavPane.Basic = new Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {
		$('h1, ul.sidebar-links > li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});