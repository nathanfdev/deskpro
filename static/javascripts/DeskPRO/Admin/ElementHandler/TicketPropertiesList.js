Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.TicketPropertiesList = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$(':checkbox[data-setting-name]', this.el).on('change', function() {
			var val = $(this).is(':checked') ? 1 : 0;
			var url = self.el.data('set-setting-url').replace(/_SETTING_NAME_/g, $(this).data('setting-name'));

			$.ajax({
				url: url,
				type: 'POST',
				data: { value: val },
				dataType: 'json'
			});
		});

		$(':checkbox[data-field-id]', this.el).on('change', function() {
			var val = $(this).is(':checked') ? 1 : 0;
			var url = self.el.data('set-field-url').replace(/_FIELD_ID_/g, $(this).data('field-id'));

			$.ajax({
				url: url,
				type: 'POST',
				data: { is_enabled: val },
				dataType: 'json'
			});
		});
	}
});
