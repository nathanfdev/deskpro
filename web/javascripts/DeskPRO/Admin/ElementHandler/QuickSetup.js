Orb.createNamespace('DeskPRO.Admin.Departments');

DeskPRO.Admin.ElementHandler.QuickSetup = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		this._initInstallSoftwareSection();
		this._initCronSection();
		this._initLicenseSection();
		this._initOutgoingEmailSection();
		this._initIncomingEmailSection();

		this.totalStepCount = $('.mega-tick').length;

		self.recountSteps();
	},

	recountSteps: function() {
		var checks = $('.mega-tick');
		var total_count = this.totalStepCount;
		var done_count = checks.filter(':visible').length;

		if (done_count >= total_count) {
			$('#section_done').find('button').removeClass('disabled').on('click', function(ev) {
				ev.preventDefault();
				window.location = $(this).data('url');
			});

			$('#section_done').find('em').hide();
		} else {
			$('#section_done').find('label').text((total_count - done_count)+'');
		}
	},

	//##################################################################################################################
	//# Install Software Section
	//##################################################################################################################

	_initInstallSoftwareSection: function() {
		this._autoDeskproUrl();
		this._autoTimezone();

		var postData = $('#setting_form').serializeArray();
		var form = $('#setting_form');
		$.ajax({
			url: form.attr('action'),
			method: 'POST',
			data: postData
		});
	},

	_autoDeskproUrl: function() {
		var field = $('#setting_url');
		if (field.val()) {
			return;//already have a value
		}

		var url = window.location.href + '';
		url = url.replace(/\/admin\/(.*?)$/, '');

		field.val(url + '/');
	},

	_autoTimezone: function() {
		var tz = $('#setting_timezone');
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
	},

	//##################################################################################################################
	//# Cron
	//##################################################################################################################

	_initCronSection: function() {
		this.doCronCheck();
	},

	doCronCheck: function() {
		var self = this;
		$.ajax({
			url: $('#section_install_cron').data('check-url'),
			dataType: 'json',
			success: function(data) {
				if (!data || !data.cron_okay) {
					window.setTimeout(function() {
						self.doCronCheck();
					}, 15000);
				} else {
					$('#section_install_cron').find('.mega-tick').fadeIn();
					self.recountSteps();
				}
			}
		});
	},

	//##################################################################################################################
	//# License
	//##################################################################################################################

	_initLicenseSection: function() {
		var self = this;
		var wrapper = $('#section_enter_license');

		wrapper.find('.page-radio-group').on('click', function(ev) {
			wrapper.find('.page-radio-group').removeClass('open');
			$(this).addClass('open').find(':radio').prop('checked', true);
		});

		var reqlicGroup   = $('#lic_group_get_demo');
		var enterlicGroup = $('#lic_group_enter_license');

		$('#lic_do_send_again').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			enterlicGroup.find('.demo-sent-message').hide();
			wrapper.find('.page-radio-group').removeClass('open');
			enterlicGroup.removeClass('open');
			reqlicGroup.addClass('open').find(':radio').prop('checked', true);
		});

		//-----
		// Handling license request
		//-----

		reqlicGroup.find('form').on('submit', function(ev) {

			ev.preventDefault();
			ev.stopPropagation();

			enterlicGroup.find('.demo-sent-message').hide();
			enterlicGroup.find('.errors-box').hide();
			reqlicGroup.find('.errors-box').hide().find('.error-item').hide();

			var form = $(this);
			var formData = form.serializeArray();

			form.addClass('mark-loading');
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: formData,
				dataTyoe: 'json',
				complete: function() {
					form.removeClass('mark-loading');
				},
				success: function(data) {
					if (data.success) {
						enterlicGroup.find('.demo-sent-message').show();
						wrapper.find('.page-radio-group').removeClass('open');
						enterlicGroup.addClass('open').find(':radio').prop('checked', true);
					} else {
						var errbox = reqlicGroup.find('.errors-box').show();
						Object.each(data.error_codes, function(v,code) {
							code = code.replace(/\./g, '_');
							errbox.find('.error_' + code).show();
						});
					}
				}
			});
		});

		//-----
		// Handling license set
		//-----

		enterlicGroup.find('form').on('submit', function(ev) {

			ev.preventDefault();
			ev.stopPropagation();

			enterlicGroup.find('.errors-box').hide();

			var form = $(this);
			var formData = form.serializeArray();

			form.addClass('mark-loading');
			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: formData,
				dataTyoe: 'json',
				complete: function() {
					form.removeClass('mark-loading');
				},
				success: function(data) {
					if (data.success) {
						$('#section_enter_license').find('.mega-tick').fadeIn();
						self.recountSteps();
					} else {
						enterlicGroup.find('.errors-box').show().find('.lic-err-code').text(data.error_code);
					}
				}
			});
		});
	},

	//##################################################################################################################
	//# Outgoing Email
	//##################################################################################################################

	_initOutgoingEmailSection: function() {
		var self = this;
		var wrapper = $('#section_config_smtp');

		var form = wrapper.find('form');

		form.on('submit', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var formData = form.serializeArray();

			form.addClass('mark-loading');
			$.ajax({
				url: form.attr('action'),
				data: formData,
				type: 'POST',
				dataType: 'json',
				complete: function() {
					form.removeClass('mark-loading');
				},
				success: function(data) {
					if (data.success) {
						wrapper.find('.mega-tick').fadeIn();
						self.recountSteps();
					}
				}
			});
		});
	},

	//##################################################################################################################
	//# Incoming Email
	//##################################################################################################################

	_initIncomingEmailSection: function() {
		var self = this;
		var wrapper = $('#section_config_pop3');

		$('#pop3_skip_trigger').on('click', function(ev) {
			ev.preventDefault();
			wrapper.find('.mega-tick').fadeIn();
			self.recountSteps();
		});

		var form = wrapper.find('form');

		form.on('submit', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var formData = form.serializeArray();

			form.addClass('mark-loading');
			$.ajax({
				url: form.attr('action'),
				data: formData,
				type: 'POST',
				dataType: 'json',
				complete: function() {
					form.removeClass('mark-loading');
				},
				success: function(data) {
					if (data.success) {
						wrapper.find('.mega-tick').fadeIn();
						self.recountSteps();
					}
				}
			});
		});
	}
});
