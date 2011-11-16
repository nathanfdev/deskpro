Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.AgentEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	person_id: 0,
	initialize: function(person_id) {
		this.person_id = person_id;
	},

	initPage: function() {
		var self = this;
		$('.save-trigger').on('click', function() {
			$('form:first').submit();
		});
		$('.cancel-trigger').on('click', function() {
			self.closeThisPopout();
		});
	},

	updateParent: function(new_row) {
		var parent_win = this.getOpenerDeskPRO();
		if (!parent_win) return;

		var info = {
			personId: this.person_id,
			newHtml: new_row
		};

		parent_win.getMessageBroker().sendMessage('agent.list.change', info);
	}
});
