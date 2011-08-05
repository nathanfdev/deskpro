Orb.createNamespace('DeskPRO.Agent.Layout');

DeskPRO.Agent.Layout.DeskproWindow = Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function() {

		var self = this;

		// This is the width of nav pane + overview pane,
		// aka where the listpane starts
		this.LEFT_START = 244;

		// Handle window resizes
		$(window).resize(function() {
			if (self._resizeTimeout) {
				window.clearTimeout(self._resizeTimeout);
			}

			self._resizeTimeout = self.doResize.delay(300, self);
		});
	},

	doResize: function() {

		var newWidth = $(window).width();

		var totalWidth = newWidth - this.LEFT_START;
		var listWidth = totalWidth * 0.35;
		if (listWidth < 255) {
			listWidth = 255;
		}

		$('#deskpro_list').width(listWidth);
		$('#deskpro_content').css('left', this.LEFT_START + listWidth + 1);

		this.fireEvent('resized', [this]);
	}
});