Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.SettingsAdvanced = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		this.filterBox = $('#settings_filter');
		this.filterUpdateTimeout = null;
		this.filterBox.on('keypress', this.handleFilterChange, this);

		this.settingsRows = $('#settings_rows');

		var self = this;
		$('input.dp-set-value', this.settingsRows).on('change', function() {
			self.updateSettingFromInput($(this));
		});
	},

	handleFilterChange: function() {

		if (this.fitlerUpdateTimeout) {
			window.clearTimeout(this.fitlerUpdateTimeout);
		}

		if (this.filterBox.val().trim() === '') {
			return;
		}

		this.fitlerUpdateTimeout = window.setTimeout(this.doFilterUpdate.bind(this), 1000);
	},

	doFilterUpdate: function() {
		var find = this.filterBox.val().trim();

		$('tr', this.settingsRows).each(function() {
			var el = $(this);

			if (el.html().indexOf(find) === -1) {
				el.hide();
			} else {
				el.show();
			}
		});
	},

	updateSettingFromInput: function(input) {
		var value = input.val().trim();
		var name = input.data('setting-name');

		$.ajax({
			url: BASE_URL + 'admin/settings/advanced-set/' + name,
			data: {value: value},
			dataType: 'json',
			type: 'POST'
		});
	}
});
