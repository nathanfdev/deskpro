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
		this.getFormEl().val(value);

		if (value == "0") value = 0;

		var el = this.getInterfaceElement();

		if (value == 0) {
			el.text('Unassigned');
			el.css('background-image', '');
		} else {
			var teamInfo = DeskPRO_Window.getTeamInfo(value);
			el.text(teamInfo.name);
			el.css('background-image', teamInfo.pictureUrlSizable.replace('{SIZE}', 20));
		}
	},

	_getInterfaceElement: function() {
		return $('.prop-agent-team-id:first', this.ticketPage.wrapper);
	},

	_formEl: null,
	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = $('input.agent_team_id:first', this.ticketPage.valueForm);

		return this._formEl;
	}
});