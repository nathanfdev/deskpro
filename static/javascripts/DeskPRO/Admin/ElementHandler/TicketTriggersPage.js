Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.TicketTriggersPage = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;
		$('input.single-num').autoGrowInput({
			maxWidth: 120
		});

		$('#autoclose_options_form input, #urgency_options_form input').on('change, keyup', function() {
			var form = $(this).closest('form');
			$('button.save-trigger', form).show();
		});

		$('#autoclose_options_form button.save-trigger, #urgency_options_form button.save-trigger').on('click', function() {
			$(this).hide();
		});

		$('#autoclose_options_form').ajaxForm({
			dataType: 'json'
		});

		$('#urgency_options_form').ajaxForm({
			dataType: 'json'
		});
	}
});
