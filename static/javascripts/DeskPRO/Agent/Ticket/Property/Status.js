Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Status = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	setValue: function(value) {

		var hidden_status = false;
		var status_classname = value;

		if (value && value.constructor.toString().indexOf("Array") != -1) {
			hidden_status = value[1].value;
			value = value[0].value;
			status_classname = value;
		} else {
			if (value.indexOf('.') != -1) {
				var parts = value.split('.');

				var value = parts[0];
				var hidden_status = parts[1];
				status_classname = value + '_' + hidden_status;

				this.ticketPage.fireEvent('ticketHidden', [hidden_status]);
			}
		}

		$('.page-header .set-status', this.ticketPage.wrapper).hide();
		$('.page-header .set-status.' + status_classname, this.ticketPage.wrapper).show();

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

	getInterfaceElement: function() {
		return $('.status.set-status', this.ticketPage.contentWrapper);
	},

	getName: function() {
		return 'status';
	}
});
