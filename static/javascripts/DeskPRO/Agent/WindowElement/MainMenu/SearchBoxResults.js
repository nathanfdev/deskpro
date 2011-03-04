Orb.createNamespace('DeskPRO.Agent.WindowElement.MainMenu');

DeskPRO.Agent.WindowElement.MainMenu.SearchBoxResults = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	init: function () {
		var opener = this.options.mainMenuOpener;
		$('#window_search_box').focus(function(ev) {
			opener.openMenu($('#window_search_form'), ev);
		}).click(function(ev) {
			// stop propgation because the focus above will open
			// the menu, dont want to bubble click into the container
			// to re-close it again
			ev.stopPropagation();
		});
	}
});