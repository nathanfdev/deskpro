Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.AgentChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.chatsWrapper = $('#agent_chats_wrapper');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		$('#agent_chat_conversation').template('agent_chat_conversation');
		$('#agent_chat_message').template('agent_chat_message');
		$('#agent_chat_message_me').template('agent_chat_message_me');

		this._initMessageHandlers();
		this._initInterface();
	},

	_initMessageHandlers: function() {
		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent_chat.new-message');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent.new-agent-online');

		DeskPRO_Window.getMessageBroker().addMessageListener('agent_chat.new-message', this.showNewMessage.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.new-agent-online', this.addOnlineAgent.bind(this));
	},

	_initInterface: function() {
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

		$.ajax({
			url: BASE_URL + 'agent/chat/get-online-agents.json',
			context: this,
			success: function(data) {
				if (data.online_agents) {
					Array.each(data.online_agents, function(info) {
						this.addOnlineAgent(info);
					}, this);
				}
			}
		});

		var self = this;
		this.onlineListEl.delegate('li', 'click', function (ev) {
			var agent_id = $(this).data('agent-id');
			var agent_name = $(this).html();

			self.addChatBox(agent_name, agent_id);
		});
	},

	//#########################################################################
	//# Online agent handling
	//#########################################################################

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


	//#########################################################################
	//# Chatbox handling
	//#########################################################################

	addChatBox: function(name, person_id) {

		var newContainer = $.tmpl('agent_chat_conversation', {
			to_agent_name: name,
			to_agent_id: person_id
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
				var msg = $(this).val().trim();
				$(this).val('');

				self.sendMessage(person_id, msg);
				self.showMyMessage(person_id, msg);
			}
		});

		var nav = $('> nav', newContainer);
		nav.click(function() {
			newContainer.toggleClass('open');
			if (newContainer.is('.open')) {
				newContainer.removeClass('new-message');
			}
		});

		$('.close-trigger', nav).click(function(ev) {
			ev.stopPropagation();
			newContainer.remove();
		});
	},

	getChatContainerForMessage: function(data) {
		var container = $('#agent_chat_conversation_' + data.author_id);

		if (!container.length) {
			this.addChatBox(data.author_name, data.author_id);
		}

		container = $('#agent_chat_conversation_' + data.author_id);

		return container;
	},

	getChatContainer: function(to_agent_id) {
		var container = $('#agent_chat_conversation_' + to_agent_id);

		if (!container.length) {
			console.warn("No chat box for %i", to_agent_id);
			return null;
		}

		return container;
	},

	
	//#########################################################################
	//# Chat message handling
	//#########################################################################

	showNewMessage: function(data) {

		var container = this.getChatContainerForMessage(data);
		var newMessage = $.tmpl('agent_chat_message', {
			author_id: data.author_id,
			author_name: data.author_name,
			message: data.message
		});

		$('.messages-container:first', container).append(newMessage).scrollTop(100000);
	},

	showMyMessage: function(to_agent_id, msg) {
		var container = this.getChatContainer(to_agent_id);

		var newMessage = $.tmpl('agent_chat_message_me', { message: msg });

		$('.messages-container:first', container).append(newMessage).scrollTop(100000);
	},

	sendMessage: function(to_agent_id, message) {

		var data = [];
		data.push({
			name: 'content',
			value: message
		});

		$.ajax({
			url: BASE_URL + 'agent/chat/send-agent-message/' + to_agent_id,
			data: data,
			context: this,
			contentType: 'json'
		});
	}
});