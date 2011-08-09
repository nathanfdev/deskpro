Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Status = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

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

		var btnSetOpen = $('.set-resolved button, .set-closed button, .mark-spam button', this.ticketPage.getEl('action_buttons'));
		var btnSetOther = $('.set-open button', this.ticketPage.getEl('action_buttons'));
		if (value == 'open' || value == 'pending') {
			btnSetOpen.show();
			btnSetOther.hide();
		} else {
			btnSetOpen.hide();

			if (value == 'resolved' || value == 'pending') {
				btnSetOther.show();
			} else {
				btnSetOther.hide();
			}
		}

		var statusDisplay = $('.prop-status-icon', this.ticketPage.getEl('ticket_header'));
		statusDisplay.attr('title', value);
		var icon = $('> span', statusDisplay);
		icon.attr('class', '');
		icon.addClass('ticket-' + value);

		$('input.status:first', this.ticketPage.valueForm).val(value);
		$('input.hidden_status:first', this.ticketPage.valueForm).val(hidden_status);
	},

	getValue: function() {
		var data = [];
		data.push({
			full_name: 'actions[status]',
			value: $('input.status:first', this.ticketPage.valueForm).val()
		});

		data.push({
			full_name: 'actions[hidden_status]',
			value: $('input.hidden_status:first', this.ticketPage.valueForm).val()
		});

		return data;
	},

	getName: function() {
		return 'status';
	}
});