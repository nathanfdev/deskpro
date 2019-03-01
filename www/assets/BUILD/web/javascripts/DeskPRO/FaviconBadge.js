Orb.createNamespace('DeskPRO');

DeskPRO.FaviconBadge = new Orb.Class({
	Implements: [Orb.Util.Options],

	initialize: function(options) {
		this.options = {};
		this.options.strokeColor = '#FF0000';
		this.options.color = '#FF0000';

		this.setOptions(options);
		this.lastNum = 0;

    Tinycon.setOptions({
      color: '#FFFFFF',
      background: '#FF0000',
      fallback: false
		});
	},

	updateBadge: function(num) {
		// We have only two digits to play with
		num = parseInt(num);
		if (num > 99) {
			num = 99;
		}

		if (this.lastNum === num) {
			return;
		}

		this.lastNum = num;

		// 0 means no number
		if (!num) {
			this.setBubble('');
		} else {
			this.setBubble(num);
		}
	},

  setBubble: function(val) {
		val = val+'';

		if (!val || val === '') {
			Tinycon.reset();
		} else {
			Tinycon.setBubble(val);
		}
	}
});
