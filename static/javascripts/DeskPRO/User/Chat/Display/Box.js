/**
 * User chat handler
 */
DpChat_Display = (function() {

	var chatBox = null;
	var chatBoxBtn = null;
	var messageWrapper = null;

	var self = this;

	this.initDisplay = function() {

		if (chatBox) {
			chatBox.remove();
			chatBoxBtn.remove();
		}

		//--------------------
		// The butotn that opens the chat
		//--------------------

		var html = [];
		html.push('<div id="dp_user_chat_btn">Chat</div>');

		var el = $(html.join(''));
		el.appendTo('body');
		chatBoxBtn = el;

		chatBoxBtn.click(function() {
			chatBox.addClass('open');
		});

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

		chatBox = el;

		messageWrapper = $('.messages', chatBox);

		$('.close-trigger', chatBox).click(function() {
			chatBox.removeClass('open');
		});

		var messageTextarea = $('.message-box > textarea', chatBox);
		messageTextarea.keypress(function(ev) {
			if (ev.keyCode == 13 && !ev.metaKey) {
				var msg = messageTextarea.val().trim();
				messageTextarea.val('');

				DpChat.sendMessage(msg);
				self.addMessageRow('Me', msg, 'user');
			}
		});
	};


	/**
	 * Add a message row
	 * 
	 * @param name
	 * @param message
	 * @param type
	 */
	this.addMessageRow = function(name, message, type) {

		type = type || 'user';

		var html = [];
		html.push('<div class="message '+type+'">');
			html.push('<div class="author">' + name + '</div>');
			html.push('<div class="msg">' + message + '</div>');
		html.push('</div>');

		var el = $(html.join(''));

		el.appendTo(messageWrapper);

		return el;
	};
})();
DpChat.setDisplay(DpChat_Display);