Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Status = new Orb.Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	setValue: function(value) {

		var status_id = false;
    var hidden_status = false;
		var status_classname = value;

		if (value && value.constructor.toString().indexOf("Array") != -1) {
			status_id = value[1].value;
			value = value[0].value;
			status_classname = value;
		} else {
			if (value.indexOf('.') != -1) {
				var parts = value.split('.');

				var value = parts[0];
				var status_id = parts[1];
				status_classname = value;
        
        if (status_id == this.ticketPage.meta.deletedTicketStatusId) {
          hidden_status = 'deleted';
        } else if (status_id == this.ticketPage.meta.spamTicketStatusId) {
          hidden_status = 'spam';
        }
        if (hidden_status) {
          this.ticketPage.fireEvent('ticketHidden', [hidden_status]);
          status_classname += '_' + hidden_status;
        }
			}
		}

    var status_code = value;
    if (status_id) {
      status_code += '.' + status_id;
    }

    if (status_classname == 'pending') {
      status_classname += ' awaiting_agent';
    }

		this.ticketPage.wrapper.find('div.layout-content').removeClass('awaiting_agent awaiting_user resolved archived hidden_deleted hidden_spam hidden_validating hidden_temp').addClass(status_classname);

		$('input.status:first', this.ticketPage.valueForm).val(status_code);

    if (hidden_status) {
      this.getInterfaceElement().text(
        status_id == this.ticketPage.meta.deletedTicketStatusId
          ? this.ticketPage.meta.deletedTicketStatusTitle
          : this.ticketPage.meta.spamTicketStatusTitle
      );
    } else {
      this.ticketPage.getEl('status_code').select2('val', status_code);
      this.ticketPage.getEl('status_code').val(status_code);
      var txt = this.ticketPage.getEl('status_code').find('option:selected').text();
      this.getInterfaceElement().text(txt);
    }

    if (value == 'awaiting_agent') {
      this.addPendingOptions();
    } else if (value != 'pending') {
      this.removePendingOptions();
    }
	},

  addPendingOptions: function() {
    if (this.ticketPage.getEl('status_code').find('option[value="pending"]').length) {
      return;
    }

    if (!this.ticketPage.meta.ticket_perms.modify_set_hold) {
      return;
    }

    var options = this.ticketPage.getEl('penging_statuses_options_template').find("select > option").clone();
    this.ticketPage.getEl('status_code').append(options);
  },

  removePendingOptions: function() {
    this.ticketPage.getEl('status_code').find('option[value^="pending"]').remove();
  },

	getValue: function() {
		var data = [];
		data.push({
			full_name: 'actions[status]',
			value: $('input.status:first', this.ticketPage.valueForm).val()
		});

		return data;
	},

	getInterfaceElement: function() {
		return this.ticketPage.getEl('status_txt');
	},

	getName: function() {
		return 'status';
	}
});
