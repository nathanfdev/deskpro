Orb.createNamespace('DeskPRO.Agent.PageFragment.SettingsPage');

DeskPRO.Agent.PageFragment.SettingsPage.Macros = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'settings_macros';
	},

	initPage: function(el) {
		var self = this;
		this.el = el;

		$('#settingswin').bind('dp_settings_macrosupdated', function() {
			self.settingsWindow.reloadInterface = true;
			self.settingsWindow.reloadTab('macros');
		});

		this.el.delegate('.delete-macro', 'click', function() {
			var row = $(this).closest('tr');
			var url = $(this).data('delete-url');

			DeskPRO_Window.showConfirm('Are you sure you want to permanantly delete this macros?', function() {
				$.ajax({
					url: url,
					success: function() {
						row.fadeOut(function() {
							row.remove();
						});
					}
				});
			});
		});
	}
});
