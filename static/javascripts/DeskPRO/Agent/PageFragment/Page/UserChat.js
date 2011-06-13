Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.UserChat = new Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initPage: function(el) {

		this.wrapper = el;
		this.contentWrapper = this.wrapper.children('.layout-content').attr('id', Orb.getUniqueId());
		this.barWrapper = this.wrapper.children('.layout-footer').attr('id', Orb.getUniqueId());

		DeskPRO_Window.getMessageBroker().addMessageListener('chat.new-message-' + this.meta.conversation_id, this.handleNewMessage.bind(this));

		this._initLayout();

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
				self.addMessageRow('You', msg);
			}
		});

		this._initMenus();
	},

	_initLayout: function() {
		this.layout = new DeskPRO.Agent.Layout.FooterLayout(this.wrapper);
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

	handleNewMessage: function(data) {
		var html = ['<tr>'];
		html.push('<td class="author">' + data.author_name + '</td>');
		html.push('<td class="message">' + data.message + '</td>');
		html.push('</tr>');

		$(html.join('')).appendTo($('.chat-messages table', this.wrapper));
	},

	addMessageRow: function(name, msg) {
		var html = ['<tr>'];
		html.push('<td class="author">' + name + '</td>');
		html.push('<td class="message">' + msg + '</td>');
		html.push('</tr>');

		$(html.join('')).appendTo($('.chat-messages table', this.wrapper));

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