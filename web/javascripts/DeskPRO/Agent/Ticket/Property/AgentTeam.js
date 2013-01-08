Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.AgentTeam = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	optionName: 'agent_team_id',

	init: function() {

	},

	getName: function() {
		return this.optionName;
	},

	getValue: function() {
		return this.getFormEl().val();
	},

	setValue: function(value) {
		this.getFormEl().select2('val', value);
		this.getInterfaceElement().addClass('eat-change').val(value).change();
	},

	getInterfaceElement: function() {
		return this.ticketPage.getEl('agent_team_sel');
	},

	_formEl: null,
	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = $('input.agent_team_id:first', this.ticketPage.valueForm);

		return this._formEl;
	}
});
