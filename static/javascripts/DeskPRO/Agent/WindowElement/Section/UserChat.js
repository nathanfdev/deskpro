Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.UserChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.setSectionElement($('<section id="chat_outline"></section>'));

		this.urlFragmentName = 'userchat';

		$('#new_user_chat_alert').template('new_user_chat_alert');
		$('#invite_chat_alert').template('invite_chat_alert');
		$('#new_user_chat_alert_message').template('new_user_chat_alert_message');
		$('#added_part_user_chat_alert').template('added_part_user_chat_alert');
		$('#user_chat_newmsg_sound').template('user_chat_newmsg_sound');

		this.dismissedChats = {};

		this._initMessageHandlers();

		this.getSectionElement().on('click', '.sub-toggle', function(ev) {
			var row = $(this).closest('li');
			var sub = $('> ul.sub-group', row);
			if (sub.length) {
				if (sub.is(':visible')) {
					row.removeClass('sub-expanded');
					sub.slideUp('fast');
				} else {
					row.addClass('sub-expanded');
					sub.slideDown('fast');
				}
			}
		});

		this._lastLoaded = new Date();
		DeskPRO_Window.getSectionData('chat_section', (function(data) {
			this.setHasInitialLoaded();
			this.contentEl.html(data.section_html);
			this._lastLoaded = new Date();
		}).bind(this));
	},

	onShow: function() {

		// Dont autorefresh until at least 8 seconds
		if ((new Date()).getTime() - this._lastLoaded.getTime() < 8000) {
			return;
		}

		this._lastLoaded = new Date();
		DeskPRO_Window.getSectionData('chat_section', (function(data) {
			this.setHasInitialLoaded();
			this.contentEl.html(data.section_html);
			this._lastLoaded = new Date();
		}).bind(this));
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
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.new', this.handleNewChat, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.reassigned', this.handleReassignedChat, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.unassigned', this.handleUnassignedChat, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.ended', this.handleChatEnded, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.depchange', this.handleDepChange, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.invited', this.handleInvited, this);
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
	modDepListingCount: function(id, op, count) {
		var el = $('#userchat_deplist_' + id + '_counter');
		var newCount = DeskPRO_Window.util.modCountEl(el, op, count);

		var row = el.closest('li');
		if (row.parent().is('.sub-group')) {
			var parentEl = $('.list-counter', row.parent().closest('li')).first();
			DeskPRO_Window.util.modCountEl(parentEl, op, count);
		}
	},

	handleDepChange: function(data) {
		if (!data.agent_id) {
			if (data.old_department_id) {
				this.modDepListingCount(data.old_department_id, '-');
			}

			if (data.department_id) {
				this.modDepListingCount(data.department_id, '+');
			}
		}
	},

	handleChatEnded: function(data) {
		$('#new_user_chat_alert_' + data.conversation_id).remove();
		this.modListingCount(data.agent_id, '-');
		DeskPRO_Window.getMessageBroker().sendMessage('chat_convo.' + data.conversation_id + '.ended', data);

		if (!data.agent_id) {
			this.modDepListingCount(data.department_id, '-');
		}

		if (this.dismissedChats[data.conversation_id]) {
			delete this.dismissedChats[data.conversation_id];
		}
	},

	handlePartsUpdated: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat_user_agent.chat-parts-updated-' + data.conversation_id, data);
	},

	handleNewChat: function(data) {
		this.modListingCount(data.agent_id, '+');
		if (!data.agent_id) {
			if (!this.dismissedChats[data.conversation_id]) {
				var info_line = [];
				this.showNewChatAlert(data);
			}
		} else {
			if (data.agent_id == DESKPRO_PERSON_ID && !this.isChatOpen(data.conversation_id)) {
				DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
			}
		}

		if (!data.agent_id) {
			this.modDepListingCount(data.department_id, '+');
		}
	},

	handleUnassignedChat: function(data) {
		this.modListingCount(data.old_agent_id, '-');
		this.modListingCount(0, '+');

		if (data.old_agent_id) {
			this.modDepListingCount(data.department_id, '+');
		}

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

	handleInvited: function(data) {
		$('#invite_chat_alert_' + data.conversation_id).remove();
		this.showInviteAlert(data);
	},

	showInviteAlert: function(data) {
		var conversation_id = data.conversation_id;
		var alertEl = $.tmpl('invite_chat_alert', data);
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		var audio = $('audio', alertEl).get(0);
		var self = this;

		$('.dismiss-trigger', alertEl).on('click', function() {
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
		});
		$('.accept-trigger', alertEl).on('click', function(ev) {
			ev.stopPropagation();
			DeskPRO_Window.runPageRouteFromElement(this);
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);
	},

	handleReassignedChat: function(data) {
		this.modListingCount(data.old_agent_id, '-');
		this.modListingCount(data.agent_id, '+');

		if (data.agent_id && !data.old_agent_id) {
			this.modDepListingCount(data.department_id, '-');
		}

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

	showNewChatAlert: function(data) {
		var conversation_id = data.conversation_id;
		var alertEl = $.tmpl('new_user_chat_alert', data);
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		var audio = $('audio', alertEl).get(0);
		var self = this;

		$('.dismiss-trigger', alertEl).on('click', function() {
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
			self.dismissedChats[data.conversation_id] = true;
		});
		$('.accept-trigger', alertEl).on('click', function(ev) {
			ev.stopPropagation();
			DeskPRO_Window.runPageRouteFromElement(this);
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);
	}
});
