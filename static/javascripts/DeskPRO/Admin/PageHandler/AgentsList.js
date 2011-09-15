Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.AgentsList = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		this.initPopoutTriggers();
		DeskPRO_Window.getMessageBroker().addMessageListener('teams.list.change', this.handleTeamListChange, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('usergroups.list.change', this.handleGroupListChange, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agents.list.change', this.handleListChange, this);
	},

	handleTeamListChange: function(info) {
		var tbody = $('#teams_rows');
		var exist = $('tr.team-' + info.teamId);

		var row = $(info.rowHtml);
		this.initPopoutTriggers(row);

		if (exist.length) {
			exist.replaceWith(row);
		} else {
			tbody.prepend(row);
		}

		$('.team-' + info.teamId + '-name').text(info.teamName);
	},

	handleGroupListChange: function(info) {
		var tbody = $('#usergroups_rows');
		var exist = $('tr.usergroup-' + info.usergroupId);

		var row = $(info.rowHtml);
		this.initPopoutTriggers(row);

		if (exist.length) {
			exist.replaceWith(row);
		} else {
			tbody.prepend(row);
		}

		$('.usergroup-' + info.usergroupId + '-tile').text(info.usergroupTitle);
	}
});
