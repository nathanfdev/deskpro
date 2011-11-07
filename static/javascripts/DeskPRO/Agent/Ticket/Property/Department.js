Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Department = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	optionName: 'department_id',

	init: function() {

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
		console.log(name);
		this.getInterfaceElement().text(name);
	},

	getInterfaceElement: function() {
		return $('.label-department-id', this.ticketPage.wrapper);
	},

	_formEl: null,
	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = $('input.department_id:first', this.ticketPage.valueForm);

		return this._formEl;
	}
});
