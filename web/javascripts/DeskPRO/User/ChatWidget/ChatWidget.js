if (window.Dp_EnableDebug) {
	var DpConsole = window.console;
} else {
	var DpConsole = {};
	DpConsole['error'] = function(){};
	DpConsole['log'] = function(){};
	DpConsole['warn'] = function(){};
	DpConsole['info'] = function(){};
	DpConsole['debug'] = function(){};
}

var DpChatWidget = new (function() {

	var options = {
		protocol: null,
		staticUrl: null,
		deskproUrl: null
	};

	var self = this;

	/**
	 * Scoped reference to jQuery
	 * @var {jQuery}
	 */
	var $ = null;

	/**
	 * Is the chat currently open?
	 * @var {Boolean}
	 */
	var isOpen = false;

	/**
	 * The chat iFrame
	 * @var {jQuery}
	 */
	var chatIframe;
	var closingChatIframe;

	/**
	 * The URL of the iframe
	 */
	var frameSrc;

	/**
	 * The button on the page that the user clicks to open the chat iframe
	 */
	var openBtn;

	/**
	 * The child iframe talks to us
	 *
	 * @param {String} messageId
	 * @param {Object} [data]
	 */
	this.childListen = function(messageId, data) {

	};


	/**
	 * Opens the overlaying iframe
	 */
	this.open = function(data) {
		if (isOpen) {
			return;
		}

		if (!chatIframe) {
			isNew = true;

			var css = [];
			css.push('position: fixed');
			css.push('bottom: 0');
			css.push('right: 20px');
			css.push('width: 340px');
			css.push('height: 350px');
			css.push('margin: 0');
			css.push('padding: 0');
			css.push('box-shadow: none');
			css.push('overflow: hidden');
			css = css.join(';');

			var qs = '?';

			var sid = getCookie('dpchat_sid');
			if (sid) {
				qs += '__sid=' + sid;
			}

			if (data) {
				for (var i = 0; i < data.length; i++) {
					qs += '&' + encodeURIComponent(data[i][0]) + '=' + encodeURIComponent(data[i][1]);
				}
			}

			frameSrc = options.deskproUrl + 'widget/chat.html' + qs + '#' + encodeURIComponent(document.location.href);
			chatIframe = $('<iframe id="dp_chat_iframe" name="dp_chat_iframe" src="' + frameSrc + '" style="' + css  +'" align="middle" frameborder="0" marginheight="0" marginwidth="0" scrolling="no"></iframe>').appendTo('body');

			comms.setupReciever(childListen, frameSrc);

			chatIframe.on('click', function(ev) {
				ev.stopPropagation();
			});
		}

		isOpen = true;
		chatIframe.show();
	};


	/**
	 * Closes the overlay
	 */
	this.close = function() {
		if (!isOpen) {
			return;
		}

		isOpen = false;
		chatIframe.hide();
	};


	//##################################################################################################################
	//# Initialize Helpers
	//##################################################################################################################

	function setCookie(c_name,value,exdays) {
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	};

	function getCookie(c_name) {
		var i,x,y,ARRcookies=document.cookie.split(";");

		for (i=0;i<ARRcookies.length;i++) {
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x=x.replace(/^\s+|\s+$/g,"");
			if (x==c_name) {
				return unescape(y);
			}
		}
	};

	function initSession() {
		// Now load our session script
		// DeskPRO script that sets/gets session and initial messages
		var url = DpChatWidget_Options.deskproUrl + 'chat/chat-session?_1=';
		if (DpChatWidget_Options && DpChatWidget_Options.currentPageUrl) {
			url += DpChatWidget_Options.currentPageUrl;
		} else {
			url += encodeURIComponent(document.location.href);
		}
		url += '&_2=';

		var sid = getCookie('dpchat_sid');
		if (sid) {
			url += '&__sid=' + sid + '&';
		}

		if (DpChatWidget_Options && DpChatWidget_Options.referrerPageUrl) {
			url += encodeURIComponent(document.location.href);
		} else {
			url += encodeURIComponent(document.referrer);
		}

		url += '&'+(new Date().getTime());

		if (options.displayType == 'DpWindow') {
			url += '&is_window=1';
		}

		DpConsole.log('DpChatWidget.initSession: adding script: ' + url);

		var script_tag = document.createElement('script');
		script_tag.setAttribute("type", "text/javascript");
		script_tag.setAttribute("src", url);
		script_tag.setAttribute("async", 'true');
		(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
	};

	function initJquery() {
		function jquery_loaded() {
			DpConsole.log('DpChatWidget.init: jquery loaded');
			$ = window.jQuery.noConflict(true);

			initSession();
		};

		var script_tag = document.createElement('script');
		script_tag.setAttribute("type", "text/javascript");
		script_tag.setAttribute("src", ('https:' == document.location.protocol ? 'https' : 'http') + "://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js");
		script_tag.setAttribute("async", 'true');
		script_tag.onload = function() {
			jquery_loaded();
		};
		script_tag.onreadystatechange = function () { // Same thing but for IE
			if (this.readyState == 'complete' || this.readyState == 'loaded') {
				jquery_loaded();
			}
		};

		(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
	};

	this.setNotAvailable = function() {
		$('body').addClass('dp-chat-disabled');
	},

	/**
	 * initWidget() is called when we know we've got jQuery
	 */
	this.initWidget = function(sessionId) {

		if (sessionId) {
			setCookie('dpchat_sid', sessionId, 7);
		}

		DpConsole.log('DpChatWidget.initWidget');

		if (window.DpChatWidget_Options) {
			options = $.extend({}, options, window.DpChatWidget_Options);
		}

		if (window.DpChatWidget_Options.interceptLeavingDomains) {
			$(document).on('click', 'a', function(ev) {
				if ($(this).is('dp-no-touch')) {
					return;
				}

				var href = $(this).attr('href');
				if (!href) return;

				var m = href.match(/:\/\/(.[^/]+)/)[1];
				if (!m || !m[1]) return;

				var domain = m[1];

				var foundDomain = false;

				Array.each(DpChatWidget_Options.interceptLeavingDomains, function(checkDomain) {
					if (domain == checkDomain || domain.indexOf(checkDomain) !== -1) {
						foundDomain = true;
						return false;
					}
				});

				if (!foundDomain) {
					if (!confirmGoingAway()) {
						ev.preventDefault();
						ev.stopPropagation();
					}
				}
			});
		}

		var css = [];
		css.push('position: fixed');
		css.push('bottom: 0');
		css.push('right: 20px');
		css.push('height: 20px');
		css.push('margin: 0');
		css.push('padding: 5px 15px 5px 15px');
		css.push('box-shadow: none');
		css.push('border: 1px solid #09184F');
		css.push('background: #1A2757');
		css.push('color: #fff');
		css.push('font-family: Arial, sans-serif');
		css.push('font-weight: bold');
		css.push('font-size: 12px');
		css.push('cursor: pointer');
		css.push('overflow: hidden');
		css.push('display: none');
		css = css.join(';');

		openBtn = $('<div id="dpchat_btn" class="dp-hide-print" style="'+css+'"><div id="dpchat_btn_label"><span class="start-chat">Click here to chat with us</span><span class="open-chat" style="display: none">Open your chat</span></div></div>');
		openBtn.appendTo('body');

		openBtn.on('click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			self.open();
		});

		if (this.doResume) {
			self.open();
		} else {
			openBtn.show();
		}

		$('body').addClass('dp-chat-enabled');
		$('.dp-chat-trigger').on('click', function(ev) {
			ev.preventDefault();
			DpChatWidget.open();
		});
	};

	var confirmGoingAway = function() {
		if (hasStarted && !hasEnded) {
			return confirm('Are you sure you want to leave our website? Your chat will be closed.');
		}
	};

	//##################################################################################################################
	// Comms interface to the chat window
	//##################################################################################################################

	function childListen(messageData) {

		if (messageData && messageData.data) {
			messageData = messageData.data;
		}

		var data = messageData.split(':');
		var messageId = data.shift();

		console.log('[ChatWidget] comms recieved: %s %o', messageId, data);

		switch (messageId) {
			case 'started':
				$('#dpchat_btn_label').find('.start-chat').hide();
				$('#dpchat_btn_label').find('.open-chat').show();

				// The button might be hidden because of doResume above,
				// but we want to show it all the time (its overlapped anyway)
				openBtn.show();
				break;

			case 'hide':
				self.close();
				break;

			case 'show':
				chatIframe.hide();
				break;

			case 'destroy':
				self.close();
				chatIframe.remove();
				chatIframe = null;
				comms.setupReciever(null, null);
				break;
		}
	};

	var comms = {
		intervalId: null,
		lastHash: null,
		hasPostMessage: window.postMessage,
		cacheBust: 0,
		pollingInterval: 130,
		recieveCallback: null,
		send: function(message, targetUrl, target) {
			if (this.hasPostMessage) {
				target.postMessage(message, targetUrl.replace( /([^:]+:\/\/[^\/]+).*/, '$1'))
			} else {
				target.location = targetUrl.replace( /#.*$/, '' ) + '#' + (+new Date) + (this.cacheBust++) + '&' + message;
			}
		},
		setupReciever: function(callback, sourceUrl) {
			// Unset existing
			if (callback && this.recieveCallback) {
				this.recieveCallback = null;
				this.setupReciever(null, '');
			}

			this.recieveCallback = callback;

			if (this.hasPostMessage) {
				if (window.addEventListener) {
					window[this.recieveCallback ? 'addEventListener' : 'removeEventListener']('message', this.recieveCallback, false);
				} else {
					window[this.recieveCallback ? 'attachEvent' : 'detachEvent' ]('onmessage', this.recieveCallback);
				}
			} else {
				if (this.intervalId) {
					window.clearInterval(this.intervalId);
				}

				if (this.recieveCallback) {
					var me = this;
					this.intervalId = window.setInterval(function() {
						var hash = document.location.hash;
						var re = /^#?\d+&/;
						if (hash !== last_hash && re.test(hash)) {
							me.lastHash = hash;
							me.recieveCallback({ data: hash.replace( re, '') });
						}
					});
				}
			}
		}
	};

	//##################################################################################################################
	//# Initialize
	//##################################################################################################################

	DpConsole.log('DpChatWidget.init');

	if (window.jQuery === undefined || window.jQuery.fn.jquery.indexOf('1.7.') === -1) {
		DpConsole.log('DpChatWidget.init: loading jquery');
		initJquery();
	} else {
		DpConsole.log('DpChatWidget.init: already have jquery');
		$ = jQuery;
		initSession();
	}

	return this;
})();
