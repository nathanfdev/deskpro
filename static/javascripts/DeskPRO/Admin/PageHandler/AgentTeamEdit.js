Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.AgentTeamEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	team_id: 0,
	initialize: function(team_id) {
		this.team_id = team_id;
	},

	initPage: function() {
		var self = this;
		$('.save-trigger').click(function() {
			$('form:first').submit();
		});
		$('.cancel-trigger').click(function() {
			self.closeThisPopout();
		});
	},

	updateParent: function(new_name, new_row) {
		var parent_win = this.getOpenerDeskPRO();
		if (!parent_win) return;

		var info = {
			teamId: this.team_id,
			teamName: new_name,
			rowHtml: new_row
		};

		parent_win.getMessageBroker().sendMessage('teams.list.change', info);
	}
});