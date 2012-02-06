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

var DpOverlayWidget = (function() {

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
	 * The overlay iframe
	 * @var {jQuery}
	 */
	var overlayIframe = null;

	/**
	 * Is the overlay currently open?
	 * @var {Boolean}
	 */
	var isOpen = false;

	/**
	 * This is a pre-init that is called automatically when the client has downloaded
	 * this source file. It ensures jQuery first, and then runs initScript that actually places things
	 */
	this.init = function(autoOpen) {
		doAutoOpen = autoOpen;

		DpConsole.log('DpDpOverlayWidget.init');

		if (window.jQuery === undefined || window.jQuery.fn.jquery.indexOf('1.7.') === -1) {

			DpConsole.log('DpOverlayWidgetChat.init: loading jquery');

			var initJquery = function() {
				DpConsole.log('DpDpOverlayWidget.init: jquery loaded');
				$ = window.jQuery.noConflict(true);
				initScript();
			};

			var script_tag = document.createElement('script');
			script_tag.setAttribute("type", "text/javascript");
			script_tag.setAttribute("src", ('https:' == document.location.protocol ? 'https' : 'http') + "://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js");
			script_tag.setAttribute("async", 'true');
			script_tag.onload = function() {
				initJquery();
			};
			script_tag.onreadystatechange = function () { // Same thing but for IE
				if (this.readyState == 'complete' || this.readyState == 'loaded') {
					initJquery();
				}
			};

			(document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
		} else {

			DpConsole.log('DpOverlayWidget.init: already have jquery');

			$ = window.jQuery;
			initScript();
		}
	};

	/**
	 * initScript() is called from init() and loads the elements onto the page and attaches event handlers
	 */
	var initScript = function() {

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
		$('<div id="dp_overlay_btn" class="dp-overlay-widget-trigger" style="' + css + '" class="' + options.tabClass + '"></div>').appendTo('body');

		$('.dp-overlay-widget-trigger').on('click', function(ev) {
			ev.preventDefault();
			self.open();
		});
	};

	/**
	 * Opens the overlaying iframe
	 */
	this.open = function() {
		if (isOpen) {
			return;
		}

		if (!overlayWrap) {
			var css = [];
			css.push('position: fixed');
			css.push('display: none');
			css.push('box-shadow: none');
			css.push('overflow: hidden');
			css.push('background: #000');
			css.push('top: 0');
			css.push('right: 0');
			css.push('bottom: 0');
			css.push('left: 0');
			css.push('filter: alpha(opacity=0.6)');
			css.push('-khtml-opacity: 0.6');
			css.push('-moz-opacity: 0.6');
			css.push('opacity: 0.6');
			css.push('z-index: 15000');
			css = css.join(';');
			overlayBack = $('<div id="dp_overlay_back" style="' + css  +'"></div>').appendTo('body');

			css = [];
			css.push('position: fixed');
			css.push('display: none');
			css.push('overflow: hidden');
			css.push('padding-top: 150px');
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
			css.push('border: 2px solid #000000');
			css.push('background-color: #ffffff');
			css.push('width: 880px');
			css.push('height: 500px');
			css.push('margin: auto');
			css.push('padding: 0');
			css.push('box-shadow: none');
			css = css.join(';');
			var overlayWrapInner = $('<div style="' + css  +'"></div>').appendTo(overlayWrap);

			css = [];
			css.push('width: 880px');
			css.push('height: 500px');
			css.push('margin: 0');
			css.push('padding: 0');
			css.push('box-shadow: none');
			css.push('overflow: hidden');
			css = css.join(';');

			var src = options.deskproUrl + 'widget/overlay.html';
			overlayIframe = $('<iframe id="dp_overlay_iframe" name="dp_overlay_iframe" src="' + src + '" style="' + css  +'" align="middle" frameborder="0" height="500" width"880" marginheight="0" marginwidth="0" scrolling="no"></div>').appendTo(overlayWrapInner);

			css = [];
			css.push('border: none');
			css.push('width: 18px');
			css.push('height: 18px');
			css.push('margin: 0');
			css.push('padding: 0');
			css.push('cursor: pointer');
			css.push('box-shadow: none');
			css.push('overflow: hidden');
			css.push('position: absolute');
			css.push('background: url(' + options.staticUrl + 'images/user/close-btn.png)');
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

	return this;
})();
DpOverlayWidget.init();
