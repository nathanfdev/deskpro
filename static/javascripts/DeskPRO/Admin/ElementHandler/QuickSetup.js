Orb.createNamespace('DeskPRO.Admin.Departments');

DeskPRO.Admin.ElementHandler.QuickSetup = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;
		this._autoTimezone();
		this._autoDeskproUrl();

		this._doNetworkCheck();
		$('#recheck_network').on('click', function(ev) {
			ev.preventDefault();
			self._doNetworkCheck();
		});

		$('#dp_admin_nav').addClass('disabled');
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
		if (tz.val()) {
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
	},

	_doNetworkCheck: function() {
		$.ajax({
			url: BASE_URL + 'admin/misc/network-check.json',
			type: 'GET',
			dataType: 'json'
		});
	}
});
