Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.UserChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		this.urlFragmentName = 'userchat';

		$('#new_user_chat_alert').template('new_user_chat_alert');
		$('#new_user_chat_alert_message').template('new_user_chat_alert_message');
		$('#added_part_user_chat_alert').template('added_part_user_chat_alert');
		$('#user_chat_newmsg_sound').template('user_chat_newmsg_sound');

		this.dismissedChats = {};

		this._initMessageHandlers();
	},

	onShow: function() {

		this.setHasInitialLoaded();

		$.ajax({
			url: BASE_URL + 'agent/chat/get-section-data.json',
			context: this,
			success: function(data) {
				this.contentEl.html(data.section_html);
			}
		});
	},

	handleUpdateCounts: function(data) {
		$('#chat_outline .agent-chat-count').hide();

		$('#userchat_navitem_0 .list-counter').html('0');

		if (!data.counts) {
			return;
		}

		var unassigned = 0;
		Object.each(data.counts, function (count, agent_id) {
			if (agent_id == '0') {
				unassigned = count;
			}
			$('#userchat_navitem_'+agent_id+' .list-counter').html(count);
			$('#userchat_navitem_'+agent_id).show();
		});

		this.updateBadge(unassigned);
	},

	isChatOpen: function(convoId) {
		var chatTabs = DeskPRO_Window.getTabWatcher().findTabType('userchat');
		var isOpen = false;
		Array.each(chatTabs, function(tab) {
			if (parseInt(tab.page.meta.conversation_id) == parseInt(convoId)) {
				isOpen = true;
				return false;
			}
		}, this);

		return isOpen;
	},

	_initMessageHandlers: function() {
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.new', this.handleNewChat, this);
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.reassigned', this.handleReassignedChat, this);
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.unassigned', this.handleUnassignedChat, this);
		DeskPRO_Window.getMessageChanneler().subscribeChannel('chat.ended', this.handleChatEnded, this);
	},

	modListingCount: function(id, op, count) {
		var el = $('#userchat_list_' + id + '_counter');
		var newCount = DeskPRO_Window.util.modCountEl(el, op, count);

		if (id != '0') {
			if (newCount < 1) {
				el.closest('li').hide();
			} else {
				el.closest('li').show();
			}
		}
	},

	handleChatEnded: function(data) {
		$('#new_user_chat_alert_' + data.conversation_id).remove();
		this.modListingCount(data.agent_id, '-');
		DeskPRO_Window.getMessageBroker().sendMessage('chat_convo.' + data.conversation_id + '.ended', data);
	},

	handlePartsUpdated: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat_user_agent.chat-parts-updated-' + data.conversation_id, data);
	},

	handleNewChat: function(data) {
		this.modListingCount(data.agent_id, '+');
		if (!data.agent_id) {
			if (!this.dismissedChats[data.conversation_id]) {
				this.showNewChatAlert(data, {
					name: data.author_name,
					message: data.initial_message || data.subject_line
				});
			}
		} else {
			if (data.agent_id == DESKPRO_PERSON_ID && !this.isChatOpen(data.conversation_id)) {
				DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
			}
		}
	},

	handleUnassignedChat: function(data) {
		this.modListingCount(data.old_agent_id, '-');
		this.modListingCount(0, '+');

		// Means we were the agent, but unassassigned ourselves
		if (data.old_agent_id && data.old_agent_id == DESKPRO_PERSON_ID) {
			this.dismissedChats[data.conversation_id] = true;
		}

		if (!this.dismissedChats[data.conversation_id]) {
			this.showNewChatAlert(data, {
				name: data.author_name,
				message: data.subject_line
			});
		}

		DeskPRO_Window.getMessageBroker().sendMessage('chat_convo.' + data.conversation_id + '.unassigned', data);
	},

	handleReassignedChat: function(data) {
		this.modListingCount(data.old_agent_id, '-');
		this.modListingCount(data.agent_id, '+');

		// Means we were the agent, but unassassigned ourselves
		if (data.old_agent_id && data.old_agent_id == DESKPRO_PERSON_ID) {
			this.dismissedChats[data.conversation_id] = true;
		}

		$('#new_user_chat_alert_' + data.conversation_id).remove();
		DeskPRO_Window.getMessageBroker().sendMessage('chat_convo.' + data.conversation_id + '.reassigned', data);

		if (data.agent_id == DESKPRO_PERSON_ID && !this.isChatOpen(data.conversation_id)) {
			DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
		}
	},

	handleEndedChat: function(data) {
		this.modListingCount(data.agent_id, '-');
		$('#new_user_chat_alert_' + data.conversation_id).remove();

		if (this.dismissedChats[data.conversation_id]) {
			delete this.dismissedChats[data.conversation_id];
		}
	},

	handleAddedAsPart: function(data) {

		console.log(data);

		// Make suer we arent already viewing it
		var checkEl = $('#deskpro_tabstrip li.user_chat_tab_' + data.conversation_id);
		if (checkEl.length) {
			return;
		}

		var conversation_id = data.conversation_id;
		var initial_message = {
			name: data.author_name,
			message: data.message
		};

		var alertEl = $.tmpl('added_part_user_chat_alert');
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		$('.dismiss-trigger', alertEl).click(function() {
			alertEl.remove();
		});
		$('.accept-trigger', alertEl).click(function(ev) {
			ev.stopPropagation();
			DeskPRO_Window.runPageRouteFromElement(this);
			alertEl.remove();
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);

		if (initial_message) {
			var messageEl = $.tmpl('new_user_chat_alert_message', initial_message);
			$('div.messages', alertEl).append(messageEl).scrollTop(10000);
		}
	},

	showNewChatAlert: function(data) {
		var conversation_id = data.conversation_id;
		var alertEl = $.tmpl('new_user_chat_alert', data);
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		var audio = $('audio', alertEl).get(0);
		var self = this;

		$('.dismiss-trigger', alertEl).click(function() {
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
			self.dismissedChats[data.conversation_id] = true;
		});
		$('.accept-trigger', alertEl).click(function(ev) {
			ev.stopPropagation();
			DeskPRO_Window.runPageRouteFromElement(this);
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);
	}
});
