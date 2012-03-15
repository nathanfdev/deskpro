Orb.createNamespace('DeskPRO.Agent.PageFragment.SettingsPage');

DeskPRO.Agent.PageFragment.SettingsPage.Filters = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {

		var ticketsSection = DeskPRO_Window.sections.tickets_section;

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
			var filterId = $(this).data('filter-id');

			DeskPRO_Window.showConfirm('Are you sure you want to permanantly delete this filter?', function() {
				$.ajax({
					url: url,
					success: function() {
						row.fadeOut(function() {
							row.remove();

							if (ticketsSection) {
								ticketsSection.removeCustomFilter(filterId);
							}
						});
					}
				});
			});
		});

		var activateView = $('#settingswin').data('activateView');
		if (activateView) {
			this.activateView(activateView);
		}
	},

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'settings_filters';
	}
});
