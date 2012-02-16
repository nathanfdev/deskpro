Orb.createNamespace('DeskPRO.Admin.Departments');

DeskPRO.Admin.ElementHandler.QuickSetup = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		this._autoTimezone();
		this._autoDeskproUrl();
	},

	_autoDeskproUrl: function() {
		var field = $('#setup_deskpro_url');
		if (field.val()) {
			return;//already have a value
		}

		var url = window.location.href + '';
		url = url.replace(/\/admin\/(.*?)$/, '');

		field.val(url + '/');
	},

	_autoTimezone: function() {
		var tz = $('#setup_timezone');
		if (tz.val() && tz.val() != 'UTC') {
			return;//already have a value
		}

		var detected = jstz.determine_timezone();
		console.log("Detected Timezone: %o", detected.name());
		if (detected.name()) {
			tz.find('option').each(function() {
				if ($(this).val() == detected.name()) {
					$(this).prop('selected', 'selected');
					console.log("Selected Timezone: %o", this);
				}
			})
		}
	}
});
