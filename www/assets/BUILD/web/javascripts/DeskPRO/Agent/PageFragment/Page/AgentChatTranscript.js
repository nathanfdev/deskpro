Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.AgentChatTranscript = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.TYPENAME = 'agentchat';
	},

	initPage: function(el) {

		var format = DeskPRO.Agent.Widget.AgentChatWin.prototype.formatMessage;
		$('.message-content', el).each(function(){
			this.innerHTML = format(this.innerHTML, true);
		});
	}
});
