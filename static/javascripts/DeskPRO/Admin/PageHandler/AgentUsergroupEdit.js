Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.AgentUsergroupEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	usergroup_id: 0,
	initialize: function(usergroup_id) {
		this.usergroup_id = usergroup_id;
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
			usergroupId: this.usergroup_id,
			usergroupTitle: new_name,
			rowHtml: new_row
		};

		parent_win.getMessageBroker().sendMessage('usergroups.list.change', info);
	}
});