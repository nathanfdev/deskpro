Orb.createNamespace('DeskPRO.Agent.PageFragment.SettingsPage');

DeskPRO.Agent.PageFragment.SettingsPage.Filters = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'settings_filters';
	},

	initPage: function(el) {
		window.settings_filters_page = this;
		this.el = el;
		var self = this;

		$('#settingswin').bind('dp_settings_filtersupdated', function() {
			self.settingsWindow.reloadInterface = true;
			self.settingsWindow.reloadTab('filters');
		});

		this.el.on('click', '.delete-filter', function() {
			var row = $(this).closest('tr');
			var url = $(this).data('delete-url');

			DeskPRO_Window.showConfirm('Are you sure you want to permanantly delete this filter?', function() {
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

		var activateView = $('#settingswin').data('activateView');
		if (activateView) {
			this.activateView(activateView);
		}
	}
});
