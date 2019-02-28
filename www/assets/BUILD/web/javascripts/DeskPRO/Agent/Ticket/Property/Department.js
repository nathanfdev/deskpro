Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Department = new Orb.Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	init: function() {
		this.optionName = 'department_id';
		this._formEl = null;
	},

	getName: function() {
		return 'department_id';
	},

	getValue: function() {
		return this.getFormEl().val();
	},

	setValue: function(value) {

		// They are the same value,
		// dont try and trigger changes
		if (parseInt(value) === parseInt(this.ticketPage.getEl('value_form').find('.department_id').val())) {
			return;
		}

		this.getFormEl().select2('val', value);

		if (value == "0") value = 0;

		var el = this.getInterfaceElement();

		var name = this.ticketPage.getEl('department_id').find('option:selected').data('full-title');
		this.getInterfaceElement().text(name);

		var icon = this.ticketPage.getEl('department_id').find('option:selected').data('icon');
		var iconEl = this.ticketPage.getEl('department_icon');
		if (icon) {
			iconEl.find('img').attr('src', icon);
			iconEl.show();
		} else {
			iconEl.hide();
		}

		this.ticketPage.getEl('value_form').find('.department_id').val(value);
	},

	getInterfaceElement: function() {
		return this.ticketPage.getEl('department_txt');
	},


	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = this.ticketPage.getEl('department_id');

		return this._formEl;
	}
});
