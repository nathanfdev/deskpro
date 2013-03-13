Orb.createNamespace('DeskPRO');

DeskPRO.TextExpander = new Orb.Class({

	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			textarea: null
		};

		this.setOptions(options);

		this.comboString = null;
		this.$txt = $(this.options.textarea);

		var self = this;
		this.$txt.on('keypress', function(ev) {
			// % key
			if (ev.which == 37) {
				if (!self.comboString) {
					self.comboString = '%';
				} else {
					var combo = self.comboString + '%';
					self.comboString = null;
					self.fireEvent('combo', [combo, ev]);
				}

			// Other input keys after 'start'
			// of combo string
			} else if (self.comboString) {
				var char = String.fromCharCode(ev.which);
				if (char.match(/[a-zA-Z0-9:\.\-_]/)) {
					self.comboString += char;
				} else {
					self.comboString = null;
				}
			} else {
				self.comboString = null;
			}
		});
	}
});
