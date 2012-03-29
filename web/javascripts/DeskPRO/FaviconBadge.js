Orb.createNamespace('DeskPRO');

DeskPRO.FaviconBadge = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {};

		this.options.strokeColor = 'rgba(252,219,117,0.85)';
		this.options.color = '#000000';

		this.options.strokeColorAlt = 'rgba(255,255,255,0.85)';
		this.options.colorAlt = '#000000';

		this.setOptions(options);

		this.animateTimeout = null;
		this.animateCount = 0;
	},

	clearAnimate: function() {
		if (this.animateTimeout) {
			window.clearTimeout(this.animateTimeout)
			this.animateTimeout = null;
			this.animateTimeoutCount = 0;
		}

		$(document).unbind('windowshow.faviconbadge');
		$(window).unbind('mousemove.faviconbadge');
		$(window).unbind('keypress.faviconbadge');
	},

	updateBadge: function(num, do_animate) {
		var self = this;

		this.clearAnimate();

		// We have only two digits to play with
		var num = parseInt(num);
		if (num > 99) {
			num = 99;
		}

		// 0 means no number
		if (!num) {
			Notificon('');
			return;
		}

		Notificon(num+'', {
			color: this.options.color,
			stroke: this.options.strokeColor
		});

		if (do_animate) {
			this.animateTimeout = window.setInterval(function() {
				self.animateCount++;
				if (self.animateCount % 2 == 0) {
					Notificon(num+'', {
						color: self.options.color,
						stroke: self.options.strokeColor
					});
				} else {
					Notificon(num+'', {
						color: self.options.colorAlt,
						stroke: self.options.strokeColorAlt
					});
				}
			}, 800);

			$(document).bind('windowshow.faviconbadge', this.clearAnimate.bind(this));
			$(window).bind('mousemove.faviconbadge', this.clearAnimate.bind(this));
			$(window).bind('keypress.faviconbadge', this.clearAnimate.bind(this));
		}
	}
});
