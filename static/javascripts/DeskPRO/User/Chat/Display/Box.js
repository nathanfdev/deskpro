Orb.createNamespace('DeskPRO.User.Chat.Display');

/**
 * User chat handler
 */
DeskPRO.User.Chat.Display.Box = new Orb.Class({
	Extends: DeskPRO.User.Chat.Display.AbstractDisplay,

	initElement: function() {

		if (this.chatBox) {
			this.chatBox.remove();
			this.chatBoxBtn.remove();
		}

		//--------------------
		// The butotn that opens the chat
		//--------------------

		var html = [];
		html.push('<div id="dp_user_chat_btn">Chat</div>');

		var el = $(html.join(''));
		el.appendTo('body');
		this.chatBoxBtn = el;

		this.chatBoxBtn.click((function() {
			this.chatBox.addClass('open');
		}).bind(this));

		//--------------------
		// The chat box
		//--------------------

		var html = [];
		html.push('<div id="dp_user_chat">');
			html.push('<div class="title-bar"><h3>Chat</h3><span class="close-trigger">Close</span></div>');
			html.push('<div class="messages"></div>');
			html.push('<div class="message-box"><textarea></textarea></div>')
		html.push('</div>');

		var el = $(html.join(''));
		el.appendTo('body');

		this.chatBox = el;

		this.messageWrapper = $('.messages', this.chatBox);

		$('.close-trigger', this.chatBox).click((function() {
			this.chatBox.removeClass('open');
		}).bind(this));

		var messageTextarea = $('.message-box > textarea');
		messageTextarea.keypress((function(ev) {
			if (ev.keyCode == 13 && !ev.metaKey) {
				var msg = messageTextarea.val().trim();
				messageTextarea.val('');

				this.sendMessage(msg);
			}
		}).bind(this));
	},


	/**
	 * Add a message row
	 * 
	 * @param name
	 * @param message
	 * @param type
	 */
	addMessageRow: function(name, message, type) {

		type = type || 'user';

		var html = [];
		html.push('<div class="message '+type+'">');
			html.push('<div class="author">' + name + '</div>');
			html.push('<div class="msg">' + message + '</div>');
		html.push('</div>');

		var el = $(html.join(''));

		el.appendTo(this.messageWrapper);

		return el;
	},


	/**
	 * Send a user message to the server
	 * 
	 * @param message
	 */
	sendMessage: function(message) {
		this.addMessageRow('Me', message, 'user');

		$.ajax({
			url: BASE_URL + 'chat/send-message/' + this.chat.id,
			data: {
				message: message
			},
			context: this,
			contentType: 'json'
		});
	}
});