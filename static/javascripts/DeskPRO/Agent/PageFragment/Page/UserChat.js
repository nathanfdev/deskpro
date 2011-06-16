Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.UserChat = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {

		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.barWrapper = this.wrapper.children('.layout-footer').attr('id', Orb.getUniqueId());

		DeskPRO_Window.getMessageBroker().addMessageListener('chat.new-message-' + this.meta.conversation_id, this.handleNewMessage.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('chat.chat-ended-' + this.meta.conversation_id, this.chatHasEnded.bind(this));
		DeskPRO_Window.getMessageBroker().addMessageListener('chat_user_agent.chat-parts-updated-' + this.meta.conversation_id, this.handleUpdateParts.bind(this));

		this._initLayout();

		DeskPRO_Window.getMessageBroker().addMessageListener('window.innerLayout.resize', (function() {
			this._handleResize()
		}).bind(this));

		var self = this;
		var messageTextarea = $('.new-message', this.barWrapper);
		messageTextarea.keypress(function(ev) {
			if (ev.keyCode == 13 && !ev.metaKey) {
				ev.preventDefault();

				var msg = messageTextarea.val().trim();
				messageTextarea.val('');

				if (!msg.length) {
					return;
				}

				self.sendMessage(msg);
				self.addMessageRow(self.meta.youName, msg);
			}
		});

		this._initMenus();
	},

	_initLayout: function() {

		var cw = this.contentWrapper;
		cw.tinyscrollbar();
		$('div.scroll-content:first, div.scroll-viewport:first', this.contentWrapper).resize(function() {
			// When size changes within the pane, need to re-size the scroll
			cw.tinyscrollbar_update();
		});
		
		this.layout = new DeskPRO.Agent.Layout.FooterLayout(this.wrapper);

		var self = this;
		var simpleTabs = new DeskPRO.UI.SimpleTabs({
			context: this.contentWrapper,
			triggerElements: $('.full-container-tabbed-tabs li', this.contentWrapper),
			onTabSwitch: function(info) {

			}
		});
	},

	handleUpdateParts: function(data) {
		this.updateActiveAgentList(data.agent_id, data.participant_ids);
	},

	updateActiveAgentList: function(assigned, parts) {
		var assigned_name = DeskPRO_Window.getDisplayName('agent', agent_id) || 'Unassigned';
		$('span.agent_id.val', this.wrapper).html(assigned_name);

		var ul = $('.convo_participants ul', this.wrapper);
		ul.empty();

		if (!parts.lenght) {
			ul.append('<li class="agent-0">None</li>');
		} else {
			Array.each(parts, function(agent_id) {
				var name = DeskPRO_Window.getDisplayName('agent', agent_id);
				ul.append('<li class="agent-'+agent_id+'">'+name+'</li>');
			});
		}
	},

	_handleResize: function() {
		if (!this.layout) return;
		this.layout.doLayout();
	},

	_initMenus: function() {
		var self = this;
		this.qrMenu = new DeskPRO.UI.Menu({
			triggerElement: $('li.macros:first', this.barWrapper),
			menuElement: $('ul.quick-replies:first', this.wrapper),
			onItemClicked: function(info) {
				var qr_id = $(info.itemEl).data('qr-id');
				self.loadQuickReply(qr_id);
			}
		});

		this.assignMenu = new DeskPRO.UI.Menu({
			triggerElement: $('div.agent_id.menu-trigger:first, .convo_participants', this.wrapper),
			menuElement: $('ul.agent_id.menu:first', this.wrapper),
			onBeforeMenuOpened: function(info) {
				var list = info.menu.elements.list;
				$('li.sep', list).show();
				$('li.assign-to-me', list).show();

				$('li[data-option-id]', list).each(function() {
					var id = $(this).data('option-id');
					var onlineEl = $('#agent_online_list > li.agent-' + id);
					if (onlineEl.length || id == DESKPRO_PERSON_ID || id == '0') {
						$(this).show();
					} else {
						$(this).hide();
					}
				});

				var trigger = $(info.menu.getOpenTriggerElement());

				var part = false;
				if (trigger.is('.convo_participants')) {
					part = true;
				} else {
					var parents = trigger.parentsUntil('.convo_participants');
					if (parents.eq(0).parent().is('.convo_participants')) {
						part = true;
					}
				}

				if (part) {
					$('li.agent-0', list).hide();
					$('li.agent-' + DESKPRO_PERSON_ID, list).hide();
					$('li.assign-to-me', list).hide();
					$('li.sep', list).hide();
				}
			},
			onItemClicked: function(info) {
				var agent_id = $(info.itemEl).data('option-id');

				var trigger = $(info.menu.getOpenTriggerElement());
				var part = false;
				if (trigger.is('.convo_participants')) {
					part = true;
				} else {
					var parents = trigger.parentsUntil('.convo_participants');
					if (parents.eq(0).parent().is('.convo_participants')) {
						part = true;
					}
				}

				console.log('part %i', part);

				if (part) {
					var wrap = $('.convo_participants', this.wrapper);
					var checkEl = $('li.agent-' + agent_id, wrap);
					if (!checkEl.length) {
						$('li.agent-0', wrap).remove();
						$('<li class="agent-'+agent_id+'">'+DeskPRO_Window.getDisplayName('agent', agent_id)+'</li>').appendTo($('ul', wrap));

						self.addPart(agent_id);
					}
				} else {
					self.reassignConvo(agent_id);
					$('span.agent_id.val', this.wrapper).html(DeskPRO_Window.getDisplayName('agent', agent_id)||'Unassigned');
				}
			}
		});

		var endMenuEl = $('ul.end-menu', this.wrapper);
		if (endMenuEl.length) {
			this.endMenu = new DeskPRO.UI.Menu({
				triggerElement: $('div.chat-status:first', this.wrapper),
				menuElement: endMenuEl,
				onItemClicked: function(info) {
					self.endChat();
				}
			});
		}
	},

	loadQuickReply: function(qr_id) {
		$.ajax({
			url: BASE_URL + 'agent/chat/get-qr/' + this.meta.conversation_id + '/' + qr_id,
			context: this,
			contentType: 'json',
			success: function(data) {
				var textarea = $('.new-message', this.barWrapper);
				textarea.val(textarea.val() + data.reply).focus();
			}
		});
	},

	endChat: function() {
		$.ajax({
			url: BASE_URL + 'agent/chat/end-chat/' + this.meta.conversation_id,
			context: this,
			contentType: 'json'
		});

		this.addMessageRow('*', 'Chat ended', 'sys');
		this.chatHasEnded();
	},

	chatHasEnded: function() {

		if (this.hasEnded) return;
		this.hasEnded = true;

		var el = $('.chat-status:first', this.wrapper);
		$('.open', el).hide();
		$('.ended', el).show();

		this.barWrapper.hide();
		this._handleResize();
	},

	addPart: function(agent_id) {
		$.ajax({
			url: BASE_URL + 'agent/chat/add-part/' + this.meta.conversation_id + '/' + agent_id,
			context: this,
			contentType: 'json'
		});

		this.addMessageRow('*', (DeskPRO_Window.getDisplayName('agent', agent_id)) + ' joined', 'sys');
	},

	reassignConvo: function(agent_id) {
		$.ajax({
			url: BASE_URL + 'agent/chat/assign/' + this.meta.conversation_id + '/' + agent_id,
			context: this,
			contentType: 'json'
		});

		this.addMessageRow('*', 'Chat assigned to ' + (DeskPRO_Window.getDisplayName('agent', agent_id)||'Unassigned'), 'sys');
	},

	handleNewMessage: function(data) {
		DeskPRO_Window.pageTabStrip.alertTab(this.meta.tabIdClass);
		this.addMessageRow(data.author_name, data.message, data.author_type);

		// Add 'pop' sound
		var alertEl = $.tmpl('user_chat_newmsg_sound');
		console.log('alert %o', alertEl);
		alertEl.appendTo(this.wrapper);
	},

	addMessageRow: function(name, msg, type) {

		if (type == 'sys') {
			name = '* ';
		} else {
			name = '&lt;' + name + '&gt; ';
		}

		msg = Orb.escapeHtml(msg);
		msg = Orb.linkUrls(msg);

		var html = ['<div class="message '+type+'">'];
			html.push('<span class="author">' + name + '</span>');
			html.push('<span class="message">' + msg + '</span>');
		html.push('</div>');

		$(html.join('')).appendTo($('.chat-messages .messages-wrapper', this.wrapper));

		$('.scroll-viewport', this.wrapper).scrollTop(10000);
	},

	sendMessage: function(msg) {
		$.ajax({
			url: BASE_URL + 'agent/chat/send-message/' + this.meta.conversation_id,
			data: {content: msg},
			context: this,
			contentType: 'json'
		});
	}
});