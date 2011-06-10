/**
 * User chat handler
 */
var DpChat_Display = (function() {

	var chatBox = null;
	var chatBoxBtn = null;
	var messageWrapper = null;

	var self = this;

	this.initDisplay = function(options) {

		DpChatConsole.log('DpChat_Display.initDisplay');

		if (chatBox) {
			chatBox.remove();
			chatBoxBtn.remove();
		}

		//--------------------
		// The butotn that opens the chat
		//--------------------

		var html = [];
		html.push('<div id="dpchat_btn"><div id="dpchat_btn_label">Click here to chat with us</div></div>');

		var el = $(html.join(''));
		el.appendTo('body');
		chatBoxBtn = el;

		chatBoxBtn.click(function() {
			chatBox.addClass('dpchat-panel-open');
		});

		DpChatConsole.log('DpChat_Display.initDisplay: chatBoxBtn %o', chatBoxBtn);

		//--------------------
		// The chat box
		//--------------------

		var html = [];
		html.push('<div id="dpchat_panel">');
			html.push('<div id="dpchat_titlebar"><h3>Chat</h3><span id="dpchat_closepanel">Close</span></div>');
			if (options.departmentSelect) {
				html.push('<div id="dpchat_messages"><div class="dpchat-info dpchat-instruction alt-form">Choose a department and select a department to get started: ' + options.departmentSelect + '</div></div>');
			} else {
				html.push('<div id="dpchat_messages"><div class="dpchat-info dpchat-instruction">Type in your question to get started</div></div>');
			}
			html.push('<div id="dpchat_input"><textarea></textarea></div>')
		html.push('</div>');

		var el = $(html.join(''));
		el.appendTo('body');
		chatBox = el;

		DpChatConsole.log('DpChat_Display.initDisplay: chatBox %o', chatBox);

		messageWrapper = $('#dpchat_messages');

		$('#dpchat_closepanel').click(function() {
			chatBox.removeClass('dpchat-panel-open');
		});

		var messageTextarea = $('#dpchat_input > textarea');
		messageTextarea.keypress(function(ev) {
			if (ev.keyCode == 13 && !ev.metaKey) {
				ev.preventDefault();
				
				var msg = messageTextarea.val().trim();
				messageTextarea.val('');

				if (!msg.length) {
					return;
				}

				DpChat.sendMessage(msg, getAltFormData());
				self.addMessageRow('You', msg, 'user');
			}
		});
	};

	var getAltFormData = function() {
		return $('.alt-form :input', chatBox).serializeArray();
	};

	this.showChatPanel = function() {
		chatBox.addClass('dpchat-panel-open');
	};

	this.hideChatPanel = function() {
		chatBox.removeClass('dpchat-panel-open');
	};


	/**
	 * Add a message row
	 * 
	 * @param name
	 * @param message
	 * @param type
	 */
	this.addMessageRow = function(name, message, type) {

		$('.dpchat-instruction', messageWrapper).hide();

		type = type || 'user';

		var html = [];
		html.push('<div class="dpchat-message dpchat-'+type+'">');
			html.push('<div class="dpchat-author">' + name + ':</div>');
			html.push('<div class="dpchat-msg">' + message + '</div>');
		html.push('</div>');

		var el = $(html.join(''));

		el.appendTo(messageWrapper);

		messageWrapper.scrollTop(100000);

		return el;
	};

	return this;
})();
DpChat.setDisplay(DpChat_Display);