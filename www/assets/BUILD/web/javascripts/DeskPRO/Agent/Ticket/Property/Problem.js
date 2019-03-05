Orb.createNamespace('DeskPRO.Agent.Ticket.Property');

DeskPRO.Agent.Ticket.Property.Problem = new Orb.Class({
	Extends: DeskPRO.Agent.Ticket.Property.Abstract,

	init: function() {
		this._formEl = null;
		this.optionName = 'problem_id';
		this._formEl = null;
	},

	getName: function() {
		return 'problem_id';
	},

	getValue: function() {
		return this.getFormEl().val();
	},

	setValue: function(value, extra) {
		value = extra ? parseInt(extra.problem_id) : parseInt(value);


		// They are the same value,
		// dont try and trigger changes
		if (value == parseInt(this.getFormEl().val())) {
			return;
		}

		this.getFormEl().val(value);

		if (!extra) {
			return;
		}

		var el = this.getInterfaceElement();
		if (value) {
			var html = '<span class="prop-val problem_id">' + extra.problem_title + '</span>&nbsp;' +
					'[&nbsp;<a class="agent_link incident-link" data-problem-id="' + value +
					'">Incidents: ' + extra.incidents + '</a>&nbsp;]' +
					'[&nbsp;<a class="agent_link close-problem-link">close</a>&nbsp;]';

			el.parent().html(html);
		} else {
			el.addClass('no-value').html(el.data('no-value-label') || 'None');
			el.parent().contents().filter(function() {
				return this !== el[0];
			}).remove();
		}
	},

	getInterfaceElement: function() {
		return $('.prop-val.'+this.optionName, this.ticketPage.contentWrapper).first();
	},

	getFormEl: function() {
		if (this._formEl !== null) return this._formEl;

		this._formEl = $('[name="ticket['+this.optionName+']"]', this.ticketPage.wrapper).first();

		return this._formEl;
	}
});
