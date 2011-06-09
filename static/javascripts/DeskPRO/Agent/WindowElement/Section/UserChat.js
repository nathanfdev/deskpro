Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.UserChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		$('#new_user_chat_alert').template('new_user_chat_alert');
		$('#new_user_chat_alert_message').template('new_user_chat_alert_message');

		this._initMessageHandlers();
	},

	_initMessageHandlers: function() {
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.new-chat');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.message');

		DeskPRO_Window.getMessageBroker().addMessageListener('chat.message', this.handleNewMessage.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.new-chat', this.handleNewChat.bind(this));
	},

	handleNewMessage: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat.new-message-' + data.conversation_id, data);
	},

	handleNewChat: function(data) {
		this.showNewChatAlert(data.conversation_id, {
			name: data.author_name,
			message: data.message
		});
	},

	showNewChatAlert: function(conversation_id, initial_message) {
		var alertEl = $.tmpl('new_user_chat_alert');
		alertEl.appendTo('body');

		$('.dismiss-trigger', alertEl).click(function() {
			alertEl.remove();
		});
		$('.accept-trigger', alertEl).click(function() {
			DeskPRO_Window.runPageRouteFromElement(this);
			alertEl.remove();
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);

		if (initial_message) {
			var messageEl = $.tmpl('new_user_chat_alert_message', initial_message);
			$('div.messages', alertEl).append(messageEl).scrollTop(10000);
		}
	}
});