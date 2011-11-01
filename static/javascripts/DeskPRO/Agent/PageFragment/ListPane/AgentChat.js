Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.AgentChat = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initializeProperties: function() {
		this.TYPENAME = 'agentchat_list';
	},

	initPage: function(el) {
		this.el = el;

		this.enableHighlightOpenRows('agentchat', 'conversation_id', '.row-item.convo-');
	}
});
