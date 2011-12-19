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

			var formData = form.serializeArray();

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
	}
});
