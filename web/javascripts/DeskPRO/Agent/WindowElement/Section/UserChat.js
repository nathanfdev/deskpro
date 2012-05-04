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

		this.openingChatTimeout = {};
	},

	_initSection: function(data) {
		if(this.hasSectionInitialised) {
			this._lastLoaded = new Date();
			this.filterGroupEditor.destroy();
			this.contentEl.empty();
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
        this.handleUpdateCounts();
	},

	refreshFilterGrouping: function(filterId) {
		var self = this;
		this.groups[filterId] = this.getGroupingVar(filterId);

		$.ajax(
			{
				type: 'POST',
				url: BASE_URL + 'agent/chat/group-count.json',
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
		DeskPRO_Window.getSectionData('chat_section', this._initSection.bind(this));
	},

	onHide: function() {
	},

	handleUpdateCounts: function(data) {
		var count = parseInt($('#userchat_deplist_0_counter').text()) + parseInt($('#userchat_list_allagents_counter').text());
		this.updateBadge(count);
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
		var el = $('#userchat_list_' + (id != '0'?id:'allagents') + '_counter');
		var newCount = DeskPRO_Window.util.modCountEl(el, op, count);

		if (id != '0') {
			var row = el.closest('li');

			if(newCount) {
				row.show();
			}
			else {
				row.hide();
			}

			this.modListingCount(0, op, count);
		}
		else {
			this.handleUpdateCounts();
		}
	},

	modDepListingCount: function(id, op, count) {
		var el = $('#userchat_deplist_' + id + '_counter');
		var newCount = DeskPRO_Window.util.modCountEl(el, op, count);

		if(id != '0') {
			var row = el.closest('li');

			if (el.data('parentid')) {
				this.modDepListingCount(el.data('parentid'), op, count);
			}
			else {
				this.modDepListingCount(0, op, count);
			}

			if(newCount) {
				row.show();
			}
			else {
				row.hide();
			}
		}
		else {
			this.handleUpdateCounts();
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

		this.handleUpdateCounts();
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

		// See handleNewChat comment about this
		if (this.openingChatTimeout[data.conversation_id]) {
			window.clearTimeout(this.openingChatTimeout[data.conversation_id]);
			delete this.openingChatTimeout[data.conversation_id];
		}

		this.handleUpdateCounts();
	},

	handlePartsUpdated: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat_user_agent.chat-parts-updated-' + data.conversation_id, data);
	},

	handleNewChat: function(data) {
		if (!data.agent_id) {
			if (!this.dismissedChats[data.conversation_id]) {
				var info_line = [];
				this.showNewChatAlert(data);
			}
		} else {
			if (data.agent_id == DESKPRO_PERSON_ID && !this.isChatOpen(data.conversation_id)) {
				var self = this;
				// Its possible we opened the chat, then closed+unassigned ourselves before the last
				// poll was done. This would create a series of client messages like:
				// - Assigned (from opening the chat)
				// - Unassigned (from leaving)
				// Then the CM would be delievered, and right here we'd see the assigned-to-me message
				// and attempt to re-open the chat we just closed.
				// So we timeout so we can add some logic to see if the chat was closed before running this,
				// this is just a easy way to process CM messages before running the open (since they're executed in sequence)
				this.openingChatTimeout[data.conversation_id] = window.setTimeout(function() {
					DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
					delete delete self.openingChatTimeout[data.conversation_id];
				}, 1000);
			}
		}

		if (!data.agent_id) {
			this.modDepListingCount(data.department_id, '+');
		}

		this.handleUpdateCounts();
	},

	handleUnassignedChat: function(data) {
		this.modListingCount(data.old_agent_id, '-');

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

		// See handleNewChat comment about this
		if (data.old_agent_id == DESKPRO_PERSON_ID && this.openingChatTimeout[data.conversation_id]) {
			window.clearTimeout(this.openingChatTimeout[data.conversation_id]);
			delete this.openingChatTimeout[data.conversation_id];
		}

		this.handleUpdateCounts();
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

		// See handleNewChat comment about this
		if (data.agent_id == DESKPRO_PERSON_ID && !this.isChatOpen(data.conversation_id)) {
			this.openingChatTimeout[data.conversation_id] = window.setTimeout(function() {
				DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
				delete delete self.openingChatTimeout[data.conversation_id];
			}, 1000);
		}

		this.handleUpdateCounts();
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
