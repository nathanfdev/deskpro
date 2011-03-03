Orb.createNamespace('DeskPRO.Admin.WindowElement');

DeskPRO.Admin.WindowElement.BasicMainMenu = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenu.Abstract,

	initialize: function(buttonEl) {
		this.parent(buttonEl);

		$('ol.icon-menu > li', this.menuEl).click(function(ev) {
			var a = $('a:first', this);
			if (a && a.attr('href')) {
				window.location = a.href;
				ev.stopPropagation();
			}
		});
	}
});