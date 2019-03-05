Orb.createNamespace('DeskPRO.UI');

/**
 * Use this to convert "dp_phone_number_hidden" hidden inputs into our phone number widget.
 *
 * We use a hidden input to ease user experience. Your real form must use the hidden input. This
 * will turn that hidden input into a usable widget for the user.
 *
 * Just create an instance and call render_phone_inputs() as often as you want.
 */
DeskPRO.UI.PhoneNumberInputs = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			'input_selector': '.dp_phone_number_hidden'
		};

		this.setOptions(options);
	},

	renderPhoneInputs: function () {
		var that = this;
		$(this.options.input_selector).each(function () {
			var input = $(this);
			var id = input.attr('id');
			var ext_input = input.parent().find('.dp_phone_ext_hidden');
			var dialCodes = $.fn.intlTelInput.getCountryData().reduce(
				function(a, cdata) {
					a[cdata.dialCode] = cdata.iso2;
					return a;
				},
				{}
			);

			if (input.next() && input.next().hasClass('intl-tel-input')) {
				return;
			}

			input.data('phone-number-inputs-touched', true);
			var phone_input = $('<input type="text" name="">');
			var phone_input_id = id + '_phone_input';
			phone_input.attr('id', phone_input_id);
			input.after(phone_input);

			phone_input.intlTelInput({
				defaultCountry: 'auto',
				autoPlaceholder: true,
				allowExtensions: true,
				nationalMode: true,
				utilsScript: window.DP_ASSET_URL.replace(/web\//, 'pub/') + 'build/phonenumber_utils.js',
				geoIpLookup: that.lookupGeoIp
			});

			if (input.val() && ext_input.val()) {
				phone_input.intlTelInput('setNumber', input.val() + ' ext. ' + ext_input.val());
			} else if (input.val()) {
				phone_input.intlTelInput('setNumber', input.val());
			}

			phone_input.width('300px');

			phone_input.on('input change', function () {
				// we have to check if the string " ext. " is in the actual input with
				// no extension present. if so, strip it out or we have bugs.
				var phone_input_val = phone_input.val();
				var raw_input = phone_input_val.split(" ext. ");
				var dial_code;
				for (var dcode in dialCodes) {
					if (!dialCodes.hasOwnProperty(dcode)) {
						return;
					}
					dial_code = '+' + dcode;
					var shouldRemoveDialCode = phone_input_val.indexOf(dial_code) == 0 && phone_input_val.length > (dial_code.length + 1);
					if (shouldRemoveDialCode) {
						phone_input.val($.trim(phone_input_val.substr(dial_code.length)));
						phone_input.intlTelInput('selectCountry', dialCodes[dcode]);
					}
				}
				if (raw_input.length > 1 && raw_input[1].length == 0) {
					phone_input.val(raw_input[0]);
				}

				ext_input.val(phone_input.intlTelInput('getExtension'));
				input.val(phone_input.intlTelInput('getNumber').split(" ext. ")[0] || phone_input.intlTelInput('getNumber'));
				if (!phone_input.intlTelInput('isValidNumber')) {
					var str = phone_input.val();
					if (str.indexOf('398', str.length - 3) !== -1) {
						phone_input.intlTelInput('setNumber', str.substring(0, str.length - 3));
					}
				}
			});

			phone_input.on('blur', function () {
				if (!phone_input.val()) {
					return;
				}
				if (phone_input.intlTelInput("isValidNumber")) {
					that.markValid(phone_input);
				} else {
					that.markInvalid(phone_input);
				}
			});

			phone_input.on('input', function () {
				if (!phone_input.val() || phone_input.intlTelInput("isValidNumber")) {
					that.markValid(phone_input);
				}
			});
		});
	},

	markInvalid: function (elem) {
		elem.css('background-color', 'red');
		elem.css('color', 'white');
	},

	markValid: function (elem) {
		elem.css('background-color', 'white');
		elem.css('color', 'black');
	},

	lookupGeoIp: function(callback) {
		$.ajax({
			url: window.BASE_URL + "agent/geoip",
			type: 'GET',
			dataType: 'json',
			noErrorOverride: true,
			success: function (data) {
				// use our api to find the country code of the agent, else use the default
				if ('country' in data.geoip && data.geoip.country && data.geoip.country.length == 2) {
					callback(data.geoip.country);
				} else {
					callback(window.DP_SETTINGS['core.default_country_code']);
				}
			}
		});
	}
});
