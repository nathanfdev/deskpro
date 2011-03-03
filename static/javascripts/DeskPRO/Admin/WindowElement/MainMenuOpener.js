Orb.createNamespace('DeskPRO.Admin.WindowElement');

DeskPRO.Admin.WindowElement.MainMenuOpener = new Class({
	Extends: DeskPRO.Agent.WindowElement.MainMenuOpener,

	getMenuHandlerClass: function(li) {
		var class = this.parent(li);

		if (!class) {
			class = DeskPRO.Admin.WindowElement.BasicMainMenu;
		}

		return class;
	}
});