Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.AgentChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#agent_chat_section');
		this.chatsWrapper = $('#agent_chats_wrapper');
		this.setSectionElement($('<section id="agent_chat_outline"></section>'));

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
				this.setHasInitialLoaded();
				this.contentEl.html(data.section_html);
			}
		});
	},

	_initMessageHandlers: function() {
		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent_chat.new-message');
		DeskPRO_Window.getMessageChanneler().subscribeChannel('agent.new-agent-online');

		DeskPRO_Window.getMessageBroker().addMessageListener('agent_chat.new-message', this.showNewMessage.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.new-agent-online', (function(info) {
			var agent_id = info.agent_id;
			this.addOnlineAgent.bind(agent_id);
		}).bind(this));
	},

	_initInterface: function() {
		this.panelEl = $('#agent_chat_panel');
		this.onlineListEl = $('#agent_online_list');
		this.offlineListEl = $('#agent_offline_list');
		this.onlineCountEl = $('#chat_online_count');

		$('.show-offline-opt', this.panelEl).click(function() {
			if ($(this).is(':checked')) {
				$('#agent_chat_panel').addClass('show-offline');
			} else {
				$('#agent_chat_panel').removeClass('show-offline');
			}
		});

		this.panelEl.click(function(ev) {
			// dont bubble to doc which will close the panel again
			ev.stopPropagation();
		});

		$('.show-section', this.panelEl).click(function() {
			DeskPRO_Window.switchToSection('agent_chat_section');
		});

		$('#agent_chat_section').click((function(ev) {
			ev.stopPropagation();
			this.panelEl.toggleClass('open');
		}).bind(this));
		$('body, #agent_chat_panel .close-trigger').click((function() {
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

				this._initDemo();
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

	_initDemo: function() {
		// TODO [UI demo]
		// Show example chat window for the one online agent
		var onlineAgentLi = $('li:not(.no-agents):first', this.onlineListEl);
		console.log(onlineAgentLi);
		
		var chatData = {
			author_id: onlineAgentLi.data('agent-id'),
			author_name: onlineAgentLi.data('agent-name'),
			author_short_name: onlineAgentLi.data('agent-short-name'),
			author_picture: onlineAgentLi.data('picture-url'),
			author_picture_sizable: onlineAgentLi.data('picture-url-sizable')
		};

		console.log(chatData);

		var messages = [
			'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
			'Proin venenatis, dui vitae congue pretium, enim tellus pharetra ante, vel laoreet purus felis sed orci',
			'Ut bibendum ipsum sed arcu gravida at tristique risus congue',
			'Sed in auctor arcu. Sed nec felis massa, id pulvinar augue',
			'Ut vel nulla sit amet ante pharetra dictum id at nunc',
			'Fusce est est, vestibulum ac ultrices vel, pharetra eget purus',
			'Aliquam mattis ullamcorper laoreet. Fusce facilisis rhoncus rhoncus',
			'Maecenas pellentesque sollicitudin lectus, sed venenatis augue adipiscing ut.',
			'Etiam eget odio dui. Mauris urna odio, gravida tincidunt aliquam nec, aliquet vitae ipsum',
			'In tortor sapien, accumsan vel aliquet eget, egestas quis dui',
			'In id ante eget nisi posuere varius',
			'Fusce gravida, enim sit amet faucibus semper, dui nisl scelerisque mauris, non ultricies sem justo nec magna'
		];

		Array.each(messages, function(msg, i) {
			if (i % 2 == 0) {
				chatData.message = msg;
				this.showNewMessage(chatData);
			} else {
				this.showMyMessage(onlineAgentLi.data('agent-id'), msg);
			}
		}, this);
	},

	//#########################################################################
	//# Online agent handling
	//#########################################################################

	addOnlineAgent: function(agent_id) {

		// Ignore ourselves
		if (DESKPRO_PERSON_ID && agent_id == DESKPRO_PERSON_ID) {
			return;
		}

		var origLi = $('.agent-' + agent_id, this.offlineListEl);

		if (!origLi.length) {
			console.warn('No agent element for %i', agent_id);
			return;
		}

		// Make sure they aren't already there (ie logged out/logged in before we could see theyre gone)
		if ($('.agent-' + agent_id, this.onlineListEl).length) {
			return;
		}

		var li = origLi.clone();
		this.onlineListEl.append(li);

		// Offline one is hidden now
		origLi.hide();

		var countInt = parseInt(this.onlineCountEl.html());
		countInt++;
		this.onlineCountEl.html(countInt);

		$('li.no-agents', this.onlineListEl).hide();
	},

	removeOnlineAgent: function(agent_id) {
		var li = $('.agent-' + agent_id, this.onlineListEl);
		var offlineLi = $('.agent-' + agent_id, this.offlineListEl);

		if (!li.length) {
			return;
		}

		li.remove();

		// Show them in offline again
		offlineLi.show();
		
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
			var agentLi = $('.agent-' + data.author_id, this.offlineListEl);
			if (agentLi.length) {
				data.author_picture = agentLi.data('picture-url-sizable').replace('{SIZE}', 20);
			}
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