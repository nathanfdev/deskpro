Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Department = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	optionName: 'department_id',

	init: function() {
		this._formEl = null;
	},

	getName: function() {
		return 'department_id';
	},

	getValue: function() {
		return this.getFormEl().val();
	},

	setValue: function(value) {
		this.getFormEl().val(value);

		if (value == "0") value = 0;

		var el = this.getInterfaceElement();

		var name = DeskPRO_Window.getDisplayName('department_full', value);
		this.getInterfaceElement().text(name);
	},

	getInterfaceElement: function() {
		return $('.label-department-id', this.ticketPage.wrapper);
	},


	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = this.ticketPage.getEl('department_id');

		return this._formEl;
	}
});
