Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

DeskPRO.Agent.WindowElement.Section.UserChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	init: function() {
		this.buttonEl = $('#chat_section');
		this.setSectionElement($('<section id="chat_outline"></section>'));
		this.groups = {};
		this.urlFragmentName = 'userchat';
		this.hasSectionInitialised = false;

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
			this._initSection(data);
		}).bind(this));
	},

	_initSection: function(data) {
		if(this.hasSectionInitialised) {
			return;
		}

		this.hasSectionInitialised = true;
		var self = this;

		this.setHasInitialLoaded();
		this.contentEl.html(data.section_html);

		if('filterGroupEditor' in this)
			this.filterGroupEditor.destroy();

		this.filterGroupEditor = new DeskPRO.Agent.Widget.FilterGroupEditor({
			containerElement: '#chat_outline',
			listElement: '#chats_outline_sys_filters',
			triggerElement: '#chat_filter_launch_editor',
			controlElement: '#chat_filter_group_editor',
			useIntId: false,
			onGroupingChanged: function(data) {
				self.refreshFilterGrouping(data, self);
			},
			onSetMarginTop: function(evData) {
				evData.marginTop = $('#chats_outline_sys_filters').position().top;
			}
		});
		this.filterGroupEditor._initControl();
		this.refreshFilterGrouping(data, this);
		this.updateGroupingVars();

		this._lastLoaded = new Date();
        this.updateBadge($('#userchat_deplist_0_counter').text());
	},

	refreshFilterGrouping: function(filterId) {
		var self = this;
		this.groups[filterId] = this.getGroupingVar(filterId);

		$.ajax(
			{
				type: 'POST',
				url: BASE_URL + 'agent/chat/filter/group-count.json',
				data: { filters: this.groups },
				dataType: 'json',
				success: function(data) {
					self.updateFilterGrouping(data, self);
				}
			}
		);
	},

	updateFilterGrouping: function(data, self) {
		var container = $('#chats_outline_sys_filters');

		for(filterId in data) {
			var element = $('.filter-' + filterId + ' .sub-group', container);
			element.html(data[filterId]);

			if(data[filterId])
				element.show();
			else
				element.hide();
		}

		self.filterGroupEditor.updatePositions();
	},

	getGroupingVar: function(filterId) {
		return $('#chat_filter_group_editor .filter-' + filterId + ' .field-option').val();
	},

	updateGroupingVars: function() {
		for(filterId in this.groups) {
			$('#chat_filter_group_editor .filter-' + filterId + ' .field-option').val(this.groups[filterId]);
		}
	},

	onShow: function() {
		this._lastLoaded = new Date();
		DeskPRO_Window.getSectionData('chat_section', this._initSection.bind(this));
	},

	onHide: function() {
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
		DeskPRO_Window.util.modCountEl($('#userchat_list_allagents_counter'), op, count);

		if (id != '0') {
			if (newCount < 1) {
				$('#userchat_list_all').hide();
				if (!$('#userchat_deplist_all').is(':visible')) {
					$('#userchat_no_chats').show();
				}
			} else {
				$('#userchat_list_all').show();
				$('#userchat_no_chats').hide();
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

		if (id != '0') {
			if (newCount < 1) {
				$('#userchat_deplist_all').hide();
				if (!$('#userchat_list_all').is(':visible')) {
					$('#userchat_no_chats').show();
				}
			} else {
				$('#userchat_deplist_all').show();
				$('#userchat_no_chats').hide();
			}
		}

        if(id == 0) {
            this.updateBadge(count);
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
		var alertEl = $(data.html);
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		var audio = $('audio', alertEl).get(0);
		var self = this;

		var secEl = alertEl.find('span.wait-timer');
		function up() {
			var secs = parseInt(secEl.data('time'));
			secs++;
			secEl.data('time', secs);

			if (secs > 60) {
				secEl.text((Math.floor(secs / 60)) + " minutes");
			} else {
				secEl.text(secs + " seconds");
			}
		};
		var waitTimer = window.setInterval(up, 1000);

		$('.dismiss-trigger', alertEl).on('click', function() {
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
			self.dismissedChats[data.conversation_id] = true;
			window.clearTimeout(waitTimer);
		});
		$('.accept-trigger', alertEl).on('click', function(ev) {
			ev.stopPropagation();
			DeskPRO_Window.runPageRouteFromElement(this);
			if (audio) {
				audio.pause();
			}
			alertEl.remove();
			window.clearTimeout(waitTimer);
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id);
	}
});
