Orb.createNamespace('DeskPRO');

DeskPRO.FaviconBadge = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {};

		this.origWindowTitle = document.title;

		this.options.strokeColor = '#FF0000';
		this.options.color = '#FF0000';

		this.setOptions(options);

		this.animateTimeout = null;
		this.animateCount = 0;
		this.crazyMode = false;
		this.crazyTitle = null;
		this.lastNum = 0;

    Tinycon.setOptions({
      color: '#FFFFFF',
      background: '#FF0000',
      fallback: false
		});
	},

	clearAnimate: function() {
		if (this.animateTimeout) {
			window.clearTimeout(this.animateTimeout)
			this.animateTimeout = null;
			this.animateTimeoutCount = 0;
		}

		$(document).unbind('windowshow.faviconbadge');
		$(document).unbind('mousemove.faviconbadge');
		$(document).unbind('keypress.faviconbadge');
		$(document).unbind('visibilitychange.faviconbadge');
	},

	enableCrazyMode: function(title) {

		if (document.visibilityState && document.visibilityState === 'visible') {
			return;
		}

    $(document).one('windowshow.faviconbadge', this.disableCrazyMode.bind(this));
    $(document).one('mousemove.faviconbadge', this.disableCrazyMode.bind(this));
    $(document).one('keypress.faviconbadge', this.disableCrazyMode.bind(this));
    $(document).one('visibilitychange.faviconbadge', this.disableCrazyMode.bind(this));

		this.crazyTitle = title || null;
		this.crazyMode = true;
		this.updateBadge(this.lastNum, true);
	},

	disableCrazyMode: function() {
		this.clearAnimate();
		this.crazyMode = false;
		this.crazyTitle = null;
		document.title = this.origWindowTitle;
		this.updateBadge(this.lastNum, false);
	},

	updateBadge: function(num) {
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
			this.setBubble('');
			return;
		}

		if (this.crazyMode) {
			this.animateTimeout = window.setInterval(function() {
				self.animateCount++;
        if (self.animateCount % 2 == 0) {
          self.setBubble('');
        } else {
          self.setBubble(num || '·');
          if (self.crazyTitle) {
            document.title = self.crazyTitle;
          }
        }
			}, 800);
		} else {
			self.setBubble(num);
		}
	},

  setBubble: function(val) {
		val = val+'';
		if (this.nextBubbleValue === val) {
			return;
		}
    this.nextBubbleValue = val;

		if (window.requestAnimationFrame && document.visibilityState && document.visibilityState === 'visible') {
			// for perf use animation frame if tab is visible
      window.requestAnimationFrame(this.doSetBubble.bind(this));
		} else {
			// else request it now so we can update when user is not on our page
			this.doSetBubble();
		}
	},

  doSetBubble: function() {
		if (!this.nextBubbleValue || this.nextBubbleValue === '') {
      Tinycon.reset();
		} else {
      Tinycon.setBubble(this.nextBubbleValue);
    }
	}
});
