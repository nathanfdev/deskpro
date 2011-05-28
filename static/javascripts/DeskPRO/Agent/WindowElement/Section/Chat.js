Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.Kb = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		this.messageChanneler.subscribeChannel('chat.new-chat');
		this.messageChanneler.subscribeChannel('chat.new-message');
		this.getMessageBroker().addMessageListener('chat.new-chat', this.initNewChat.bind(this));
		this.getMessageBroker().addMessageListener('chat.new-message', this.showNewmessage.bind(this));
	},

	initNewChat: function(data) {
		// TODO finish ui

		console.log('New chat: %o', data);

		var newContainer = $.tmpl('chat_conversation', data);
		$(document).append(newContainer);

		var self = this.
		$('textarea').onkeypress(function(ev) {
			if (ev.keyCode == 21) {
				self.sendMessage(data.conversation_id, $(this).val())
			}
		});
	},

	showNewMessage: function(data) {

		console.log('New message: %o', data);

		var container = $('#chat_conversation_' + data.conversation_id);
		var newMessage = $.tmpl('chat_message', data);

		$('.messages', container).append(newMessage);
	},

	sendMessage: function(conversation_id, message)
	{
		message = message.trim();
		
		var data = []
		data.push({
			name: 'message',
			value: message
		});

		$.ajax({
			url: BASE_URL + 'agent/chat/' + conversation_id + '/send-message.json',
			data: data,
			context: this,
			success: function(data) {
				
			}
		});
	}
});