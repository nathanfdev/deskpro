Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.SettingsPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		var form = $('#settings_form');

		Array.each(['user', 'agent'], function(x) {
			if ($('#'+x+'_attach_limit_list_whitelist').val().length) {
				var exist = $('#'+x+'_attach_limit_list_whitelist').val().split(',');
			} else {
				var exist = $('#'+x+'_attach_limit_list_blacklist').val().split(',');
			}
			if (!exist) {
				exist = [];
			}

			var filetypeText = $('#'+x+'_attach_limit_input').textext({
				plugins: 'autocomplete suggestions tags arrow prompt',
				suggestions: 'pdf doc docx xls txt rtf html htm gif png jpg jpeg bmp zip rar tgz gz'.split(' '),
				prompt: 'Enter a file extensions...',
				tagsItems: exist
			});

			function formatSliderVal() {
				var val = $('#'+x+'_attach_maxsize').val();
				var mb = parseFloat(val / 1000000).toFixed(2);

				$('#'+x+'_attach_maxsize_label').text(mb);
				$('#'+x+'_attach_maxsize').val(val);
			}
			$('#'+x+'_attach_maxsize_slider').slider({
				min: 100000,   // 0.1 mb
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

	}
});
