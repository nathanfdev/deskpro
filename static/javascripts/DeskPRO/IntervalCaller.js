Orb.createNamespace('DeskPRO');

DeskPRO.IntervalCaller = new Orb.Class({

	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			touchResets: true,
			touchRequired: true,
			resetTimeForce: 0,
			callback: function() {},
			timeout: null,
			autostart: true
		};

		this.setOptions(options);

		if (this.options.autostart) {
			this.start();
		}

		this.touched = false;
		this.paused = false;
		this.lastTime = new Date();
	},

	start: function() {
		if (this.timer) {
			window.clearTimeout(this.timer);
			this.timer = null;
		}

		this.timer = window.setTimeout(this.exec.bind(this), this.options.timeout);
	},

	stop: function() {
		if (this.timer) {
			window.clearTimeout(this.timer);
			this.timer = null;
		}
	},

	touch: function() {
		this.touched = true;
		if (this.options.touchResets) {
			if (this.options.resetTimeForce) {
				var now = new Date();
				var diff = now.getTime() - this.lastTime.getTime();
				if (diff > this.options.resetTimeForce) {
					return;
				}
			}

			// Restart if its not over max time
			this.start();
		}
	},

	exec: function(force) {

		if (this.lastTime) {
			this.lastTime = new Date();
		}

		if (!force && this.options.touchRequired && !this.touched) {
			this.start();
			return;
		}

		this.touched = false;

		this.options.callback();

		this.start();
	},

	execNow: function() {
		this.exec();
	}
});
