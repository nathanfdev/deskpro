Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.TicketPropertiesList = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var self = this;

		$(':checkbox[data-setting-name]', this.el).change(function() {
			var val = $(this).is(':checked') ? 1 : 0;
			var url = self.el.data('set-setting-url').replace(/_SETTING_NAME_/g, $(this).data('setting-name'));

			$.ajax({
				url: url,
				type: 'POST',
				data: { value: val },
				dataType: 'json'
			});
		});
	}
});
