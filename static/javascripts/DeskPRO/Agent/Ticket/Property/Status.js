Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Status = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.StandardOption,

	setValue: function(value) {

		var hidden_status = false;

		if (value && value.constructor.toString().indexOf("Array") != -1) {
			hidden_status = value[1].value;
			value = value[0].value;
		} else {
			if (value.indexOf('.') != -1) {
				var parts = value.split('.');

				var value = parts[0];
				var hidden_status = parts[1];
			}
		}

		var statusEl = this.getInterfaceElement().parent();
		var hiddenEl = $('.ticket-hidden-bar', this.ticketPage.contentWrapper);
		var hiddenWordEl = $('span', hiddenEl);
		var hiddenFormEl = $('input.hidden_status:first', this.ticketPage.valueForm);

		hiddenEl.hide();
		hiddenFormEl.val('');
		if (hidden_status) {
			hiddenWordEl.text(DeskPRO_Window.getDisplayName('hidden_status', hidden_status));
			hiddenEl.show();

			hiddenFormEl.val(hidden_status);
		}

		this.parent(value);
		statusEl.removeClass('ticket-open ticket-closed ticket-pending ticket-resolved ticket-hidden').addClass('ticket-' + value);
	},

	getValue: function() {
		var data = [];
		data.push({
			full_name: 'actions[status]',
			value: this.parent()
		});

		data.push({
			full_name: 'actions[hidden_status]',
			value: $('input.hidden_status:first', this.ticketPage.valueForm).val()
		});

		return data;
	}
});