Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.UsergroupsEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	usergroup_id: 0,
	initialize: function(usergroup_id) {
		this.usergroup_id = usergroup_id;
	},

	initPage: function() {

	},

	updateParentListRow: function(row_html) {
		var parent_win = this.getOpenerDeskPRO();
		if (!parent_win) return;

		parent_win.getMessageBroker().sendMessage('usergroups.list.change', {
			usergroup_id: this.usergroup_id,
			row_html: row_html
		});
	}
});