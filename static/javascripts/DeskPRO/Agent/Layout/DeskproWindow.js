Orb.createNamespace('DeskPRO.Agent.Layout');

DeskPRO.Agent.Layout.DeskproWindow = Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function() {

		var self = this;

		// This is the width of nav pane + overview pane,
		// aka where the listpane starts
		this.LEFT_START = 215;

		// Where the center section (where all cols are embedded) starts
		this.CENTER_START = 55;

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

		var totalWidth = newWidth - this.LEFT_START - this.CENTER_START;
		var listWidth = totalWidth * 0.40;
		if (listWidth < 370) {
			listWidth = 370;
		} else if (listWidth > 700) {
			listWidth = 700;
		}

		$('#dp_list').width(listWidth);
		$('#dp_omnibox_wrap').width(listWidth-1); // -1 for border
		$('#dp_content').css('left', this.LEFT_START + listWidth + 1); //+1 for border

		this.fireEvent('resized', [this]);
	}
});
