Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.UserChat = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {

		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.barWrapper = this.wrapper.children('.layout-footer').attr('id', Orb.getUniqueId());

		DeskPRO_Window.getMessageBroker().addMessageListener('chat.new-message-' + this.meta.conversation_id, this.handleNewMessage.bind(this));

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

	_handleResize: function() {
		if (!this.layout) return;
		this.layout.resizeAll();
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
			triggerElement: $('div.agent_id.menu-trigger:first', this.wrapper),
			menuElement: $('ul.agent_id.menu:first', this.wrapper),
			onItemClicked: function(info) {
				var agent_id = $(info.itemEl).data('option-id');
				self.reassignConvo(agent_id);

				$('span.agent_id.val', this.wrapper).html(DeskPRO_Window.getDisplayName('agent', agent_id)||'Unassigned');
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

		var el = $('.chat-status:first', this.wrapper);
		$('.open', el).hide();
		$('.ended', el).show();

		this.addMessageRow('*', 'Chat ended', 'sys');
	},

	reassignConvo: function(agent_id) {
		$.ajax({
			url: BASE_URL + 'agent/chat/assign/' + this.meta.conversation_id + '/' + agent_id,
			context: this,
			contentType: 'json'
		});

		this.addMessageRow('*', 'Chat assigned to ' + DeskPRO_Window.getDisplayName('agent', agent_id)||'Unassigned', 'sys');
	},

	handleNewMessage: function(data) {
		DeskPRO_Window.pageTabStrip.alertTab(this.meta.tabIdClass);
		this.addMessageRow(data.author_name, data.message, data.author_type);
	},

	addMessageRow: function(name, msg, type) {

		if (type == 'sys') {
			name = '* ';
		} else {
			name = '&lt;' + name + '&gt; ';
		}

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