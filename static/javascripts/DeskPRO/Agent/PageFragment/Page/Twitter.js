Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');

DeskPRO.Agent.PageFragment.Page.Twitter = new Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {
		this.parent(el);

		$('li', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});
