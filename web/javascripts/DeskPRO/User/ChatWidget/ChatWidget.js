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

	//##################################################################################################################
	//# Util
	//##################################################################################################################

	var util = {
		createEl: function(html) {
			var div = document.createElement('div');
			div.innerHTML = html;

			return div.firstChild;
		},

		addStyleEl: function(css) {
			var styleEl = document.createElement('style');
			(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(styleEl);
			styleEl.setAttribute('type', 'text/css');

			// IE
			if (style.styleSheet) {
				styleEl.styleSheet.cssText = css;

			// Others
			} else {
				style.appendChild(document.createTextNode(css));
			}

			return styleEl;
		},

		hasClass: function(el, className) {
			if (el.className === "") {
				return false;
			}

			return (" " + el.className + " ").indexOf(" " + className + " ") > -1;
		},

		addClass: function(el, className) {
			if (!this.hasClass(el, className)) {
				el.className += " " + className;
			}
		},

		removeClass: function(el, className) {
			if (this.hasClass(el, className)) {
				el.className.replace(new RegExp("(^|\\s)" + className + "(\\s|$)"), " ").replace(/\s$/, "");
			}
		},

		hideEl: function(el) {
			el.style.display = 'none';
		},

		showEl: function(el) {
			el.style.display = 'block';
		},

		getElWidth: function (el) {
			return el.offsetWidth;
		},

		getElHeight: function(el) {
			return el.offsetHeight;
		},

		removeEl: function(el) {
			el.parentNode.removeChild(el);
		},

		bind: function(el, eventName, callback) {
			if (el.addEventListener) {
				el.addEventListener(eventName, callback);
			} else {
                el.attachEvent("on" + eventName, callback);
			}
		},

		extend: function(obj, obj2) {
			for (var property in obj2) {
				obj[property] = obj2[property];
			}

			return obj;
		}
	};

	//##################################################################################################################
	//# Chat Widget
	//##################################################################################################################

	var body = document.body;
	var tmp, tmpi;

	var options = {
		protocol: null,
		staticUrl: null,
		deskproUrl: null,
		btnClass: 'dp-chat-btn'
	};

	var self = this;

	/**
	 * Is the chat currently open?
	 * @var {Boolean}
	 */
	var isOpen = false;

	/**
	 * The chat iFrame
	 * @var {HTMLElement}
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
	 * Contains whether the page being viewed is RTL.
	 */
	var isRtl = false;

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
			css.push((isRtl ? 'left' : 'right') + ': 20px');
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

			chatIframeHolder = util.createEl('<div id="dp_chat_iframe_holder" class="dp-chat-iframe-holder" style="' + css  +'" />');
			body.appendChild(chatIframeHolder);


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
			css.push((isRtl ? 'left' : 'right') + ': 28px');
			css.push('font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif');
			css.push('cursor: pointer');
			css.push('-webkit-box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css.push('box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css = css.join(';');

			var phrase = 'Open this chat in a new window';
			if (typeof DESKPRO_LANG != 'undefined' && DESKPRO_LANG['user.chat.window_open-new']) {
				phrase = DESKPRO_LANG['user.chat.window_open-new'];
			}
			chatIframeWinTab = util.createEl('<div id="dp_chat_iframe_wintab" class="dp-chat-iframe-wintab" style="' + css + '">' + phrase + '</div>');
			chatIframeHolder.appendChild(chatIframeWinTab);
			util.bind(chatIframeWinTab, 'click', openInWindow);

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
			css.push((isRtl ? 'left' : 'right') + ': 2px');
			css.push('font-family: \'Helvetica Neue\',Helvetica,Arial,sans-serif');
			css.push('cursor: pointer');
			css.push('-webkit-box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css.push('box-shadow:  0px -1px 3px 1px rgba(0, 0, 0, 0.2)');
			css = css.join(';');

			var phrase = 'Minimize';
			if (typeof DESKPRO_LANG != 'undefined' && DESKPRO_LANG['user.chat.window_open-minimize']) {
				phrase = DESKPRO_LANG['user.chat.window_open-minimize'];
			}
			tmp = util.createEl('<div id="dp_chat_iframe_closebtn" class="dp-chat-iframe-closetab" title="'+phrase+'" style="' + css + '">&#9660;</div>');
			chatIframeHolder.appendChild(tmp);
			util.bind(tmp, 'click', function() { self.close() });

			isNew = true;

			css = [];
			css.push('position: absolute');
			css.push('bottom: 0');
			css.push('top: 0');
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

			qs += '&parent_url=' + encodeURIComponent(window.location.href);

			frameSrc = options.deskproUrl + 'widget/chat.html' + qs;
			chatIframe = util.createEl('<iframe id="dp_chat_iframe" name="dp_chat_iframe" src="' + frameSrc + '" style="' + css  +'" align="middle" frameborder="0" marginheight="0" marginwidth="0" scrolling="no"></iframe>');
			chatIframeHolder.appendChild(chatIframe);

			comms.setupReciever(childListen, frameSrc);

			util.bind(chatIframe, 'click', function(ev) {
				if (ev && ev.stopPropagation) ev.stopPropagation();
				else window.event.cancelBubble = true;
			});
		}

		isOpen = true;
		util.showEl(chatIframeHolder);
	};


	/**
	 * Closes the overlay
	 */
	this.close = function() {
		if (!isOpen) {
			return;
		}

		isOpen = false;
		util.hideEl(chatIframeHolder);
	};


	//##################################################################################################################
	//# Initialize Helpers
	//##################################################################################################################

	function openInWindow() {
		var src = chatIframe.src;
		src = src.replace(/\?/, '?is_window_mode=1&');

		window.open(src, 'dpchatwin','width=500,height=400,location=0,menubar=0,scrollbars=0,status=0,toolbar=0,resizable=1');

		util.removeEl(chatIframeHolder);
		util.hideEl(openBtn);
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
		isRtl = (document.documentElement && document.documentElement.dir && document.documentElement.dir == 'rtl');

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

	this.setNotAvailable = function() {
		util.addClass(body, 'dp-chat-disabled');
	},

	this.initWidget = function(sessionId) {

		if (sessionId) {
			setCookie('dpchat_sid', sessionId, 7);
		}

		DpConsole.log('DpChatWidget.initWidget');

		if (window.DpChatWidget_Options) {
			util.extend(options, window.DpChatWidget_Options);
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
		css.push((isRtl ? 'left' : 'right') + ': 150px');
		css.push('margin: 0');
		css.push('padding: 0 15px');
		css.push('height: 18px');
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
		css.push('border-' + (isRtl ? 'left' : 'right') + ': none');
		css.push('border-top: none');
		css.push('text-shadow: 0px 0px 2px #000000');
		css.push('opacity: 0.85');
		css = css.join(';');

		util.addStyleEl('#dpchat_btn { '+css+ '}');

		var isIE  = (navigator && navigator.appName && navigator.appName == 'Microsoft Internet Explorer');
		var ieVer = 0;
		if (isIE) {
			var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(navigator.userAgent) != null) ieVer = parseFloat(RegExp.$1);
		}

		var css = [];
		css.push('position: fixed');
		css.push('bottom: 0');
		css.push((isRtl ? 'left' : 'right') + ': 20px');
		css.push('margin: 0');
		css.push('height: 7px');
		css.push('padding: 0');
		css.push('line-height: 100%');
		css.push('box-shadow: none');
		css.push('-webkit-border-top-' + (isRtl ? 'left' : 'right') + '-radius: 9px');
		css.push('-moz-border-radius-top' + (isRtl ? 'left' : 'right') + ': 9px');
		if (isRtl && !isIE) {
			// IE has a bug where the background bleeds through the border radius in RTL
			css.push('border-top-' + (isRtl ? 'left' : 'right') + '-radius: 9px');
		}
		css.push('cursor: pointer');
		css.push('background: ' + bgColor);
		css.push('background: ' + bgColorA);
		css.push('border: ' + border);
		css.push('border-bottom: none');
		css.push('border-' + (isRtl ? 'right' : 'left') + ': 0 none');
		css = css.join(';');

		util.addStyleEl('#dpchat_btn_btm { '+css+ '}');

		var css = [];
		css.push('position: absolute');
		css.push('top: 0px');
		css.push('bottom: 0');
		css.push((isRtl ? 'left' : 'right') + ': -6px');
		css.push('width: 6px');
		css.push('margin: 0');
		css.push('height: 9px');
		css.push('padding: 0');
		css.push('line-height: 100%');
		css.push('box-shadow: none');
		css.push('-webkit-border-bottom-' + (isRtl ? 'right' : 'left') + '-radius: 6px');
		css.push('-moz-border-radius-bottom' + (isRtl ? 'right' : 'left') + ': 6px');
		css.push('border-bottom-' + (isRtl ? 'right' : 'left') + '-radius: 6px')
		css.push('cursor: pointer');
		css.push('display: block');
		css.push('background: transparent');
		css.push('border: ' + border);
		css.push('border-top: none');
		css.push('border-' + (isRtl ? 'left' : 'right') + ': none');
		css.push('z-index: 1');
		css = css.join(';');

		util.addStyleEl('#dpchat_btn_inner2 { '+css+ '}');

		var css = [];
		css.push('position: absolute');
		css.push('top: -11px');
		css.push('height: 6px');
		css.push((isRtl ? 'left' : 'right') + ': 0');
		css.push((isRtl ? 'right' : 'left') + ': -2px');
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

		util.addStyleEl('#dpchat_btn_inner { '+css+ '}');

		var css = [];
		css.push('position: absolute');
		css.push('bottom: 0');
		css.push('height: 9px');
		css.push('width: 7px');
		css.push('overflow: hidden');
		css.push((isRtl ? 'right' : 'left') + ': -7px');
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

		util.addStyleEl('#dpchat_btn_btm_shade { '+css+ '}');

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

		openBtn = util.createEl('<div id="dpchat_btn" class="dp-hide-print"><div id="dpchat_btn_inner"></div><div id="dpchat_btn_inner2"></div><div id="dpchat_btn_label" style="position:relative;top:-1px;"><span id="dpchat_btn_label_start-chat" class="start-chat">'+phrase1+'</span><span id="dpchat_btn_label_open-chat" class="open-chat" style="display: none">'+phrase2+'</span></div><div id="dpchat_btn_btm" class="dp-hide-print"><div id="dpchat_btn_btm_shade"></div></div></div>');
		if (isRtl) {
			util.addClass(openBtn, 'rtl');
		}
		if (this.isWindowChat) {
			util.hideEl(openBtn);
		}
		body.appendChild(openBtn);

		var w = util.getElWidth(document.getElementById('dpchat_btn'));
		document.getElementById('dpchat_btn_btm').style.width = (w - 20 + 150 - w - 8) + "px";

		util.bind(openBtn, 'click', function(ev) {
			if (ev && ev.preventDefault) ev.preventDefault();
			else window.event.returnValue = false;

			if (ev && ev.stopPropagation) ev.stopPropagation();
			else window.event.cancelBubble = true;

			self.open();
		});

		if (this.isWindowChat) {

		} else {
			if (this.doResume) {
				self.open();
			} else {
				util.showEl(openBtn);
			}
		}

		util.addClass(body, 'dp-chat-enabled');
		if (document.getElementsByClassName) {
			tmp = document.getElementsByClassName('dp-chat-trigger');
			for (tmpi = 0; tmpi < tmp.length; tmpi++) {
				util.bind(tmp[tmpi], 'click', function(ev) {
					if (ev && ev.preventDefault) ev.preventDefault();
					else window.event.returnValue = false;

					DpChatWidget.open();
				});
			}
		}
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
				util.hideEl(document.getElementById('dpchat_btn_label_start-chat'));
				util.showEl(document.getElementById('dpchat_btn_label_open-chat'));

				// The button might be hidden because of doResume above,
				// but we want to show it all the time (its overlapped anyway)
				util.showEl(openBtn);
				break;

			case 'hide':
				self.close();
				break;

			case 'show':
				self.open();
				break;

			case 'destroy':
				self.close();
				comms.reset();
				chatIframeHolder = null;
				chatIframeWinTab = null;
				chatIframe = null;

				if (chatIframeHolder) {
					util.removeEl(chatIframeHolder);
				}

				if (!comms.hasPostMessage) {
					var targetLoc = window.location.href + '';
					window.location.replace(targetLoc.replace(/#.*$/, '') + '#');
				}
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
		hasPostMessage: window.postMessage && (!isIE || ieVer > 9),
		cacheBust: 0,
		recieveCallback: null,
		send: function(message, targetUrl, target) {
			if (this.hasPostMessage) {
				target.postMessage(message, targetUrl.replace(/([^:]+:\/\/[^\/]+).*/, '$1'))
			} else {
				var targetLoc = targetUrl;
				target.location.replace(targetLoc.replace(/#.*$/, '') + '#' + (+new Date) + (this.cacheBust++) + '&' + message);

				if (this.resetHashTimeout) {
					window.clearTimeout(this.resetHashTimeout);
				}
				this.resetHashTimeout = window.setTimeout(function() {
					target.location.replace(targetLoc.replace(/#.*$/, '') + '#');
				}, 95);
			}
		},
		reset: function() {
			this.recieveCallback = null;
			this.lastHash = null;
			if (this.intervalId) {
				window.clearInterval(this.intervalId);
			}
		},
		setupReciever: function(callback, sourceUrl) {
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
					}, 60);
				}
			}
		}
	};

	//##################################################################################################################
	//# Initialize
	//##################################################################################################################

	DpConsole.log('DpChatWidget.init');
	initSession();

	return this;
})();
