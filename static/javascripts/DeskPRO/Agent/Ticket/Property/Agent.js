Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Agent = new Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	optionName: 'agent_id',

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
			var agentInfo = DeskPRO_Window.getAgentInfo(value);
			el.text(agentInfo.name);
			el.css('background-image', agentInfo.pictureUrlSizable.replace('{SIZE}', 20));
		}

		// Hide buttons that dont make sense anymore
		if (value == DESKPRO_PERSON_ID) {
			$('.assign-me', this.ticketPage.wrapper);
		} else if (value == 0) {
			$('.assign-none', this.ticketPage.wrapper);
		}
	},

	getInterfaceElement: function() {
		return $('.prop-agent-id:first', this.ticketPage.wrapper);
	},

	_formEl: null,
	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = $('input.agent_id:first', this.ticketPage.valueForm);

		return this._formEl;
	}
});
