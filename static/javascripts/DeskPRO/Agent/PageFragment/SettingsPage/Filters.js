Orb.createNamespace('DeskPRO.Agent.PageFragment.SettingsPage');

DeskPRO.Agent.PageFragment.SettingsPage.Filters = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'settings_filters';
	},

	initPage: function(el) {
		var self = this;
		this.el = el;

		$('#settingswin').bind('dp_settings_filtersupdated', function() {
			self.settingsWindow.reloadInterface = true;
			self.settingsWindow.reloadTab('filters');
		});
	}
});
