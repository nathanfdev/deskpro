Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.SettingsPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		var form = $('#settings_form');

		Array.each(['user', 'agent', 'sendemail'], function(x) {
			if ($('#'+x+'_attach_limit_list_whitelist')[0]) {
				if ($('#'+x+'_attach_limit_list_whitelist').val().length) {
					var exist = $('#'+x+'_attach_limit_list_whitelist').val().split(',');
				} else {
					var exist = $('#'+x+'_attach_limit_list_blacklist').val().split(',');
				}
			} else {
				var exist = null;
			}

			if (!exist) {
				exist = [];
			}

			var maxSizeEl = $('#'+x+'_attach_maxsize_notice');
			var maxSize = parseInt(maxSizeEl.data('maxsize'));
			maxSize = (maxSize / 1024 / 1024) * 1000 * 1000; // 1000 based instead of 1024

			var filetypeText = $('#'+x+'_attach_limit_input').textext({
				plugins: 'autocomplete suggestions tags arrow prompt',
				suggestions: 'pdf doc docx xls txt rtf html htm gif png jpg jpeg bmp zip rar tgz gz'.split(' '),
				prompt: 'Enter a file extensions...',
				tagsItems: exist
			});

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

			form.on('submit', function(ev) {
				var blacklist = $('#'+x+'_attach_limit_list_blacklist');
				var whitelist = $('#'+x+'_attach_limit_list_whitelist');

				var list_json = filetypeText.textext()[0].hiddenInput().val();
				var list = [];
				if (list_json.length) {
					list = $.parseJSON(list_json);
				}
				list = list.join(',');

				if ($('#'+x+'_attach_limit_type_b').is(':checked')) {
					whitelist.val('');
					blacklist.val(list);
				} else {
					whitelist.val(list);
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
