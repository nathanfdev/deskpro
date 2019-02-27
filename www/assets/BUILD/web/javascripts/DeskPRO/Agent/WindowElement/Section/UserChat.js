Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');
//
/**
 * The UserChat section also controls the chat status at the top of the window.
 */
DeskPRO.Agent.WindowElement.Section.UserChat = new Orb.Class({
	Extends: DeskPRO.Agent.WindowElement.Section.AbstractSection,

	//##################################################################################################################
	//# Init
	//##################################################################################################################

	init: function() {
		var self = this;
		this.buttonEl = $('#chat_section');
		this.setSectionElement($('<section id="chat_outline"></section>'));
		this.groups = {};
		this.urlFragmentName = 'userchat';
		this.hasSectionInitialised = false;
		this.lastOnlineUserLoad = null;
		this.lastOnlineUserCount = null;

		this.onlineUsersWrap = $('#agent_status_online_users');
		this.statusMenuOpen = false;
		this.dismissedChats = {};
		this.newChats = {};
		this.openingChatTimeout = {};
		this.refreshCountsTimeout = null;
		this.onlineAgentIds = [];

		this._initStatusMenu();
		this._initStatusMenuAgents();
		this._initStatusMenuUsers();
		this._initTemplates();
		this._initMessageHandlers();

		this._lastLoaded = new Date();
		DeskPRO_Window.getSectionData('chat_section', (function(data) {
			this._initSection(data);
		}).bind(this));

		this.refreshCountInterval = setInterval(this.handleUpdateCounts.bind(this), 2*60*1000);
	},

	_initSection: function(data) {


		var lastSelectedId = null;
		if (this.contentEl) {
			lastSelectedId = this.contentEl.find('.nav-selected').find('.list-counter').attr('id');
		}

		if(this.hasSectionInitialised) {
			this._lastLoaded = new Date();
			//this.filterGroupEditor.destroy();
			this.contentEl.empty();
		}

		this.hasSectionInitialised = true;
		this.setHasInitialLoaded();
		this.contentEl.html(data.section_html);

		this.updateGroupingVars();

		this._lastLoaded = new Date();

		if (lastSelectedId) {
			$('#' + lastSelectedId).closest('.is-nav-item').addClass('nav-selected');
		}

		new DeskPRO.ElementHandler.SimpleTabs($('#chat_outline_labels_switcher'));

		this.fireEvent('sectionInit');
	},

	_initStatusMenu: function() {
		var self = this;

		var statusMenuHandler = {
			getBackdrop: function() {
				if (!this.backdrop) {
					this.backdrop = $('<div class="backdrop" />').appendTo('body');
					this.backdrop.on('click', function() { statusMenuHandler.close(); });
				}
				return this.backdrop;
			},
			getList: function() {
				if (!this.list) {
					this.list = $('#agent_status_menu').hide().detach().appendTo('body');
				}
				return this.list;
			},
			open: function() {
				this.getBackdrop().show();
				this.getList().show();
				self.statusMenuOpen = true;
				self.fireEvent('statusMenuOpened');
			},
			close: function() {
				this.getBackdrop().hide();
				this.getList().hide();
				self.statusMenuOpen = false;
				self.fireEvent('statusMenuClosed')
			},
			init: function() {
				$('#chatStatusWrap').on('click', function() { statusMenuHandler.open(); });
				$('#agent_status_away_overlay').on('click', function() { statusMenuHandler.close(); });
				$('#agent_status_menu').find('.notifHead').on('click', function() { statusMenuHandler.close(); });
			}
		};
		statusMenuHandler.init();

		$('#agent_status_menu_me_list').find('.trigger-toggle-status').on('click', function(ev) {

			ev.preventDefault();
			ev.stopPropagation();

			$('#agent_status_menu_me_list').addClass('dp-loading-on');

			var is_available;
			if (!$('#agent_status_menu_me_list').data('is-online')) {
				// Toggle on
				is_available = true;
			} else {
				// Toggle off
				is_available = false;
			}

			$('#agent_status_menu_me_list').data('is-online', is_available);

			self.sendUpdateAgentStatus(is_available, function() {
				$('#agent_status_menu_me_list').removeClass('dp-loading-on');

				if (!is_available) {
					self.onlineAgentIds.erase(DESKPRO_PERSON_ID);
				} else {
					self.onlineAgentIds.include(DESKPRO_PERSON_ID);
				}

				self.refreshOnlineAgentsList();
				self.refreshOnlineAgentDepGroups();
			});
		});
	},

	_initStatusMenuAgents: function() {
		var self = this;
		var status_menu_el = $('#agent_status_menu_onlinelist');
		this.onlineAgentsGroupDepCheck = status_menu_el.find('.agents-list-groupdep');
		this.onlineAgentsList = status_menu_el.find('ul.list.normal');
		this.onlineAgentsListGrouped = status_menu_el.find('ul.list.department-grouped');

		status_menu_el.find('.agents-list-groupdep').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			self.onlineAgentsGroupDepCheck.toggleClass('toggle-on');

			if (self.onlineAgentsGroupDepCheck.hasClass('toggle-on')) {
				self.refreshOnlineAgentDepGroups();
				self.onlineAgentsListGrouped.show();
				self.onlineAgentsList.hide();
			} else {
				self.onlineAgentsListGrouped.hide();
				self.onlineAgentsList.show();
			}
		});

		// We broadcast this when setting the status manually
		// It means any other locations we're signed in under get the same
		// message and we all sync our status properly
		DeskPRO_Window.getMessageBroker().addMessageListener('agent.ui.user-chat-status', function(info) {
			if (info.is_online) {
				self.onlineAgentIds.include(DESKPRO_PERSON_ID);
			} else {
				self.onlineAgentIds.erase(DESKPRO_PERSON_ID);
			}
		});

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

		DeskPRO_Window.getMessageBroker().addMessageListener('agent.online-agents-userchat', function(info) {
			self.onlineAgentIds = [];

			if (info.online_agents && info.online_agents.length) {
				info.online_agents.forEach(function(agent_id) {
					self.onlineAgentIds.push(parseInt(agent_id));
				});
			}

			self.refreshOnlineAgentsList();
			self.refreshOnlineAgentDepGroups();
		}, this);
	},

	_initStatusMenuUsers: function() {
		var self = this;

		this.addEvent('statusMenuOpened', function() {
			if (this.onlineUsersRefreshTimer) {
				window.clearTimeout(this.onlineUsersRefreshTimer);
				this.onlineUsersRefreshTimer = null;
			}
		}, this);
	},

	_initTemplates: function() {
		$('#new_user_chat_alert').template('new_user_chat_alert');
		$('#invite_chat_alert').template('invite_chat_alert');
		$('#new_user_chat_alert_message').template('new_user_chat_alert_message');
		$('#added_part_user_chat_alert').template('added_part_user_chat_alert');
		$('#user_chat_newmsg_sound').template('user_chat_newmsg_sound');
	},

	_initMessageHandlers: function() {
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.new', this.handleNewChat, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.reassigned', this.handleReassignedChat, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.unassigned', this.handleUnassignedChat, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.ended', this.handleChatEnded, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.depchange', this.handleDepChange, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.invited', this.handleInvited, this);
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_user_agent.chat-parts-updated', this.handlePartsUpdated, this);
	},

	//##################################################################################################################
	//# Window: Online users / tracking
	//##################################################################################################################

	startRefreshingWhileOpenTimer: function() {
		var self = this;
		if (!this.statusMenuOpen) {
			return;
		}

		if (this.onlineUsersRefreshAjax) {
			return;
		}

		if (this.onlineUsersRefreshTimer) {
			window.clearTimeout(this.onlineUsersRefreshTimer);
			this.onlineUsersRefreshTimer = null;
		}
	},

	//##################################################################################################################
	//# Window: Online agents / status
	//##################################################################################################################

	refreshOnlineAgentsList: function() {
		var self = this;
		var list = this.onlineAgentsList;
		var count;
		var hasme = false;
		list.find('li').hide().removeClass('on last');

		this.onlineAgentIds.forEach(function(agent_id) {
			if (parseInt(agent_id) === DESKPRO_PERSON_ID) {
				hasme = true;
			}

			list.find('li.agent-' + agent_id).show().addClass('on');
		});

		list.find('li.on').last().addClass('last');

		count = self.onlineAgentIds.length;

		if (hasme) {
			$('#agent_status_menu_me_list').data('is-online', true);
			Orb.enablePhraseEl('agent.chrome.chat_sign-out', $('#agent_status_menu_me_list'));
			Orb.enablePhraseEl('agent.chrome.chat_logged-in', $('#dp_header_userchat_btn').find('.status'));
		} else {
			$('#agent_status_menu_me_list').data('is-online', false);
			Orb.enablePhraseEl('agent.chrome.chat_sign-in', $('#agent_status_menu_me_list'));
			Orb.enablePhraseEl('agent.chrome.chat_logged-out', $('#dp_header_userchat_btn').find('.status'));
		}


		Orb.phraseTextEl($('.agent_chrome_chat_online_agents'), {count: count});
		DeskPRO_Window.util.modCountEl($('.userchat-online-agents-count'), '=', count);

		var userchatBtn = $('#dp_header_userchat_btn');
		userchatBtn.removeClass('me-offline all-offline');
		if (count) {
			if (!hasme) {
				$('#dp_header_userchat_btn').addClass('me-offline');
			}
		} else {
			$('#dp_header_userchat_btn').addClass('all-offline');
		}
	},

	refreshOnlineAgentDepGroups: function() {
		var self = this;
		if (!this.onlineAgentsGroupDepCheck.hasClass('toggle-on')) {
			return;
		}

		this.onlineAgentsListGrouped.find('li.dep').hide();
		this.onlineAgentsListGrouped.find('ul').empty();

		this.onlineAgentIds.forEach(function(agentId) {
			var li = this.onlineAgentsList.find('li.agent-' + agentId);
			var depIds = (li.data('department-ids') || '') + '';
			depIds = depIds.split(',');

			if (depIds.length) {
				depIds.each(function(depId) {
					var depRow = self.onlineAgentsListGrouped.find('li.dep-' + depId);
					var depList = depRow.find('ul');
					depList.append(li.clone());
					depRow.show();
				});
			}
		}, this);
	},

	sendUpdateAgentStatus: function(is_available, callback) {

		var status   = 'available';
		var postData = [];

		if (is_available) {
			postData.push({
				name: 'is_chat_available',
				value: 1
			});
		} else {
			postData.push({
				name: 'is_chat_available',
				value: 0
			});
		}

		if (status == 'available') {
			$.ajax({
				url: BASE_URL + 'agent/misc/set-agent-status/available',
				type: 'POST',
				data: postData,
				complete: function() {
					if (!is_available) {
						$('#chatStatusWrap').addClass('offline');
					} else {
						$('#chatStatusWrap').removeClass('offline');
					}
					if (callback) {
						callback();
					}
				}
			});
		} else if (status == 'away') {
			$.ajax({
				url: BASE_URL + 'agent/misc/set-agent-status/away',
				type: 'POST',
				complete: function() {
					if (callback) {
						callback();
					}
				}
			});
		}
	},

	//##################################################################################################################
	//# Source pane
	//##################################################################################################################

	refreshOpenCounts: function(now) {

		var self = this;
		if (!now && this.refreshCountsTimeout) {
			return;
		}

		var fn = function() {
			self.refreshCountsTimeout = null;
			$.ajax({
				url: BASE_URL + 'agent/chat/open-counts.json',
				dataType: 'json',
				success: function(data) {

					$('#userchat_deplist_all').find('.list-counter').each(function() {
						var key, subkey, count;
						key = $(this).data('count-key').split('.');
						subkey = key[1];
						key = key[0];

						if (data && data[key] && data[key][subkey]) {
							count = data[key][subkey];
						} else {
							count = 0;
						}

						$(this).text(count);
						if (!$(this).parent().is('h3')) {
							$(this).closest('li').hide();
						} else {
							$(this).closest('li').show();
						}
					});

					$('#userchat_deplist_all').find('> ul.nav-list').each(function() {
						if ($(this).find('> li').filter(':visible')) {
							$(this).show();
						} else {
							$(this).hide();
						}
					});

					self.handleUpdateCounts();
				}
			});
		};

		if (now) {
			if (this.refreshCountsTimeout) {
				window.clearTimeout(refreshCountsTimeout);
			}
			fn();
		} else {
			this.refreshCountsTimeout = window.setTimeout(fn, 4500);
		}
	},

	refreshFilterGrouping: function(filterId, lastSelectedId) {
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

					if (lastSelectedId) {
						$('#' + lastSelectedId).closest('.is-nav-item').addClass('nav-selected');
					}
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

		//self.filterGroupEditor.updatePositions();
	},

	getGroupingVar: function(filterId) {
		return $('#chat_filter_group_editor .filter-' + filterId + ' .field-option').val();
	},

	updateGroupingVars: function() {
		var filterId;

		if (!this.groups) {
			return;
		}

		for(filterId in this.groups) {
			$('#chat_filter_group_editor').find('.filter-' + filterId + ' .field-option').val(this.groups[filterId] || '');
		}
	},

	onShow: function() {
		var self = this;
		window.setTimeout(function() {
			if (!self.isVisible()) {
				DeskPRO_Window.getSectionData('chat_section', self._initSection.bind(self));
				var el = self.getSectionElement().find('.nav-selected');
				if (el[0]) {
					if (el.data('route')) {
						DeskPRO_Window.runPageRouteFromElement(el);
					} else {
						DeskPRO_Window.runPageRouteFromElement(el.closest('[data-route]'));
					}
				}
			}
		}, 250);
	},

	onHide: function() {
	},

	handleUpdateCounts: function(data) {
		DeskPRO_Window.getSectionData('chat_section', (function(data) {
			this._initSection(data);
		}).bind(this));
	},

	isChatOpen: function(convoId) {
		var chatTabs = DeskPRO_Window.getTabWatcher().findTabType('userchat');
		var isOpen = false;
		chatTabs.forEach(function(tab) {
			if (parseInt(tab.page.meta.conversation_id) == parseInt(convoId)) {
				isOpen = tab;
				return false;
			}
		}, this);

		return isOpen;
	},

	modListingCount: function(id, op, count) {

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
		} else {
			this.handleUpdateCounts();
		}
	},

	handleDepChange: function(data) {
		this.handleUpdateCounts();
	},

	handleChatEnded: function(data) {
		if (!this.isDepAllowed(data.department_id)) {
			return;
		}

		this.refreshOpenCounts();

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
			DeskPRO_Window.faviconBadge.disableCrazyMode();
		}

		this.handleUpdateCounts();
	},

	handlePartsUpdated: function(data) {
		DeskPRO_Window.getMessageBroker().sendMessage('chat_user_agent.chat-parts-updated-' + data.conversation_id, data);

		if (data && data.participant_ids && data.participant_ids.contains(DESKPRO_PERSON_ID)) {
			if (!this.isChatOpen(data.conversation_id)) {
				DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
			}
		}
	},

	handleNewChat: function(data) {
		var self = this;

		if (!this.isDepAllowed(data.department_id)) {
			return;
		}

		this.handleUpdateCounts();

		var openTab = this.isChatOpen(data.conversation_id);
		if (openTab && data.restarted) {
			openTab.page.closeSelf();
			DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
			return;
		}

		if (!data.agent_id) {
			if (!this.dismissedChats[data.conversation_id] && !this.newChats[data.conversation_id]) {
				this.showNewChatAlert(data);
			}
		} else {
			if (data.agent_id == DESKPRO_PERSON_ID && !this.isChatOpen(data.conversation_id)) {
        if (data.eventType === 'chat.new') {
					// Round robin assigned
          this.showNewChatRoundRobinAlert(data);
          DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {focus: false, isBackgroundLoad: true, noToggle:true});
        } else {
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
						delete self.openingChatTimeout[data.conversation_id];
						DeskPRO_Window.faviconBadge.disableCrazyMode();
					}, 1000);
				}
			}

			DeskPRO_Window.getSectionData('chat_section', self._initSection.bind(self));
		}

		if (!data.agent_id) {
			this.modDepListingCount(data.department_id, '+');
		}

		this.refreshOpenCounts();
	},

	handleUnassignedChat: function(data) {
		if (!this.isDepAllowed(data.department_id)) {
			return;
		}

		this.handleUpdateCounts();

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
			DeskPRO_Window.faviconBadge.disableCrazyMode();
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
			if (audio && audio.pause) {
				try {
					audio.pause();
				} catch(e) {}
			}
			alertEl.remove();
		});
		$('.accept-trigger', alertEl).on('click', function(ev) {
			ev.stopPropagation();

			DeskPRO_Window.runPageRouteFromElement(this);
			if (audio && audio.pause) {
				audio.pause();
			}
			alertEl.remove();

			self.closeIframes();

		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id + '/join');
	},

	handleReassignedChat: function(data) {
		var self = this;

		if (!this.isDepAllowed(data.department_id) && data.agent_id != DESKPRO_PERSON_ID) {
			return;
		}

		// Means we were the agent, but unassassigned ourselves
		if (data.old_agent_id && data.old_agent_id == DESKPRO_PERSON_ID) {
			this.dismissedChats[data.conversation_id] = true;
		}

		var notifyWin = $('#new_user_chat_alert_' + data.conversation_id);
		if (notifyWin[0]) {
			if (DP_USERCHAT_HIDE_CLAIMED_CHAT) {
				notifyWin.remove();
				return;
			}

			notifyWin.addClass('claimed');
			notifyWin.find('.waiting.row').hide();
			notifyWin.find('.taken.row').show().find('.place-assigned-name').text(data.new_agent_name);
			notifyWin.find('.accept-trigger').hide();
			notifyWin.find('audio').remove();

			notifyWin.data('dismiss-count', 100);
			var updateFn = function() {
				notifyWin.find();
				var count = parseInt(notifyWin.data('dismiss-count')) - 1;
				notifyWin.data('dismiss-count', count);
				notifyWin.find('button.dismiss-trigger').find('.place-countdown').show().text('(' + count + ')');
				if (count == 0) {
					notifyWin.remove();
				} else {
					window.setTimeout(updateFn, 1000);
				}
			}
			updateFn();
		}
		DeskPRO_Window.getMessageBroker().sendMessage('chat_convo.' + data.conversation_id + '.reassigned', data);

		// See handleNewChat comment about this
		if (data.agent_id == DESKPRO_PERSON_ID && !this.isChatOpen(data.conversation_id)) {
			this.openingChatTimeout[data.conversation_id] = window.setTimeout(function() {
				DeskPRO_Window.runPageRoute('page:' + BASE_URL + 'agent/chat/view/' + data.conversation_id, {noToggle:true});
				delete self.openingChatTimeout[data.conversation_id];
				DeskPRO_Window.faviconBadge.disableCrazyMode();
			}, 1000);
		}

		this.handleUpdateCounts();
	},

	showNewChatAlert: function(data) {

		if (!this.isDepAllowed(data.department_id)) {
			return;
		}

		this.refreshOpenCounts();

		if (!this.onlineAgentIds.contains(DESKPRO_PERSON_ID)) {
			return;
		}

		var conversation_id = data.conversation_id;
		this.newChats[conversation_id] = true;

		// If we already have the chat tab open, it probably means the chat was timed out
		// but the user came back
		var chatTabs = DeskPRO_Window.getTabWatcher().findTabType('userchat');
		var found = false;
		chatTabs.forEach(function(t) {
			if (t.meta && t.meta.conversation_id && parseInt(t.meta.conversation_id) == parseInt(conversation_id)) {
				found = t;
				return false;
			}
		});

		if (found) {
			DeskPRO_Window.loadPage(BASE_URL + 'agent/chat/view/' + conversation_id, {ignoreExist:true});
			found.closeSelf();
			return;
		}

		var alertEl = $(data.html);
		alertEl.appendTo('body');
		DeskPRO_Window.handleSoundElements(alertEl);

		var titles = this.getNewChatTitles();
    var winTitle;
		if (titles.length === 1) {
			winTitle = 'New chat: ' + titles[0];
		} else {
			winTitle = titles.length + ' New chats: ' + titles.join(', ');
		}
		DeskPRO_Window.faviconBadge.enableCrazyMode(winTitle);

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

		DeskPRO_Window.notifications.addMessage('chat', 'New chat by ' + alertEl.find('.label-by-name').text(), 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id, 'chat-' + conversation_id)

		$('.dismiss-trigger', alertEl).on('click', function() {
			if (audio && audio.pause) {
				try {
					audio.pause();
				} catch(e) {}
			}
			alertEl.remove();
      DeskPRO_Window.util.ajaxWithClientMessages({
        url: BASE_URL + 'agent/chat/decline/' + conversation_id,
        success: function(data) {
        }
      });

			self.dismissedChats[data.conversation_id] = true;
			DeskPRO_Window.faviconBadge.disableCrazyMode();
			window.clearTimeout(waitTimer);
		});
		$('.accept-trigger, .join-trigger', alertEl).on('click', function(ev) {
			ev.stopPropagation();
			self.closeIframes();
			if (audio) {
				try {
					audio.pause();
				} catch(e) {}
			}
			DeskPRO_Window.util.ajaxWithClientMessages({
				url: BASE_URL + 'agent/chat/join/' + conversation_id,
				success: function(data) {
					if (data.result) {
						$('.chatreply .input-wrap').show();
						$('.chatreply .agent-join').hide();
					}
				}
			});
			alertEl.remove();
			window.clearTimeout(waitTimer);

// todo trigger, chat click handler (show tabs)
//			if (!DeskPRO_Window.paneVis.tabs) {
//				DeskPRO_Window.setPaneVis('tabs', true);
//			}
		}).data('route', 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id + '/join');
	},

  showNewChatRoundRobinAlert: function(data) {
    this.refreshOpenCounts();

    var conversation_id = data.conversation_id;

    if (document.visibilityState && document.visibilityState !== 'visible') {
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

      $('.accept-trigger, .join-trigger', alertEl).on('click', function(ev) {
        ev.stopPropagation();
        self.closeIframes();
        if (audio) {
          try {
            audio.pause();
          } catch(e) {}
        }
        alertEl.remove();
        window.clearTimeout(waitTimer);

      });
    }

    var titles = this.getNewChatTitles();
    var winTitle;
    if (titles.length === 1) {
      winTitle = 'New chat: ' + titles[0];
    } else {
      winTitle = titles.length + ' New chats: ' + titles.join(', ');
    }
    DeskPRO_Window.faviconBadge.enableCrazyMode(winTitle);

    DeskPRO_Window.notifications.addMessage('chat', 'New chat by ' + data.person_name, 'page:' + BASE_URL + 'agent/chat/view/' + conversation_id, 'chat-' + conversation_id)
  },

	getNewChatTitles: function() {
		var titles = [];
		$('body > section.new-user-chat-alert').each(function() {
			titles.push($(this).find('span.label-by-name').text().trim());
		});

		return titles;
	},

	isDepAllowed: function(id) {
		id = parseInt(id);

		console.log("%d in %o", id, window.DESKPRO_PERSON_PERMS.chat_dep_ids);

		return window.DESKPRO_PERSON_PERMS.chat_dep_ids.indexOf(id) !== -1;
	},

	closeIframes: function() {
		if (window.DP_FRAME_OVERLAYS) {
      Object.keys(window.DP_FRAME_OVERLAYS).forEach(function(key) {
				var iframe = window.DP_FRAME_OVERLAYS[key];
				if (iframe.opened) {
					iframe.close();
				}
			});
		}
	}
});
