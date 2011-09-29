Orb.createNamespace('DeskPRO.Admin.Departments');

DeskPRO.Admin.Departments.AgentSelector = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	initPage: function() {
		var self = this;

		this.department_id = this.el.data('department-id');
		var btn = this.el;

		this.optionbox = new DeskPRO.UI.OptionBox({
			element: $('#optionbox_dep_' + this.department_id),
			trigger: this.el,
			onClose: function(optionbox) {
				var countTeams  = optionbox.getCount('teams');
				var countAgents = optionbox.getCount('agents');

				var words = [];
				if (countAgents > 0) {
					words.push(countAgents + ' agents');
				}
				if (countTeams > 0) {
					words.push(countTeams + ' teams');
				}

				if (!words.length) {
					words = ['No agents'];
				}

				btn.text(words.join(', '));

				self.fireEvent('updated', [self.department_id, optionbox.getSelected('agents'), optionbox.getSelected('teams'), self]);
			}
		});
	},

	getHandlerName: function() {
		return 'agent_selector';
	}
});
