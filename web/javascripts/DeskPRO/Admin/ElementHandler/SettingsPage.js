Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.SettingsPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		var form = $('#settings_form');

		Array.each(['user', 'agent', 'sendemail'], function(x) {
			var maxSizeEl = $('#'+x+'_attach_maxsize_notice');
			var maxSize = parseInt(maxSizeEl.data('maxsize'));
			maxSize = (maxSize / 1024 / 1024) * 1000 * 1000; // 1000 based instead of 1024

			function formatSliderVal() {
				var val = parseInt($('#'+x+'_attach_maxsize').val());

				if (val > maxSize) {
					maxSizeEl.show();
					val = maxSize;
					$('#'+x+'_attach_maxsize_slider').slider('value', val);
				} else {
					maxSizeEl.hide();
				}

				if (val) {
					var mb = parseFloat(val / 1000000).toFixed(2);
				} else {
					var mb = 0;
				}

				$('#'+x+'_attach_maxsize_label').text(mb);
				$('#'+x+'_attach_maxsize').val(val);
			}
			$('#'+x+'_attach_maxsize_slider').slider({
				min: 0,   // 0.1 mb
				max: 50000000, // 100 mb
				step: 100000,
				value: $('#'+x+'_attach_maxsize').val(),
				slide: function(event, ui) {
					var val = parseInt(ui.value);
					$('#'+x+'_attach_maxsize').val(val);
					formatSliderVal();
				}
			});
			formatSliderVal();

			var blacklistOpt = $('#'+x+'_attach_limit_type_b');
			var whitelistOpt = $('#'+x+'_attach_limit_type_w');
			var blacklist = $('#'+x+'_attach_limit_list_blacklist');
			var whitelist = $('#'+x+'_attach_limit_list_whitelist');

			blacklistOpt.on('click', function() {
				blacklist.show();
				whitelist.hide();
			});

			whitelistOpt.on('click', function() {
				whitelist.show();
				blacklist.hide();
			});

			if (whitelistOpt.prop('checked')) {
				whitelistOpt.prop('checked', true);
				whitelistOpt.trigger('click');
			} else {
				blacklistOpt.prop('checked', true);
				blacklistOpt.trigger('click');
			}

			form.on('submit', function(ev) {
				if ($('#'+x+'_attach_limit_type_b').is(':checked')) {
					whitelist.val('');
				} else {
					blacklist.val('');
				}
			});
		}, this);

		var settingsWarn = new DeskPRO.UI.Overlay({
			contentElement: '#adv_settings_warn',
			triggerElement: '#adv_settings_btn'
		});
	}
});
