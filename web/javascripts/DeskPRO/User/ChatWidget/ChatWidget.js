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

if (!window.Dp_WaitingLibLoad) {
	window.Dp_WaitingLibLoad = [];
}

var DpChatWidget = new (function() {

	var options = {
		protocol: null,
		staticUrl: null,
		deskproUrl: null,
		btnClass: 'dp-chat-btn'
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
	var chatIframeHolder;
	var chatIframeWinTab;
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

		if (this.toLoginPage) {
			window.location = options.deskproUrl + 'login';
			return;
		}

		if (isOpen) {
			return;
		}

		if (!chatIframe) {

			var css = [];
			css.push('position: fixed');
			css.push('bottom: 0');
			css.push('right: 20px');
			css.push('width: 340px');
			css.push('height: 350px');
			css.push('background: #ffffff');
			css.push('margin: 0');
			css.push('padding: 0');
			css.push('box-shadow: none');
			css.push('border: 3px solid #2A69A9');
			css.push('border-bottom: none');
			css.push('-moz-background-clip: padding');
			css.push('-webkit-background-clip: padding-box');
			css.push('background-clip: padding-box');
			css.push('-webkit-border-top-left-radius: 4px');
			css.push('-webkit-border-top-right-radius: 4px');
			css.push('-moz-border-radius-topleft: 4px');
			css.push('-moz-border-radius-topright: 4px');
			css.push('border-top-left-radius: 4px');
			css.push('border-top-right-radius: 4px');
			css.push('-webkit-box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css.push('box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css.push('z-index: 90000');
			css = css.join(';');

			chatIframeHolder = $('<div id="dp_chat_iframe_holder" class="dp-chat-iframe-holder" style="' + css  +'" />').appendTo('body');

			// The little tabby thing at the top
			var css = [];
			css.push('background: #2A69A9');
			css.push('color: #ffffff');
			css.push('-webkit-border-top-left-radius: 4px');
			css.push('-webkit-border-top-right-radius: 4px');
			css.push('-moz-border-radius-topleft: 4px');
			css.push('-moz-border-radius-topright: 4px');
			css.push('border-top-left-radius: 4px');
			css.push('border-top-right-radius: 4px');
			css.push('z-index: 90001');
			css.push('font-size: 10px');
			css.push('line-height: 100%');
			css.push('padding: 3px 5px 3px 5px');
			css.push('position: absolute');
			css.push('top: -18px');
			css.push('right: 28px');
			css.push('font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif');
			css.push('cursor: pointer');
			css.push('-webkit-box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css.push('box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css = css.join(';');

			var phrase = 'Open this chat in a new window';
			if (typeof DESKPRO_LANG != 'undefined' && DESKPRO_LANG['user.chat.window_open-new']) {
				phrase = DESKPRO_LANG['user.chat.window_open-new'];
			}
			chatIframeWinTab = $('<div id="dp_chat_iframe_wintab" class="dp-chat-iframe-wintab" style="' + css + '">' + phrase + '</div>').on('click', openInWindow).appendTo(chatIframeHolder);

			// Minmize button
			var css = [];
			css.push('background: #2A69A9');
			css.push('color: #ffffff');
			css.push('-webkit-border-top-left-radius: 4px');
			css.push('-webkit-border-top-right-radius: 4px');
			css.push('-moz-border-radius-topleft: 4px');
			css.push('-moz-border-radius-topright: 4px');
			css.push('border-top-left-radius: 4px');
			css.push('border-top-right-radius: 4px');
			css.push('z-index: 90001');
			css.push('font-size: 10px');
			css.push('line-height: 100%');
			css.push('padding: 3px 5px 3px 5px');
			css.push('position: absolute');
			css.push('top: -18px');
			css.push('right: 2px');
			css.push('font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif');
			css.push('cursor: pointer');
			css.push('-webkit-box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css.push('box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css = css.join(';');

			var phrase = 'Minimize';
			if (typeof DESKPRO_LANG != 'undefined' && DESKPRO_LANG['user.chat.window_open-minimize']) {
				phrase = DESKPRO_LANG['user.chat.window_open-minimize'];
			}
			$('<div id="dp_chat_iframe_closebtn" class="dp-chat-iframe-closetab" title="'+phrase+'" style="' + css + '">&#9660;</div>').on('click', function() { self.close() }).appendTo(chatIframeHolder);

			isNew = true;

			css = [];
			css.push('position: absolute');
			css.push('bottom: 0');
			css.push('top: 0');
			css.push('left: 3');
			css.push('right: 30');
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
			chatIframe = $('<iframe id="dp_chat_iframe" name="dp_chat_iframe" src="' + frameSrc + '" style="' + css  +'" align="middle" frameborder="0" marginheight="0" marginwidth="0" scrolling="no"></iframe>').appendTo(chatIframeHolder);

			comms.setupReciever(childListen, frameSrc);

			chatIframe.on('click', function(ev) {
				ev.stopPropagation();
			});
		}

		isOpen = true;
		chatIframeHolder.show();
	};


	/**
	 * Closes the overlay
	 */
	this.close = function() {
		if (!isOpen) {
			return;
		}

		isOpen = false;
		chatIframeHolder.hide();
	};


	//##################################################################################################################
	//# Initialize Helpers
	//##################################################################################################################

	function openInWindow() {
		var src = chatIframe.get(0).src;
		src = src.replace(/\?/, '?is_window_mode=1&');

		window.open(src, 'dpchatwin','width=500,height=400,location=0,menubar=0,scrollbars=0,status=0,toolbar=0,resizable=1');

		chatIframeHolder.remove();
		openBtn.hide();
	};

	function setCookie(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		} else {
			var expires = "";
		}
		document.cookie = name+"="+value+expires+"; path=/";
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

		if (typeof DESKPRO_SESSION_ID != 'undefined') {
			var sid = DESKPRO_SESSION_ID;
		} else {
			var sid = getCookie('dpchat_sid');
		}
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

		url += '&_ts=' + ((new Date()).getTime());

		DpConsole.log('DpChatWidget.initSession: adding script: ' + url);

		var script_tag = document.createElement('script');
		script_tag.setAttribute("type", "text/javascript");
		script_tag.setAttribute("src", url);
		script_tag.setAttribute("async", 'true');
		(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
	};

	function initJquery() {
		window.Dp_WaitingLibLoad.push(function() {
			DpConsole.log('DpChatWidget.init: jquery loaded');
			$ = window.Dp_jQuery;
			initSession();
		});

		if (!window.Dp_JqueryScript) {
			window.oldJquery = window.jQuery;
			window.old$ = window.$;

			function jquery_loaded() {
				window.Dp_jQuery = window.jQuery.noConflict(true);

				window.jQuery = window.oldJquery;
				window.$ = window.old$;

				window.oldJquery = null;
				window.old$ = null;

				var i;
				for (i = 0; i < window.Dp_WaitingLibLoad.length; i++) {
					window.Dp_WaitingLibLoad[i]();
				}

				window.Dp_WaitingLibLoad = [];
			};

			var script_tag = document.createElement('script');
			window.Dp_JqueryScript = script_tag;

			script_tag.setAttribute("type", "text/javascript");
			script_tag.setAttribute("src", ('https:' == document.location.protocol ? 'https' : 'http') + "://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
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
		}
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

		var bgColor  = 'rgb(63,63,63)';
		var bgColorA = 'rgb(63,63,63)';
		var border   = '2px solid #727272';

		if (DpChatWidget_Options && DpChatWidget_Options.btnStyle) {
			if (DpChatWidget_Options.btnStyle.bgColor) {
				bgColor = DpChatWidget_Options.btnStyle.bgColor;
			}
			if (DpChatWidget_Options.btnStyle.bgColorA) {
				bgColorA = DpChatWidget_Options.btnStyle.bgColorA;
			}
			if (DpChatWidget_Options.btnStyle.border) {
				border = DpChatWidget_Options.btnStyle.border;
			}
		}

		var css = [];
		css.push('position: fixed');
		css.push('bottom: 0');
		css.push('right: 150px');
		css.push('margin: 0');
		css.push('padding: 0 15px 0 15px');
		css.push('height: 23px');
		css.push('line-height: 100%');
		css.push('box-shadow: none');
		css.push('color: #fff');
		css.push('font-family: Arial, sans-serif');
		css.push('font-weight: bold');
		css.push('font-size: 12px');
		css.push('cursor: pointer');
		css.push('display: none');
		css.push('background: ' + bgColor);
		css.push('background: ' + bgColorA);
		css.push('border: ' + border);
		css.push('border-bottom: none');
		css.push('border-right: none');
		css.push('border-top: none');
		css.push('text-shadow: 0px 0px 2px #000000');
		css.push('opacity: 0.85');
		css = css.join(';');

		$('head').append('<style type="text/css">#dpchat_btn { '+css+ '}</style>');

		var css = [];
		css.push('position: fixed');
		css.push('bottom: 0');
		css.push('right: 20px');
		css.push('margin: 0');
		css.push('height: 12px');
		css.push('padding: 0');
		css.push('line-height: 100%');
		css.push('box-shadow: none');
		css.push('-webkit-border-top-right-radius: 9px');
		css.push('-moz-border-radius-topright: 9px');
		css.push('border-top-right-radius: 9px')
		css.push('cursor: pointer');
		css.push('background: ' + bgColor);
		css.push('background: ' + bgColorA);
		css.push('border: ' + border);
		css.push('border-bottom: none');
		css.push('border-left: none');
		css.push('border-left: none');
		css = css.join(';');

		$('head').append('<style type="text/css">#dpchat_btn_btm { '+css+ '}</style>');

		var css = [];
		css.push('position: absolute');
		css.push('top: 0px');
		css.push('bottom: 0');
		css.push('right: -6px');
		css.push('width: 6px');
		css.push('margin: 0');
		css.push('height: 9px');
		css.push('padding: 0');
		css.push('line-height: 100%');
		css.push('box-shadow: none');
		css.push('-webkit-border-bottom-left-radius: 6px');
		css.push('-moz-border-radius-bottomleft: 6px');
		css.push('border-bottom-left-radius: 6px')
		css.push('cursor: pointer');
		css.push('display: block');
		css.push('background: transparent');
		css.push('border: ' + border);
		css.push('border-top: none');
		css.push('border-right: none');
		css.push('z-index: 1');
		css = css.join(';');

		$('head').append('<style type="text/css">#dpchat_btn_inner2 { '+css+ '}</style>');

		var css = [];
		css.push('position: absolute');
		css.push('top: -11px');
		css.push('height: 6px');
		css.push('right: 0');
		css.push('left: -2px');
		css.push('height: 6px');
		css.push('margin: 0');
		css.push('height: 9px');
		css.push('padding: 0');
		css.push('line-height: 100%');
		css.push('box-shadow: none');
		css.push('-webkit-border-top-left-radius: 9px');
		css.push('-webkit-border-top-right-radius: 9px');
		css.push('-moz-border-radius-topleft: 9px');
		css.push('-moz-border-radius-topright: 9px');
		css.push('border-top-left-radius: 9px');
		css.push('border-top-right-radius: 9px');
		css.push('cursor: pointer');
		css.push('display: block');
		css.push('background: ' + bgColor);
		css.push('background: ' + bgColorA);
		css.push('border: ' + border);
		css.push('border-bottom: none');
		css = css.join(';');

		$('head').append('<style type="text/css">#dpchat_btn_inner { '+css+ '}</style>');

		var css = [];
		css.push('position: absolute');
		css.push('bottom: 0');
		css.push('height: 14px');
		css.push('width: 7px');
		css.push('overflow: hidden');
		css.push('left: -7px');
		css.push('margin: 0');
		css.push('padding: 0');
		css.push('line-height: 100%');
		css.push('box-shadow: none');
		css.push('cursor: pointer');
		css.push('display: block');
		css.push('background: ' + bgColor);
		css.push('background: ' + bgColorA);
		css.push('border: none');
		css = css.join(';');

		$('head').append('<style type="text/css">#dpchat_btn_btm_shade { '+css+ '}</style>');

		var phrase1 = 'Chat with us';
		var phrase2 = 'Open your chat';
		if (DpChatWidget_Options && DpChatWidget_Options.lang) {
			if (DpChatWidget_Options.lang['user.chat.window_start-button']) {
				phrase1 = DpChatWidget_Options.lang['user.chat.window_start-button'];
			}
			if (DpChatWidget_Options.lang['user.chat.window_resume-button']) {
				phrase2 = DpChatWidget_Options.lang['user.chat.window_resume-button'];
			}
		}

		openBtn = $('<div id="dpchat_btn" class="dp-hide-print"><div id="dpchat_btn_inner"></div><div id="dpchat_btn_inner2"></div><div id="dpchat_btn_label"><span class="start-chat">'+phrase1+'</span><span class="open-chat" style="display: none">'+phrase2+'</span></div><div id="dpchat_btn_btm" class="dp-hide-print"><div id="dpchat_btn_btm_shade"></div></div></div>');
		if (this.isWindowChat) {
			openBtn.hide();
		}
		openBtn.appendTo('body');

		var w = $('#dpchat_btn').width();
		$('#dpchat_btn_btm').width(w - 20 + 150 - w - 8);

		openBtn.on('click', function(ev) {
			ev.stopPropagation();
			ev.preventDefault();
			self.open();
		});

		if (this.isWindowChat) {

		} else {
			if (this.doResume) {
				self.open();
			} else {
				openBtn.show();
			}
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

		console.log('[ChatWidget] comms received: %s %o', messageId, data);

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
				if (chatIframeHolder) {
					chatIframeHolder.remove();
				}
				chatIframeHolder = null;
				chatIframeWinTab = null;
				chatIframe = null;
				comms.setupReciever(null, null);
				break;
		}
	};

	var isIE  = (navigator && navigator.appName && navigator.appName == 'Microsoft Internet Explorer');
	var ieVer = 0;
	if (isIE) {
		var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(navigator.userAgent) != null) ieVer = parseFloat(RegExp.$1);
	}

	var comms = {
		intervalId: null,
		lastHash: null,
		hasPostMessage: window.postMessage && (!isIE || ieVer > 8),
		cacheBust: 0,
		pollingInterval: 130,
		recieveCallback: null,
		send: function(message, targetUrl, target) {
			if (this.hasPostMessage) {
				target.postMessage(message, targetUrl.replace( /([^:]+:\/\/[^\/]+).*/, '$1'))
			} else {
				var targetLoc = target.location + '';
				target.location = targetLoc.replace(/#.*$/, '') + '#' + (+new Date) + (this.cacheBust++) + '&' + message;
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
						if (hash !== me.lastHash && re.test(hash)) {
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
