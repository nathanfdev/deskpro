Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.OpenChats = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {

		this.wrapper = el;

		$('tr.with-route', el).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
		});
	}
});