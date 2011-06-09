if (DpChat_EnableDebug) {
	var DpChatConsole = window.console;
} else {
	var DpChatConsole = {};
	['error', 'log', 'warn', 'info', 'debug'].each(function(v) {
		DpChatConsole[v] = function() { };
	});
}

/**
 * The main user chat handler. Loaded onto a page using a async loader (UserBundle:Common:chat-loader.html.twig).
 *
 * This itself loads the display adapter for the theme, and then sets up and handles proper routing
 * of chat messages etc.
 */
var DpChat = (function() {
	var options = {
		protocol: null,
		staticUrl: null,
		deskproUrl: null,
		displayType: 'Box'
	};

	var self = this;

	/**
	 * Scoped reference to jQuery
	 * @var {jQuery}
	 */
	var $ = function() {};

	/**
	 * The display handler that defines the theme etc
	 */
	var display = null;

	/**
	 * The visitor code of this user
	 * @var {Integer}
	 */
	var visitorCode = null;

	/**
	 * Any previous messages that'll be pushed into the chat window upon load
	 * @var {Array}
	 */
	var initialMessages = null;

	/**
	 * This is a pre-init that is called automatically when the client has downloaded
	 * this source file. It ensures jQuery first, and then runs initScript that starts
	 * our actual chat init.
	 */
	this.init = function() {

		DpChatConsole.log('DpChat.init');

		if (window.jQuery === undefined || window.jQuery.fn.jquery.indexOf('1.6.') === -1) {

			DpChatConsole.log('DpChat.init: loading jquery');

			var initJquery = function() {
				DpChatConsole.log('DpChat.init: jquery loaded');
				$ = window.jQuery.noConflict(true);
				initScript();
			};

			var script_tag = document.createElement('script');
			script_tag.setAttribute("type","text/javascript");
			script_tag.setAttribute("src", ('https:' == document.location.protocol ? 'https' : 'http') + "://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js");
			script_tag.setAttribute("async", 'true');
			script_tag.onload = function() { initJquery(); };
			script_tag.onreadystatechange = function () { // Same thing but for IE
				if (this.readyState == 'complete' || this.readyState == 'loaded') {
					initJquery();
				}
			};

			(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
		} else {

			DpChatConsole.log('DpChat.init: already have jquery');

			$ = window.jQuery;
			initScript();
		}
	};

	
	/**
	 * initScript() is called from init() and loads the resources for the theme, and
	 * also fetches the users visitor ID and existing chat data, if there is any from previous pages.
	 */
	var initScript = function() {

		DpChatConsole.log('DpChat.initScript');

		if (window.DpChat_Options) {
			$.extend(options, DpChat_Options);
		}

		// Box.js
		var el = $('<script type="text/javascript" async="true" src="' + options.staticUrl + 'javascripts/DeskPRO/User/Chat/Display/' + options.displayType + '.js"></script>').appendTo('body');
		DpChatConsole.info('Added display %o', el);

		// DeskPRO script that sets/gets visitor and initial messages
		el = $('<script type="text/javascript" async="true" src="' + options.deskproUrl + 'chat/chat-visitor"></script>').appendTo('body');
		DpChatConsole.info('Added visitor init %o', el);

		// Box.css
		el = $('<link rel="stylesheet" type="text/css" href="' + options.staticUrl + 'javascripts/DeskPRO/User/Chat/Display/' + options.displayType + '.css" />').appendTo('body');
		DpChatConsole.info('Added display css %o', el);
	};


	/**
	 * When the display source file is loaded by the client, it calls DpChat.setDisplay() to set itself.
	 * If the visitorCode is already fetched, then the main chat app can finally be fully set up.
	 * 
	 * @param display
	 */
	this.setDisplay = function(_display) {
		DpChatConsole.log('DpChat.setDisplay(%o)', _display);
		display = _display;

		mainRunner();
	};


	/**
	 * When the visitor source file is loaded by the client, it calls this DpChat.setvisitorCode() to set itself.
	 * Just like setDisplay, it checks if all values are set and if they are, main() is called to fully run the chat.
	 * 
	 * @param visitorCode
	 */
	this.setVisitorCode = function(_visitorCode) {
		DpChatConsole.log('DpChat.setVisitorCode(%o)', _visitorCode);
		visitorCode = _visitorCode;

		mainRunner();
	};

	/**
	 * Ghe visitor source can call DpChat.setInitialMessages() to load messages that were exchanged on a previous
	 * page.
	 */
	this.setInitialMessages = function(_initialMessages) {
		DpChatConsole.log('DpChat.setInitialMessages(%o)', _initialMessages);
		initialMessages = _initialMessages;
	};

	
	//#################################################################
	//# Simple implementations of message broker and poller
	//#################################################################

	var messageBroker = this.messageBroker = {
		messageListeners: {},

		sendMessage: function (name, data) {

			if (this.messageListeners[name] !== undefined) {
				this.messageListeners[name].each(function(callback) {
					callback(data, name);
				});
			}

			var nameparts = name.split('.');
			var cur_name = null;

			while (nameparts.pop()) {
				cur_name = nameparts.join('.') + '.*';
				if (this.messageListeners[cur_name] !== undefined) {
					this.messageListeners[cur_name].each(function(callback) {
						callback(data, name);
					});
				}
			}
		},

		addMessageListener: function(name, callback) {
			if (this.messageListeners[name] === undefined) {
				this.messageListeners[name] = [];
			}

			this.messageListeners[name].push(callback);
		}
	};

	var ajaxPoller = this.ajaxPoller = {
		options: {
			interval: 10000, /* start off at 10000, when chat starts it'll reduce to 2 */
			alwaysRequest: false
		},
		filterdData: [],
		disable: false,
		maxDelayTimers: [],

		init: function() {
			this.autoSendTimeout = Function_Delay(this.send, this.options.interval, this);
		},


		
		addData: function(data, name, options) {
			name = name || 'default';
			options = options || {};

			if (options.addedTime === undefined) {
				options.addedTime = new Date();
			}

			if (options.maxDelay) {
				Function_Delay(function() {
					this.send();
				}, options.maxDelay, this);
			}

			this.filterdData.push([name, data, options]);
		},

		send: function() {

			this._clearDelays();

			if (this.disable || (!this.options.alwaysRequest && !this.filterdData.length)) {
				this.autoSendTimeout = Function_Delay(this.send, this.options.interval, this);
				return;
			}

			//------------------------------
			// Build data to send
			//------------------------------

			var now = new Date();

			var send_data = [];
			var sent_info = [];

			var filterdData = this.filterdData;
			this.filterdData = [];

			var item = null;
			while (item = filterdData.shift()) {
				var item_name = item[0];
				var item_data = item_orig_data = item[1];
				var item_opts = item[2];

				if (item_opts.minDelay && !(item_opts.minDelayAfterOne && !item_opts.sentCount)) {
					// If its too soon, add it back immediately
					if (item_opts.minDelay > (now.getTime() - item_opts.addedTime.getTime())) {
						this.addData(item_orig_data, item_name, item_opts);
						continue;
					}
				}

				if (typeOf(item_data) == 'function') {
					item_data = item_data(item_name, {}, item_opts);
				}

				if (typeOf(item_data) == 'array') {
					send_data.append(item_data);
				} else {
					Object.each(item_data, function(v, k) {
						send_data.push({ name: k, value: v });
					});
				}

				sent_info.push([item_orig_data, item_name, item_opts]);
			}

			if (!this.options.alwaysRequest && !sent_info.length) {
				this._handleAjaxSuccess({}, sent_info);
				return;
			}

			//------------------------------
			// Send data
			//------------------------------

			$.ajax({
				cache: false,
				url: DpChat.options.deskproUrl + 'chat/poll/' + visitorCode,
				context: this,
				crossDomain: true,
				data: send_data,
				dataType: 'jsonp',
				success: function (data) {
					this._handleAjaxSuccess(data, sent_info);
				}
			});
		},

		_handleAjaxSuccess: function (data, sent_info) {

			var item = null;
			while (item = sent_info.shift()) {
				var item_name = item[0];
				var item_data = item[1];
				var item_opts = item[2];

				if (item_opts.recurring) {
					item_opts.lastSent = new Date();

					if (item_opts.sentCount === undefined) item_opts.sentCount = 0;
					item_opts.sentCount++;

					// Delete addedTime so minDelay check will reset too
					delete item_opts.addedTime;

					this.addData(item_name, item_data, item_opts);
				}
			}

			if (data.messages === undefined || typeOf(data.messages) != 'array') {
				return;
			}

			var message = null;
			while (message = data.messages.shift()) {
				messageBroker.sendMessage(message[0], message[1]);
			}

			// Start auto timer
			this.autoSendTimeout = Function_Delay(this.send, this.options.interval, this);
		},

		_clearDelays: function() {
	
			this.autoSendTimeout = window.clearTimeout(this.autoSendTimeout);
			this.autoSendTimeout = null;

			var t = null;
			while (t = this.maxDelayTimers.pop()) {
				window.clearTimeout(t);
			}
		}
	};


	/**
	 * Sends a new chat message. The server decides if the chat should be new or not
	 * 
	 * @param message
	 */
	this.sendMessage = function(message) {

		DpChatConsole.log('DpChat.sendMessage: %s', message);

		ajaxPoller.options.interval = 2000;

		$.ajax({
			cache: false,
			url: options.deskproUrl + 'chat/send-message/' + visitorCode,
			context: this,
			crossDomain: true,
			data: {content: message},
			dataType: 'jsonp'
		});
	};
	
	//#################################################################
	//# Util
	//#################################################################

	var Function_Delay = function(fn, delay, bind, args) {
		return setTimeout(fn.pass((args == null ? [] : args), bind), delay);
	};


	//#################################################################
	//# Main
	//#################################################################

	/**
	 * Called in the setX methods to run main once all data has been collected
	 */
	var mainRunner = function() {
		if (display && visitorCode) {
			main();
		}
	};

	var main = function() {

		DpChatConsole.log('DpChat.main');

		ajaxPoller.init();
		display.initDisplay();

		if (initialMessages) {
			for (var i = 0; i < initialMessages.length; i++) {
				display.addMessageRow(
					initialMessages[i].name,
					initialMessages[i].message,
					initialMessages[i].type
				);
			}

			display.showChatPanel();
			ajaxPoller.options.interval = 2000;
		}
	};

	return this;
})();
DpChat.init();