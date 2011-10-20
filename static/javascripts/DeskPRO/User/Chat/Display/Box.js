/**
 * User chat handler
 */
var DpChat_Display = (function() {

	var chatBox = null;
	var chatBoxBtn = null;
	var messageWrapper = null;
	var findingAgentEl = null;

	var typingFuncTime = null;

	var self = this;

	var $ = null;

	this.initDisplay = function(options) {

		$ = options.jQuery;

		DpChatConsole.log('DpChat_Display.initDisplay');

		if (chatBox) {
			chatBox.remove();
			chatBoxBtn.remove();
		}

		//--------------------
		// The butotn that opens the chat
		//--------------------

		var html = [];
		html.push('<div id="dpchat_btn"><div id="dpchat_btn_label"><span class="start-chat">Click here to chat with us</span><span class="open-chat">Open your chat</span></div></div>');

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

		options.name = options.email = 1;

		var html = [];
		html.push('<div id="dpchat_panel">');
			html.push('<div id="dpchat_titlebar"><h3>Chat</h3><span id="dpchat_closepanel">Minimize</span><span id="dpchat_endchat">End Chat</span><span id="dpchat_popchat">Open in new window</span></div>');
			if (options.name || options.email || options.departmentSelect) {
				html.push('<div id="dpchat_preform">')

				if (options.name) {
					html.push('<div class="dpchat-row dpchat-name">');
						html.push('<label>Your name:</label><div class="dpchat-input"><input type="text" value="" name="name" /></div>');
					html.push('</div>');
				}
				if (options.email) {
					html.push('<div class="dpchat-row dpchat-email">');
						html.push('<label>Your email address:</label><div class="dpchat-input"><input type="text" value="" name="email" /></div>');
					html.push('</div>');
				}
				if (options.departmentSelect) {
					html.push('<div class="dpchat-row dpchat-department">');
						html.push('<label>Choose a department:</label><div class="dpchat-input">' + options.departmentSelect + '</div>');
					html.push('</div>');
				}

				html.push('<div class="controls"><button id="dpchat_preform_submit">Start Chatting</button></div>');
				html.push('</div>');
			}
			html.push('<div id="dpchat_messages" ' + (options.departmentSelect ? 'style="display:none"' : '') + '>');
				html.push('<div class="dpchat-info dpchat-instruction">Type in your question to get started</div>');
				html.push('<div class="dpchat-finding-agent">Please wait while we find an agent to take your chat.</div>');
			html.push('</div>');
			html.push('<div id="dpchat_input" ' + (options.departmentSelect ? 'style="display:none"' : '') + '><textarea></textarea><button id="dpchat_send">Send</button></div>')
		html.push('</div>');

		var el = $(html.join(''));
		el.appendTo('body');
		chatBox = el;

		DpChatConsole.log('DpChat_Display.initDisplay: chatBox %o', chatBox);

		messageWrapper = $('#dpchat_messages');
		findingAgentEl = $('.dpchat-finding-agent', messageWrapper);

		var self = this;
		$('#dpchat_preform_submit').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			var altData = getAltFormData();
			console.log(altData);
			DpChat.sendMessage('', altData);
			self.addMessageRow(false, false, 'justStart');
		});

		$('#dpchat_closepanel').click(function() {
			chatBox.removeClass('dpchat-panel-open');
		});

		$('#dpchat_endchat').click(function() {
			if (confirm('Are you sure you want to end this chat?')) {
				DpChat.endChat();
			}
		});

		$('#dpchat_popchat').click(function() {
			DpChat.popChat();
		});

		var messageTextarea = $('#dpchat_input > textarea');
		messageTextarea.keypress(function(ev) {
			DpChat.userTypingIndicator(messageTextarea.val());

			if (ev.keyCode == 13 && !ev.metaKey) {
				ev.preventDefault();
				doSend();
			}
		});

		$('#dpchat_send').click(function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			doSend();
		});
	};

	var doSend = function() {
		var messageTextarea = $('#dpchat_input > textarea');

		var msg = $.trim(messageTextarea.val());
		messageTextarea.val('');

		if (!msg.length) {
			return;
		}

		if (typingFuncTime) {
			window.clearTimeout(typingFuncTime);
		}

		DpChat.sendMessage(msg, getAltFormData());
		self.addMessageRow('You', msg, 'user');
	};

	this.showAssignedStatus = function(isAssigned) {
		if (!isAssigned) {
			findingAgentEl.detach().appendTo(messageWrapper).show();
		} else {
			findingAgentEl.hide();
		}
	};

	this.showProactive = function() {
		var html = [];
		html.push('<div id="dpchat_proactive_wrapper">');
			html.push('<div id="dpchat_proactive_close"></div>');
		html.push('</div>');

		var el = $(html.join('')).appendTo('body');
		el.click(function() {
			el.remove();
			DpChat.popChat();
		});
		$('#dpchat_proactive_close').click(function(ev) {
			ev.stopPropagation();
			el.remove();

			DpChat.proactiveHidden();
		});
	};

	var getAltFormData = function() {
		return $('#dpchat_preform :input').serializeArray();
	};

	this.showChatPanel = function() {
		chatBox.addClass('dpchat-panel-open');
	};

	this.hideChatPanel = function() {
		chatBox.removeClass('dpchat-panel-open');
	};

	var overlay = null;
	this.openIframeOverlay = function(url) {
		var html = [];
		html.push('<div class="dpchat-overlay-outer"');
			html.push('<div class="dpchat-overlay-inner">');
				html.push('<div class="dpchat-overlay-content">');
					html.push('<a class="close-overlay close-trigger">X</a>');
					html.push('<iframe src="'+url+'" style="width: 600px; height: 400px;" marginheight="0" marginwidth="0" frameborder="0"></iframe>');
				html.push('</div>');
			html.push('</div>');
		html.push('</div>');

		overlay = $(html.join(''));
		$('.close-trigger', overlay).click(function() {
			overlay.remove();
		});

		var x = ($(window).width() - 600) / 2;
		var y = ($(window).height() - 400) / 2;

		overlay.css({
			'zIndex': 1000000,
			'display': 'block',
			'top': y,
			'right': x
		});

		overlay.appendTo('body');
	};


	/**
	 * Add a message row
	 *
	 * @param name
	 * @param message
	 * @param type
	 */
	this.addMessageRow = function(name, message, type, is_html) {

		$('#dpchat_preform').hide();
		$('#dpchat_messages').show();
		$('#dpchat_input').show();

		chatBoxBtn.addClass('has-chat');
		chatBox.addClass('has-chat');
		$('.dpchat-instruction', messageWrapper).hide();

		if (type == 'justStart') {
			return;
		}

		type = type || 'user';
		if (type == 'sys') {
			name = '* ';
		} else {
			name = name + ': ';
		}

		var html = [];
		html.push('<div class="dpchat-message dpchat-'+type+'">');
			html.push('<div class="dpchat-author">' + name + '</div>');
			html.push('<div class="dpchat-msg"></div>');
		html.push('</div>');

		var el = $(html.join(''));
		if (is_html) {
			console.log(message);
			$('.dpchat-msg', el).html(message);
		} else {
			message = DpChat.util.escapeHtml(message);
			message = DpChat.util.linkUrls(message);
			$('.dpchat-msg', el).html(message);
		}

		el.appendTo(messageWrapper);

		messageWrapper.scrollTop(100000);

		return el;
	};

	this.destroy = function() {
		chatBox.remove();
		chatBoxBtn.remove();
	};

	return this;
})();
DpChat.setDisplay(DpChat_Display);
