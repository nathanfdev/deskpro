Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.UserChat = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {

		this.destroyEls = [];

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

		if (this.meta.viewPersonUrl) {
			this._initPopout();
		}
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

		if (data.message_html) {
			this.addMessageRow(data.author_name, data.message_html, data.author_type, true);
		} else {
			this.addMessageRow(data.author_name, data.message, data.author_type);
		}

		// Add 'pop' sound
		var alertEl = $.tmpl('user_chat_newmsg_sound');
		alertEl.appendTo(this.wrapper);
		DeskPRO_Window.handleSoundElements(alertEl);
	},

	addMessageRow: function(name, msg, type, is_html) {

		if (type == 'sys') {
			name = '* ';
		} else {
			name = '&lt;' + name + '&gt; ';
		}

		var popoutclass = '';
		if (type == 'user') {
			popoutclass = " person-overview";
		}

		msg = Orb.escapeHtml(msg);
		msg = Orb.linkUrls(msg);

		var html = ['<div class="message '+type+'">'];
			html.push('<span class="author' + popoutclass + '">' + name + '</span>');
			html.push('<span class="message"></span>');
		html.push('</div>');

		var row = $(html.join(''));
		if (is_html) {
			$('.message', row).html(msg);
		} else {
			$('.message', row).text(msg);
		}

		row.appendTo($('.chat-messages .messages-wrapper', this.wrapper));

		$('.scroll-viewport', this.wrapper).scrollTop(10000);
	},

	sendMessage: function(msg) {
		$.ajax({
			url: BASE_URL + 'agent/chat/send-message/' + this.meta.conversation_id,
			data: {content: msg},
			context: this,
			contentType: 'json'
		});
	},

	//#################################################################
	//# Popout
	//#################################################################

	personPopoutHtml: null,
	personPopoutWaiting: false,
	_initPopout: function() {
		var self = this;
		var el = this.wrapper;

		// AJAX load the fragment now
		var url = this.getMetaData('viewPersonUrl');
		$.ajax({
			dataType: 'text',
			url: url,
			type: 'GET',
			success: function(html) {
				self.personPopoutHtml = html;
				if (self.personPopoutWaiting) {
					self.personPopoutWaiting = false;
					self._initPopoutPageFragment();
				}
			}
		});

		$('.person-overview', el).css({'cursor': 'pointer'}).click(function(event) {
			self.isMouseOverPopout = true;
			self.openPopOut(event);
		});
	},

	_initPopoutEls_done: false,
	_initPopoutEls: function() {

		if (this._initPopoutEls_done) return;
		this._initPopoutEls_done = true;

		var el = this.contentWrapper;
		var self = this;

		this.popout = $('.person-popout:first', el);
		this.popout.click(function(event) {
			// Any clicks that bubble here should stop now
			event.stopPropagation();
		});
		this.popout.detach().appendTo('body');
		this.destroyEls.push(this.popout);

		this.popoutOuter = $('.person-popout-outer:first', el);
		this.popoutOuter.detach().appendTo('body');
		this.destroyEls.push(this.popoutOuter);

		this.popoutTabs = $('.person-popout-tabs:first', el);
		this.popoutTabs.detach().appendTo('body');
		this.destroyEls.push(this.popoutTabs);

		var self = this;
		$('.close:first', this.popoutTabs).click(function() {
			self.closePopout();
		});

		$('.move-to-tab:first', this.popoutTabs).click(function() {
			DeskPRO_Window.runPageRouteFromElement($('.person-overview', self.wrapper));
			self.closePopout();
		});
	},

	openPopOut: function(event) {

		this._initPopoutEls();

		// Already open
		if (this.popout.is(':visible')) {
			return;
		}

		var orig = $('.person-overview:first', this.wrapper);
		var pos = orig.offset();
		var wrapper_pos = this.wrapper.offset();

		// can use the left position of the element to roughly
		// determine how wide the columns are
		// so we want it to stretch as far as we can, minus some wriggle room
		var width = pos.left - 35;

		// ... but not too big
		if (width > 780) {
			width = 780;
		}

		var show_popout = true;
		if (width < 400) {
			show_popout = false;
		}

		if (show_popout) {
			this.popout.css({
				'position': 'absolute',
				'display': 'block',
				'z-index': 999998,
				'width': width,
				'overflow': 'auto'
			});

			// Separate on purpose, we need the outerWidth which
			// wont be correct until the above rules are applied
			this.popout.css({
				'top': (wrapper_pos.top - 8),
				'left': (pos.left - this.popout.outerWidth() - 20),
				'bottom': 30
			});

			var poppos = this.popout.offset();
			this.popoutOuter.css({
				'position': 'absolute',
				'display': 'block',
				'z-index': 999997,
				'width': width+2+6, //2px for thi sborder, 6px for the popout border
				'overflow': 'auto',
				'top': poppos.top-1,
				'left': poppos.left-1,
				'bottom': 29 //popout bottom (30) -1 for the white border
			});

			this.popoutTabs.css({
				'z-index': 999996,
				'display': 'block',
				'top': (wrapper_pos.top - 30),
				'left': (pos.left - 260)
			});
		}

		if (!this.hasInitPopout && show_popout) {
			if (this.personPopoutHtml) {
				this._initPopoutPageFragment();
			} else {
				this.personPopoutWaiting = true;
			}
		}
	},

	closePopout: function() {
		this.popout.hide();
		this.popoutOuter.hide();
		this.popoutTabs.hide();
	},

	_initPopoutPageFragment: function() {

		this.popoutPage = DeskPRO_Window.createPageFragment(this.personPopoutHtml);
		this.popout.html(this.personPopoutHtml);
		this.personPopoutHtml = null;
		this.popoutPage.initPage(this.popout);
		this.hasInitPopout = true;
	}
});
