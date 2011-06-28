Orb.createNamespace('DeskPRO');

DeskPRO.FaviconBadge = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.supported = false;
		if (typeof HTMLCanvasElement != undefined) {
			this.supported = true;
		}

		this.faviconEl = $(options.favicon);
		this.badgeEl = null;
	},

	updateBadge: function(num, do_animate) {
		if (!this.supported) return;
		
		var img = document.createElement('img');
		var self = this;

		// We have only two digits to play with
		if (num > 99) {
			num = 99;
		}

		// Same number, dont need to redraw
		if (self.badgeEl && self.badgeEl.data('num') == num) {
			return;
		}

		if (self.currentCancel) self.currentCancel();

		// With zero we put the old one back
		if (num == 0) {
			do_animate = false;
			self.badgeEl.remove();
		}

		img.src = this.faviconEl.attr('href');
		img.onload = function() {
			function badge1() {
				if (self.badgeEl) {
					self.badgeEl.remove();
				}
				var canvas = self.drawCanvus(img, num);
				self.badgeEl = self.faviconEl.clone();
				self.badgeEl.data('num', num);
				self.badgeEl.get(0).href = canvas.toDataURL('image/png');
				$('body').append(self.badgeEl);
			}
			function badge2() {
				if (self.badgeEl) {
					self.badgeEl.remove();
				}
				var canvas = self.drawCanvus(img, num, true);
				self.badgeEl = self.faviconEl.clone();
				self.badgeEl.data('num', num).addClass('alt');
				self.badgeEl.get(0).href = canvas.toDataURL('image/png');
				$('body').append(self.badgeEl);
			};

			function alternate() {

				if (runs++ > 10) {
					cancelAnimation();
					return;
				}

				if (!self.badgeEl) {
					badge1();
				} else {
					if (self.badgeEl.is('.alt')) {
						badge1();
					} else {
						badge2();
					}
				}

				timeout = window.setTimeout(function() { alternate() }, 1000);
			};

			function cancelAnimation() {
				if (timeout) window.clearTimeout(timeout);
				badge1();

				$(window).unbind('focus', cancelAnimation);
				$(window).unbind('mousemove', cancelAnimation);

				self.currentCancel = null;
			};

			self.currentCancel = cancelAnimation;

			if (do_animate) {

				var timeout = null;
				var runs = 0;
				alternate();

				$(window).bind('focus', cancelAnimation);
				$(window).bind('mousemove', cancelAnimation);
			} else {
				badge1();
			}
		};
	},

	drawCanvus: function(img, num, alt) {
		var canvas = document.createElement('canvas');
		canvas.height = canvas.width = 16;

		var canvasContext = canvas.getContext('2d');

		canvasContext.drawImage(img, 0, 0);
		canvasContext.font = '11px "helvetica", sans-serif';

		if (alt) {
			canvasContext.fillStyle = 'rgba(255, 255, 255, 1)';
		} else {
			canvasContext.fillStyle = 'rgba(255, 255, 255, 0.75)';
		}
		canvasContext.fillRect(3, 6, 12, 10);

		canvasContext.fillStyle = '#000';
		canvasContext.fillText(num, 4, 16);

		return canvas;
	}
});