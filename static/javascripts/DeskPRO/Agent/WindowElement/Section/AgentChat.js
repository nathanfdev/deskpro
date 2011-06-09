Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.AgentChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#agent_chat_section');
		this.chatsWrapper = $('#agent_chats_wrapper');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		$('#agent_chat_conversation').template('agent_chat_conversation');
		$('#agent_chat_message').template('agent_chat_message');
		$('#agent_chat_message_me').template('agent_chat_message_me');

		this._initMessageHandlers();
		this._initInterface();
	},

	onShow: function() {
		$.ajax({
			url: BASE_URL + 'agent/agent-chat/get-section-data.json',
			context: this,
			success: function(data) {
				this.contentEl.html(data.section_html);
			}
		});
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

		$('.show-section', this.panelEl).click(function() {
			DeskPRO_Window.switchToSection('agent_chat_section');
		});

		$('#agent_chat_section').click((function(ev) {
			ev.stopPropagation();
			this.panelEl.toggleClass('open');
		}).bind(this));
		$('body').click((function() {
			this.panelEl.removeClass('open');
			$('> section', this.chatsWrapper).removeClass('open');
		}).bind(this));

		$.ajax({
			url: BASE_URL + 'agent/agent-chat/get-online-agents.json',
			context: this,
			success: function(data) {
				if (data.online_agents) {
					Array.each(data.online_agents, function(info) {
						this.addOnlineAgent(info);
					}, this);
				}
			}
		});

		// stop propagation for clicks on the chat wrapper
		// so it dorsnt bubble up and close the open chat window
		this.chatsWrapper.click(function(ev) {
			ev.stopPropagation();
		});

		var self = this;
		this.onlineListEl.delegate('li', 'click', function (ev) {
			ev.stopPropagation();

			var agent_id     = $(this).data('agent-id');
			var agent_name   = $(this).data('agent-short-name');
			var picture_url  = $(this).data('picture-url');

			self.addChatBox(agent_name, agent_id, picture_url);
			self.openChatBox(agent_id);

			// And close the online list
			self.panelEl.removeClass('open');
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

		// Make sure they aren't already there (ie logged out/logged in before we could see theyre gone)
		if ($('agent-' + data.agent_id, this.onlineListEl).length) {
			return;
		}

		var html = '<li class="agent-' + data.agent_id + '" data-agent-id="' + data.agent_id + '" data-agent-short-name="'+ data.agent_short_name + '" data-picture-url="' + data.picture_url + '">';
		if (data.picture_url) {
			html += '<img src="' + data.picture_url + '" />';
		}
		html +=  data.agent_name + '</li>';
		this.onlineListEl.append(html);

		var countInt = parseInt(this.onlineCountEl.html());
		countInt++;
		this.onlineCountEl.html(countInt);

		$('li.no-agents', this.onlineListEl).hide();
	},

	removeOnlineAgent: function(data) {
		var el = $('.agent-' + data.agent_id, this.onlineListEl);

		if (!el.length) {
			return;
		}

		el.remove();
		
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

	addChatBox: function(name, person_id, picture_url) {

		// Make sure it doesnt already exist
		var exist = $('#agent_chat_conversation_' + person_id);
		if (exist.length) {
			return;
		}

		var newContainer = $.tmpl('agent_chat_conversation', {
			to_agent_name: name,
			to_agent_id: person_id,
			to_agent_picture: picture_url
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
		nav.click(function(ev) {
			ev.stopPropagation();
			if (newContainer.is('.open')) {
				newContainer.removeClass('open');
			} else {
				self.openChatBox(person_id);
			}
		});

		$('.close-trigger', nav).click(function(ev) {
			ev.stopPropagation();
			newContainer.remove();
		});
	},

	getChatBox: function(person_id) {
		var el = $('#agent_chat_conversation_' + person_id);
		return el;
	},

	openChatBox: function(person_id) {
		var el = this.getChatBox(person_id);

		// Already open
		if (el.is('.open')) {
			return;
		}

		// Close others
		$('> section', this.chatsWrapper).removeClass('open');

		el.addClass('open');
		el.removeClass('new-message');
		$('textarea', el).focus();
	},

	getChatContainerForMessage: function(data) {
		var container = $('#agent_chat_conversation_' + data.author_id);

		if (!container.length) {
			this.addChatBox(data.author_short_name, data.author_id, data.author_picture);
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
			author_picture: data.author_picture,
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
			url: BASE_URL + 'agent/agent-chat/send-agent-message/' + to_agent_id,
			data: data,
			context: this,
			contentType: 'json'
		});
	}
});