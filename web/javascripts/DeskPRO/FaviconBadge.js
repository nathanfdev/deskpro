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
		this.crazyMode = false;
		this.lastNum = 0;
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

	enableCrazyMode: function() {
		this.crazyMode = true;
		this.updateBadge(this.lastNum, true);
	},

	disableCrazyMode: function() {
		this.crazyMode = false;
		this.updateBadge(this.lastNum, false);
	},

	updateBadge: function(num, do_animate) {
		var self = this;

		this.clearAnimate();

		// We have only two digits to play with
		var num = parseInt(num);
		if (num > 99) {
			num = 99;
		}

		this.lastNum = num;

		// 0 means no number
		if (!num && !this.crazyMode) {
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
				if (self.crazyMode) {
					if (self.animateCount % 2 == 0) {
						Notificon('◎ ', {
							color: '#000000',
							stroke: '#FFFFFF'
						});
					} else {
						Notificon('◉ ', {
							color: '#FF0000',
							stroke: '#FFFFFF'
						});
					}
				} else {
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
				}
			}, 800);

			$(document).bind('windowshow.faviconbadge', this.clearAnimate.bind(this));
			$(window).bind('mousemove.faviconbadge', this.clearAnimate.bind(this));
			$(window).bind('keypress.faviconbadge', this.clearAnimate.bind(this));
		}
	}
});
