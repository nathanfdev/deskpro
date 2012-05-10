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

var DpOverlayWidget = new (function() {

	var options = {
		protocol: null,
		staticUrl: null,
		deskproUrl: null,
		tabLocation: 'left',
		tabClass: ''
	};

	var self = this;

	/**
	 * Scoped reference to jQuery
	 * @var {jQuery}
	 */
	var $ = null;

	/**
	 * The overlay backdrop div
	 * @var {jQuery}
	 */
	var overlayBack = null;

	/**
	 * The overlay wrapper
	 * @var {jQuery}
	 */
	var overlayWrap = null;

	/**
	 * The inner overlay wrapper
	 * @var {jQuery}
	 */
	var overlayWrapInner = null;


	/**
	 * The overlay iframe
	 * @var {jQuery}
	 */
	var overlayIframe = null;

	/**
	 * The overlay the loads to the right of the left pane for alt content
	 */
	var contentWrap;

	/**
	 * Is the overlay currently open?
	 * @var {Boolean}
	 */
	var isOpen = false;

	/**
	 * The current window height
	 * @var {Integer}
	 */
	var winHeight = 0;

	/**
	 * The current window width
	 * @var {Integer}
	 */
	var winWidth  = 0;

	var lastWinHeight = 0;
	var lastWinWidth = 0;
	var childRequestedHeight = 350;

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

	/**
	 * The child iframe talks to us through this method.
	 *
	 * Note that the parent never talks to the child. All communication is done through the child
	 * asking the parent for information.
	 *
	 * @param {Array} messageData
	 */
	this.childListen = function(messageData) {

		if (messageData && messageData.data) {
			messageData = messageData.data;
		}

		var messageData = messageData.split(':');
		var messageId = messageData.shift();

		console.log('[ChatWidget] comms recieved: %s %o', messageId, data);

		var self = this;
		console.log('[Recieving] %s', messageId);

		switch (messageId) {

			case 'closeMe':
				self.close();
				break;

			// When the child wants to resize to a certain height (ie to accomodate more stuff) they send this message
			// Afterwards we pass back the height we were able to set which may be smaller than it wanted
			case 'requestHeight':
				var height = messageData[0];
				var winMaxHeight = winHeight - 40;

				if (height < 600) {
					height = 600;
				}
				if (height > winMaxHeight) {
					height = winMaxHeight;
				}

				childRequestedHeight = messageData[0];
				setHeight(height);

				break;

			case 'requestChat':

				var data = {
					name: messageData[0].replace(/__DP_COL__/g, ':'),
					email: messageData[1].replace(/__DP_COL__/g, ':'),
					department_id: messageData[2].replace(/__DP_COL__/g, ':')
				};
				var preform = $('#dpchat_preform');
				preform.find('input[name="name"]').val(data.name);
				preform.find('input[name="email"]').val(data.email);
				preform.find('select[name="department_id"]').val(data.department_id);

				if (window.DpChatWidget) {
					DpChatWidget.open([
						['name', messageData[0].replace(/__DP_COL__/g, ':')],
						['email', messageData[1].replace(/__DP_COL__/g, ':')],
						['department_id', messageData[2].replace(/__DP_COL__/g, ':')],
						['auto_start', 1]
					]);
					self.close();
				}

				return;

			case 'showContentPage':

				if (contentWrap) {
					contentWrap.remove();
				}

				var w = overlayWrapInner.width();
				var h = overlayWrapInner.height();

				var myWidth  = w - 350 + 20; // 350 is width of the left pane inside
				var myHeight = h + 50; // 20 for some space around

				var top = (winHeight - myHeight) / 2;
				var left = ((winWidth - myWidth) / 2) + 175 + 20;

				var css = [];
				css.push('position: fixed');
				css.push('border: 1px solid #7F8394');
				css.push('background: #ffffff url(' + options.staticUrl + 'images/spinners/loading-big-circle.gif) no-repeat 50% 50%');
				css.push('-moz-background-clip: padding');
				css.push('-webkit-background-clip: padding-box');
				css.push('background-clip: padding-box');
				css.push('width: ' + myWidth + 'px');
				css.push('height: ' + myHeight + 'px');
				css.push('margin: 0');
				css.push('padding: 0');
				css.push('box-shadow: none');
				css.push('top: ' + top + 'px');
				css.push('left: ' + left + 'px');
				css.push('-webkit-border-radius: 8px');
				css.push('-moz-border-radius: 8px');
				css.push('border-radius: 8px');
				css.push('z-index: 16001');
				css.push('box-shadow: 0 0px 3px rgba(0, 0, 0, 0.5)');
				css = css.join(';');
				contentWrap = $('<div style="' + css  +'"></div>').appendTo('body');

				var css = [];
				css.push('position: absolute');
				css.push('top: 0');
				css.push('right: 0');
				css.push('bottom: 0');
				css.push('left: 0');
				css.push('-webkit-border-radius: 9px');
				css.push('-moz-border-radius: 9px');
				css.push('border-radius: 9px');
				css.push('overflow: hidden');
				css.push('-moz-background-clip: padding');
				css.push('-webkit-background-clip: padding-box');
				css.push('background-clip: padding-box');
				css = css.join(';');
				var inner = $('<div style="' + css  +'"></div>').appendTo(contentWrap);

				css = [];
				css.push('border: none');
				css.push('width: 28px');
				css.push('height: 28px');
				css.push('margin: 0');
				css.push('padding: 0');
				css.push('cursor: pointer');
				css.push('box-shadow: none');
				css.push('overflow: hidden');
				css.push('position: absolute');
				css.push('background: url(' + options.staticUrl + 'images/user/widget/btn-close.png)');
				css.push('top: -10px');
				css.push('right: -10px');
				css = css.join(';');

				close = $('<span style="'+css+'"></span>').appendTo(contentWrap).click(function(ev) {
					ev.preventDefault();
					contentWrap.fadeOut('fast', function() {
						contentWrap.remove();
						contentWrap = null;
					});
				});

				css = [];
				css.push('width: ' + myWidth + 'px');
				css.push('height: ' + myHeight + 'px');
				css.push('margin: 0');
				css.push('padding: 0');
				css.push('box-shadow: none');
				css.push('overflow: hidden');
				css = css.join(';');

				var url = messageData[0];
				url = url.replace(/__DP_COL__/g, ':');
				url += '#' + encodeURIComponent(document.location.href);
				$('<iframe src="' + url + '" style="' + css  +'" align="middle" frameborder="0" marginheight="0" marginwidth="0" scrolling="no"></iframe>').appendTo(inner);

				break;
		}
	};


	/**
	 * Opens the overlaying iframe
	 */
	this.open = function() {
		if (isOpen) {
			return;
		}

		var self = this;

		// Always re-create the iframe so the stage resets
		if (overlayIframe) {
			overlayIframe.remove();
		}

		if (!overlayWrap) {
			isNew = true;

			var css = [];
			css.push('position: fixed');
			css.push('display: none');
			css.push('box-shadow: none');
			css.push('overflow: hidden');
			css.push('background: #000000');
			css.push('top: 0');
			css.push('right: 0');
			css.push('bottom: 0');
			css.push('left: 0');
			css.push('filter: alpha(opacity=0.7)');
			css.push('-khtml-opacity: 0.7');
			css.push('-moz-opacity: 0.7');
			css.push('opacity: 0.7');
			css.push('z-index: 15000');
			css = css.join(';');
			overlayBack = $('<div id="dp_overlay_back" style="' + css  +'"></div>').appendTo('body');

			css = [];
			css.push('position: fixed');
			css.push('display: none');
			css.push('overflow: hidden');
			css.push('top: 0');
			css.push('right: 0');
			css.push('bottom: 0');
			css.push('left: 0');
			css.push('text-align: center');
			css.push('z-index: 15001');
			css = css.join(';');
			overlayWrap = $('<div id="dp_overlay_wrap" style="' + css  +'"></div>').appendTo('body');

			css = [];
			css.push('position: relative');
			css.push('text-align: left');
			css.push('border: 1px solid #9C9B9B');
			css.push('background: #F9FAFC url(' + options.staticUrl + 'images/spinners/loading-big-circle.gif) no-repeat 50% 50%');
			css.push('width: 960px');
			css.push('height: 500px');
			css.push('margin: auto');
			css.push('padding: 0');
			css.push('box-shadow: none');
			css.push('-webkit-border-radius: 4px');
			css.push('-moz-border-radius: 4px');
			css.push('border-radius: 4px');
			css.push('box-shadow:0 0 9px #e7e7e7');
			css.push('-webkit-box-shadow: 0 0 9px #e7e7e7');
			css.push('-moz-box-shadow: 0 0 9px #e7e7e7');
			css = css.join(';');
			overlayWrapInner = $('<div style="' + css  +'"></div>').appendTo(overlayWrap);

			css = [];
			css.push('border: none');
			css.push('width: 28px');
			css.push('height: 28px');
			css.push('margin: 0');
			css.push('padding: 0');
			css.push('cursor: pointer');
			css.push('box-shadow: none');
			css.push('overflow: hidden');
			css.push('position: absolute');
			css.push('background: url(' + options.staticUrl + 'images/user/widget/btn-close.png)');
			css.push('top: -10px');
			css.push('right: -10px');
			css = css.join(';');

			var close = $('<span style="'+css+'"></span>').appendTo(overlayWrapInner);
			close.click(function(ev) {
				ev.preventDefault();
				self.close();
			});
		}

		isOpen = true;

		css = [];
		css.push('background: transparent');
		css.push('width: 964px');
		css.push('height: 500px');
		css.push('margin: 0');
		css.push('padding: 0');
		css.push('box-shadow: none');
		css.push('overflow: hidden');
		css = css.join(';');

		var src = options.deskproUrl + 'widget/overlay.html?h=' + winHeight;
		src += '#' + encodeURIComponent(document.location.href);
		overlayIframe = $('<iframe id="dp_overlay_iframe" name="dp_overlay_iframe" allowtransparency="true" src="' + src + '" style="' + css  +'" align="middle" frameborder="0" marginheight="0" marginwidth="0" scrolling="no"></iframe>').appendTo(overlayWrapInner);

		setHeight(350);

		comms.setupReciever(function(messageData) {
			self.childListen(messageData);
		}, src);

		overlayBack.fadeIn('fast');
		overlayWrap.fadeIn();
	};


	/**
	 * Closes the overlay
	 */
	this.close = function() {
		if (!isOpen) {
			return;
		}

		isOpen = false;
		overlayBack.fadeOut('fast');
		overlayWrap.fadeOut('fast');
	};


	//##################################################################################################################
	//# Private helper methods
	//##################################################################################################################

	function setHeight(height) {
		DpConsole.log('DpOverlayWidget:setHeight ' + height);

		overlayWrapInner.height(height);
		overlayWrapInner.css('top', (winHeight - height) / 2);
		overlayIframe.height(height);
	};

	function updatePosition() {
		overlayWrapInner.css('top', (winHeight - overlayWrapInner.height()) / 2);

		var winMaxHeight = winHeight - 40;
		var height = overlayWrapInner.height();
		if (height > winMaxHeight) {
			setHeight(winMaxHeight);
		} else if (height < childRequestedHeight) {
			if (childRequestedHeight > winMaxHeight) {
				setHeight(winMaxHeight);
			} else {
				setHeight(childRequestedHeight);
			}
		}
	};


	//##################################################################################################################
	//# Initialize Helpers
	//##################################################################################################################

	function initJquery() {
		function jquery_loaded() {
			DpConsole.log('DpDpOverlayWidget.init: jquery loaded');
			$ = window.jQuery.noConflict(true);
			initWidget();
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

	/**
	 * initWidget() is called when we know we've got jQuery
	 */
	function initWidget() {

		DpConsole.log('DpOverlayWidget.initScript');

		if (window.DpOverlayWidget_Options) {
			options = $.extend({}, options, window.DpOverlayWidget_Options);
		}

		var css = [];
		css.push('position: fixed');
		css.push('display: block');
		css.push('cursor: pointer');
		css.push('box-shadow: none');
		css.push('width: 29px');
		css.push('height: 154px');
		css.push('background-position: 0 0');
		css.push('background-repeat: no-repeat');
		css.push('overflow: hidden');
		css.push('top: 200px');

		var url = options.staticUrl + 'images/user/widget-btn-';

		if (options.tabLocation == 'left') {
			url += 'left.png';
			css.push('left: 0');
		} else {
			url += 'right.png';
			css.push('right: 0');
		}

		css.push('background-image: url(' + url + ')');
		css = css.join(';');
		$('<div id="dp_overlay_btn" class="dp-overlay-widget-trigger" style="' + css + '" class="dp-hide-print ' + options.tabClass + '"></div>').appendTo('body');

		$('.dp-overlay-widget-trigger').on('click', function(ev) {
			ev.preventDefault();
			self.open();
		});

		// Preload images used in the overlay
		(new Image()).src = options.staticUrl + 'images/spinners/loading-big-circle.gif';
		(new Image()).src = options.staticUrl + 'images/user/widgetlogo.png';
		(new Image()).src = options.staticUrl + 'images/user/widgetlogo-on.png';

		winWidth  = lastWinWidth  = $(window).width();
		winHeight = lastWinHeight = $(window).height();

		var repositionTimeout = null;
		$(window).on('resize', function() {

			lastWinWidth  = winWidth;
			lastWinHeight = winHeight;

			winWidth  = $(window).width();
			winHeight = $(window).height();

			if (!isOpen) {
				return;
			}

			if (lastWinWidth != winWidth || lastWinHeight != winHeight) {
				if (repositionTimeout) {
					window.clearTimeout(repositionTimeout);
				}

				repositionTimeout = window.setTimeout(function() {
					repositionTimeout = null;
					updatePosition();
				}, 80);
			}
		});
	};

	//##################################################################################################################
	//# Initialize
	//##################################################################################################################

	DpConsole.log('DpDpOverlayWidget.init');

	if (window.jQuery === undefined || window.jQuery.fn.jquery.indexOf('1.7.') === -1) {
		DpConsole.log('DpOverlayWidgetChat.init: loading jquery');
		initJquery();
	} else {
		DpConsole.log('DpOverlayWidget.init: already have jquery');
		$ = jQuery;
		initWidget();
	}

	return this;
})();
