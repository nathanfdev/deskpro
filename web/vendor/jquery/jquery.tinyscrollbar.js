/*!
 * Tiny Scrollbar 1.43
 * http://www.baijs.nl/tinyscrollbar/
 *
 * Copyright 2010, Maarten Baijs
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.opensource.org/licenses/gpl-2.0.php
 *
 * Date: 02 / 24 / 2011
 * Depends on library: jQuery
 */
(function($){
	$.fn.tinyscrollbar = function(options){
		var defaults = {
			axis: 'y', // vertical or horizontal scrollbar? ( x || y ).
			wheel: 40,  //how many pixels must the mouswheel scroll at a time.
			scroll: true, //enable or disable the mousewheel scrollbar
			size: 'auto', //set the size of the scrollbar to auto or a fixed number.
			sizethumb: 'auto' //set the size of the thumb to auto or a fixed number.
		};
		var options = $.extend(defaults, options);
		var oWrapper = $(this);

		// Handle inserting wrappers etc automatically if the supplied
		// element is the scroll content
		if (oWrapper.is('.scroll-content') && !oWrapper.parent().is('.scroll-viewport')) {
			var parentWrapper = oWrapper.parent();
			oWrapper.wrap('<div class="scroll-viewport" />');
			$('<div class="scrollbar disable"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>').insertBefore(oWrapper);

			parentWrapper.addClass('with-scrollbar');

			return parentWrapper.tinyscrollbar(options);
		}

		if (oWrapper.is('.scroll-setup')) {
			return oWrapper;
		}

		oWrapper.addClass('scroll-setup with-scrollbar');

		oWrapper.on('goscrolltop', function() {
			oThumb.obj.css(sDirection, 0);
			oContent.obj.css(sDirection, 0);
			iScroll = 0;
			iMouse['start'] = oThumb.obj.offset()[sDirection];
		});
		oWrapper.on('goscrollbottom', function() {

			// No scrolling, there is no bottom
			if (oScrollbar.obj.hasClass('disable')) {
				return;
			}

			iScroll = 100000;
			iScroll = Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll));
			iScroll + 10;

			oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
			oContent.obj.css(sDirection, -iScroll);
		});

		var oViewport = { obj: $('.scroll-viewport', oWrapper).first() };
		var oContent = { obj: $('.scroll-content', oWrapper).first() };
		var oScrollbar = { obj: $('.scrollbar', oWrapper).first() };
		var oTrack = { obj: $('.track', oScrollbar.obj) };
		var oThumb = { obj: $('.thumb', oScrollbar.obj) };
		var sAxis = options.axis == 'x', sDirection = sAxis ? 'left' : 'top', sSize = sAxis ? 'Width' : 'Height';
		var iScroll, iPosition = { start: 0, now: 0 }, iMouse = {};
		var wheelStopTimeout = null;
		var mouseoverTimeout = null;

		if (this.length > 1){
			this.each(function(){$(this).tinyscrollbar(options)});
			return this;
		}
		this.initialize = function(){
			setEvents();
			var self = this;
			window.setTimeout(function() {
				self.tinyscrollbar_update();
				oWrapper.addClass('scroll-draw');
			}, 250);
		};
		this.tinyscrollbar_update = function(sScroll){

			if (!sScroll) {
				sScroll = 'relative';
			}

			if (!oViewport.obj[0]) {
				return;
			}

			oViewport[options.axis] = oViewport.obj[0]['offset'+ sSize];
			oContent[options.axis] = oContent.obj[0]['scroll'+ sSize];
			oContent.ratio = oViewport[options.axis] / oContent[options.axis];
			if (oContent.ratio >= 1) {
				oScrollbar.obj.addClass('disable');
			} else {
				oScrollbar.obj.removeClass('disable');
			}
			oTrack[options.axis] = options.size == 'auto' ? oViewport[options.axis] : options.size;
			oThumb[options.axis] = Math.min(oTrack[options.axis], Math.max(0, ( options.sizethumb == 'auto' ? (oTrack[options.axis] * oContent.ratio) : options.sizethumb )));

			if (oThumb[options.axis] < 30) {
				oThumb[options.axis] = 30;
				options.sizethumb = 30;
			}

			oScrollbar.ratio = options.sizethumb == 'auto' ? (oContent[options.axis] / oTrack[options.axis]) : (oContent[options.axis] - oViewport[options.axis]) / (oTrack[options.axis] - oThumb[options.axis]);

			if (sScroll == 'relative' && oContent.ratio <= 1.0) {
				iScroll = Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll));
			}

			if (!iScroll) {
				iScroll = 0;
			}

			if (oViewport[options.axis] >= oContent[options.axis]) {
				iScroll = 0;
			}

			setSize();
		};
		function setSize(){
			oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
			oContent.obj.css(sDirection, -iScroll);

			iMouse['start'] = oThumb.obj.offset()[sDirection];
			var sCssSize = sSize.toLowerCase();
			oScrollbar.obj.css(sCssSize, oTrack[options.axis]);
			oTrack.obj.css(sCssSize, oTrack[options.axis]);
			oThumb.obj.css(sCssSize, oThumb[options.axis]);
		};
		function setEvents(){
			oThumb.obj.bind('mousedown', start);
			oThumb.obj[0].ontouchstart = function(oEvent){
				oEvent.preventDefault();
				oThumb.obj.unbind('mousedown');
				start(oEvent.touches[0]);
				return false;
			};
			oTrack.obj.bind('mouseup', drag);
			if(options.scroll && this.addEventListener){
				oWrapper[0].addEventListener('DOMMouseScroll', wheel, false);
				oWrapper[0].addEventListener('mousewheel', wheel, false );
			}
			else if(options.scroll){oWrapper[0].onmousewheel = wheel;}
		};
		function start(oEvent){
			iMouse.start = sAxis ? oEvent.pageX : oEvent.pageY;
			var oThumbDir = parseInt(oThumb.obj.css(sDirection));
			iPosition.start = oThumbDir == 'auto' ? 0 : oThumbDir;
			$(document).bind('mousemove', drag);
			$(document).bind('mouseup', end);
			oThumb.obj.bind('mouseup', end);
			return false;
		};
		function wheel(oEvent){
			if(!(oContent.ratio >= 1)){
				var oEvent = oEvent || window.event;
				var iDelta = oEvent.wheelDelta ? oEvent.wheelDelta/120 : -oEvent.detail/3;
				iScroll -= iDelta * options.wheel;
				iScroll = Math.min((oContent[options.axis] - oViewport[options.axis]), Math.max(0, iScroll));
				oThumb.obj.css(sDirection, iScroll / oScrollbar.ratio);
				oContent.obj.css(sDirection, -iScroll);

				oEvent = $.event.fix(oEvent);
				oEvent.preventDefault();

				// For inner scrollable areas. Dont want scroll
				// to bubble to containing scrollable area too
				if (!oScrollbar.obj.is('.disable') /*&& origScroll > 0 && origScroll < oViewport[options.axis]*/) {
					oEvent.stopPropagation();
				}
			};
		};

		function end(oEvent){
			$(document).unbind('mousemove', drag);
			$(document).unbind('mouseup', end);
			oThumb.obj.unbind('mouseup', end);
			return false;
		};
		function drag(oEvent){
			if(!(oContent.ratio >= 1)){
				iPosition.now = Math.min((oTrack[options.axis] - oThumb[options.axis]), Math.max(0, (iPosition.start + ((sAxis ? oEvent.pageX : oEvent.pageY) - iMouse.start))));
				iScroll = iPosition.now * oScrollbar.ratio;
				oContent.obj.css(sDirection, -iScroll);
				oThumb.obj.css(sDirection, iPosition.now);;
			}
			return false;
		};
		return this.initialize();
	};
})(jQuery);
