Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.AgentChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.chatsWrapper = $('#agent_chats_wrapper');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent_chat.new-message');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent.new-agent-online');

		DeskPRO_Window.getMessageBroker().addMessageListener('agent_chat.new-message', this.showNewMessage.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.new-agent-online', this.addOnlineAgent.bind(this));

		this.panelEl = $('#agent_chat_panel');
		this.onlineListEl = $('#agent_online_list');
		this.onlineCountEl = $('#chat_online_count');

		$('#chat_section').click((function(ev) {
			ev.stopPropagation();
			this.panelEl.toggleClass('open');
		}).bind(this));
		$('body').click((function() {
			this.panelEl.removeClass('open');
		}).bind(this));

		$('#agent_chat_conversation').template('agent_chat_conversation');
		this.addChatBox('Jane', '123');

		$.ajax({
			url: BASE_URL + 'agent/chat/get-online-agents.json',
			data: data,
			context: this,
			success: function(data) {
				if (data.online_agents) {
					Array.each(data.online_agents, function(info) {
						this.addOnlineAgent(info);
					}, this);
				}
			}
		});
	},

	addOnlineAgent: function(data) {

		// Ignore ourselves
		if (DESKPRO_PERSON_ID && data.agent_id == DESKPRO_PERSON_ID) {
			return;
		}

		var html = '<li class="agent-' + data.agent_id + '" data-agent-id="' + data.agent_id + '">' + data.agent_name + '</li>';
		this.onlineListEl.append(html);

		var countInt = parseInt(this.onlineCountEl.html());
		countInt++;
		this.onlineCountEl.html(countInt);

		$('li.no-agents', this.onlineListEl).hide();
	},

	removeOnlineAgent: function(data) {
		$('.agent-' + data.agent_id, this.onlineListEl).remove();

		var countInt = parseInt(this.onlineCountEl.html());
		countInt--;
		this.onlineCountEl.html(countInt);

		if (countInt < 1) {
			$('li.no-agents', this.onlineListEl).show();
		}
	},

	countChats: function() {
		return $('> section.agent-chat', this.chatsWrapper).length;
	},

	addChatBox: function(name, person_id) {

		var newContainer = $.tmpl('agent_chat_conversation', {
			author_name: name,
			author_id: person_id
		});

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