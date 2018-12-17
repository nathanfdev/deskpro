Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Status = new Class({
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

		this.ticketPage.wrapper.find('div.layout-content').removeClass('awaiting_agent awaiting_user resolved archived hidden_deleted hidden_spam hidden_validating hidden_temp').addClass(status_classname);

		$('input.status:first', this.ticketPage.valueForm).val(status_code);

		// Hold is automatically taken off on PHP side when not awaiting user,
		// so need to reshow the 'set hold' button now incase user toggles status back to awaiting agent
		if (value == 'awaiting_agent') {
			this.ticketPage.getEl('hold_container').css('display', 'inline');
		} else {
			this.ticketPage.getEl('hold_container').css('display', 'none');
			this.ticketPage.getEl('hold_container').find('.hold').show();
			this.ticketPage.getEl('hold_container').find('.unhold').hide();
		}

    if (hidden_status) {
      this.getInterfaceElement().text(
        status_id == this.ticketPage.meta.deletedTicketStatusId
          ? this.ticketPage.meta.deletedTicketStatusTitle
          : this.ticketPage.meta.spamTicketStatusTitle
      );
    } else if (status_code == 'pending') {
      this.getInterfaceElement().text(this.ticketPage.meta.pendingTicketStatusTitle);
    } else {
      this.ticketPage.getEl('status_code').select2('val', status_code);
      this.ticketPage.getEl('status_code').val(status_code);
      var txt = this.ticketPage.getEl('status_code').find('option:selected').text().trim();
      this.getInterfaceElement().text(txt);
    }
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
