Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.UserModePage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		var form = $('#mode_form');

		var quitit = false;
		form.on('click', 'li', function(ev) {
			if (quitit) return;
			quitit = true;

			var li = $(this);
			var radio = $('input[name="mode"]', li).click();
			$('li', form).removeClass('on');
			li.addClass('on');

			$('#save_btn_wrap').show();

			quitit = false;
		});

		form.on('submit', function(ev) {

			ev.preventDefault();

			var selectedLi = $('.dp-input-group.on');
			var formData = selectedLi.find('input, textarea, select').serializeArray();

			formData.append($('#general_settings').find('input, textarea, select').serializeArray());

			form.addClass('loading');
			$.ajax({
				url: form.attr('action'),
				data: formData,
				type: 'POST',
				complete: function() {
					form.removeClass('loading');
				},
				success: function() {
					$('#save_btn_wrap').hide();
				}
			});
		});

		var reg_urls = $('input[name="reg_url"]');
		reg_urls.on('change', function() {
			reg_urls.val($(this).val());
		});
	}
});
