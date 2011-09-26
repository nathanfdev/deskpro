Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.AgentChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#agent_chat_section');
		this.chatsWrapper = $('#agent_chats_wrapper');
		this.setSectionElement($('<section id="agent_chat_outline"></section>'));

		$('#agent_chat_conversation').template('agent_chat_conversation');
		$('#agent_groupchat_conversation').template('agent_groupchat_conversation');
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

		DeskPRO_Window.getMessageBroker().addMessageListener('agent_chat.new-message', this.newIncomingMessage, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.new-agent-online', function(info) {
			var agent_id = info.agent_id;
			this.addOnlineAgent.bind(agent_id);
		}, this);
	},

	_initInterface: function() {
		this.panelEl = $('#agent_chat_panel');
		this.onlineListEl = $('#agent_online_list');
		this.offlineListEl = $('#agent_offline_list');
		this.onlineCountEl = $('#chat_online_count');
		this.agentTeamList = $('#agent_team_list');

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
			this.close();
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

		var openChatFn = function (ev) {
			ev.stopPropagation();
			var agent_id = $(this).data('agent-id');
			self.newChatWindow([agent_id]);
		};

		this.onlineListEl.delegate('li', 'click', openChatFn);
		this.offlineListEl.delegate('li', 'click', openChatFn);
		this.agentTeamList.delegate('li', 'click', function(ev) {
			ev.stopPropagation();
			var agentIds = $(this).data('member-ids');
			console.log(agentIds);
			agentIds = agentIds.split(',');
			console.log(agentIds);

			self.newChatWindow(agentIds);
		});

		// Agents/teams tabs
		this.listTabs = new DeskPRO.UI.SimpleTabs({
			triggerElements: $('#agent_chat_panel_listviews > li')
		});
	},

	close: function() {
		this.panelEl.removeClass('open');
		$('> section', this.chatsWrapper).removeClass('open');
	},

	newChatWindow: function(agent_ids) {

		var chatWin = DeskPRO.Agent.Widget.AgentChatWin_FindAgents(agent_ids);
		if (!chatWin) {
			chatWin = new DeskPRO.Agent.Widget.AgentChatWin({
				agentIds: agent_ids
			});
		}

		this.close();

		chatWin.open();
	},

	newIncomingMessage: function(info) {
		var chatWin = DeskPRO.Agent.Widget.AgentChatWin_Find(info.conversation_id);
		if (!chatWin) {
			chatWin = new DeskPRO.Agent.Widget.AgentChatWin({
				convoId: info.conversation_id,
				agentIds: info.participant_ids
			});
		}

		chatWin.showMessage(info.author_id, info.message);
		chatWin.open();
	},

	//#########################################################################
	//# Online agent handling
	//#########################################################################

	addOnlineAgent: function(agent_id) {

		if (agent_id.agent_id) {
			agent_id = agent_id.agent_id;
		}

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
	}
});
