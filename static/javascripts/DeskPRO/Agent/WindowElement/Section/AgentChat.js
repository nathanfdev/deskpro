Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.AgentChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.chatsWrapper = $('#agent_chats_wrapper');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent_chat.new-message');
		DeskPRO_Window.getMessageBroker().addMessageListener('agent_chat.new-message', this.showNewMessage.bind(this));

		$('#agent_chat_conversation').template('agent_chat_conversation');
		this.initNewChat({
			conversation_id: 'test'
		});
		this.initNewChat({
			conversation_id: 'test2'
		});
	},

	countChats: function() {
		return $('> section.agent-chat', this.chatsWrapper).length;
	},

	initNewChat: function(data) {
		console.log('New chat: %o', data);

		var newContainer = $.tmpl('agent_chat_conversation', data);

		// Modify position of button if there are others
		var lastChat = $('> section.agent-chat:last', this.chatsWrapper);
		if (lastChat.length) {
			var leftPos = lastChat.position().left + $('> nav', lastChat).outerWidth() + 8;
			newContainer.css('left', leftPos);
		}

		this.chatsWrapper.append(newContainer);

		newContainer.addClass('new-message');

		var self = this;
		$('textarea', newContainer).keypress(function(ev) {
			// Enter, but not when meta key (alt, ctrl etc) are pressed
			if (ev.keyCode == 13 && !ev.metaKey) {
				self.sendMessage(data.conversation_id, $(this).val().trim())
			}
		});

		$('> nav', newContainer).click(function() {
			newContainer.toggleClass('open');
			if (newContainer.is('.open')) {
				newContainer.removeClass('new-message');
			}
		});
	},

	getChatContainer: function(data) {
		var container = $('#agent_chat_conversation_' + data.conversation_id);

		if (!container.length) {
			this.initNewChat(data);
		}

		container = $('#agent_chat_conversation_' + data.conversation_id);
		return container;
	},

	showNewMessage: function(data) {

		console.log('New message: %o', data);

		var container = this.getChatContainer();
		var newMessage = $.tmpl('agent_chat_message', data);

		$('.messages', container).append(newMessage);
	},

	sendMessage: function(conversation_id, message) {
		message = message.trim();
		
		var data = [];
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