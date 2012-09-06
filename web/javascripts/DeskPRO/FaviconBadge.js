Orb.createNamespace('DeskPRO');

DeskPRO.FaviconBadge = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		var self = this;
		this.options = {};

		this.origWindowTitle = document.title;

		this.options.strokeColor = 'rgb(255,0,0)';
		this.options.color = '#FFFFFF';

		this.options.strokeColorAlt = 'rgb(255,255,255)';
		this.options.colorAlt = '#FFFFFF';

		$(document).bind('windowshow', this.disableCrazyMode.bind(this));
		$(window).bind('mousemove', this.disableCrazyMode.bind(this));
		$(window).bind('keypress', this.disableCrazyMode.bind(this));

		this.setOptions(options);

		this.animateTimeout = null;
		this.animateCount = 0;
		this.crazyMode = false;
		this.crazyTitle = null;
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

	enableCrazyMode: function(title) {

		if ($('html').hasClass('window-active')) {
			return;
		}

		this.crazyTitle = title || null;
		this.crazyMode = true;
		this.updateBadge(this.lastNum, true);
	},

	disableCrazyMode: function() {
		this.crazyMode = false;
		this.crazyTitle = null;
		document.title = this.origWindowTitle;
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
						if (self.crazyTitle) {
							document.title = self.origWindowTitle;
						}
					} else {
						Notificon('◉ ', {
							color: '#FF0000',
							stroke: '#FFFFFF'
						});
						if (self.crazyTitle) {
							document.title = self.crazyTitle;
						}
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
		}
	}
});
