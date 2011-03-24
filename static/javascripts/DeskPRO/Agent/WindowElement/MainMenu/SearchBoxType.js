Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.SearchBoxType = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {
		$('li[data-search-type]', this.buttonEl).click(function() {
			var search_type = $(this).data('search-type');
			$('#window_search_type').removeClass('all users tickets').addClass(search_type).data('search-type', search_type);
		});

		var self = this;
		$('#search-form').submit(function(ev) {
			ev.preventDefault();
			self.navToFirst();
		});
	},

	navToFirst: function() {
		var el = $('#window_search_rusults_wrap .results-list li[data-route]:first');
		if (el.length) {
			DeskPRO_Window.runPageRouteFromElement(el);
			this.closeMenu();
		}
	}
});