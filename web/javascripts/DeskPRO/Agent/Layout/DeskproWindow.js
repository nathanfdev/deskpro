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

		window.onresize = function() {
			self.doResize();
		};
	},

	doResize: function() {

		var newWidth = $(window).width();

		var totalWidth = newWidth - this.LEFT_START - this.CENTER_START;
		var listWidth = totalWidth * 0.40;

		if (typeof Modernizr != 'undefined' && Modernizr.ipad) {
			if (listWidth < 370) {
				listWidth = 370;
			}
		} else {
			if (listWidth < 370) {
				listWidth = 370;
			}
		}

		$('#dp_list').width(listWidth);
		$('#dp_omnibox_wrap').width(listWidth-1); // -1 for border
		$('#dp_omnibox').width(listWidth-56); // -1 for border
		$('#dp_content').css('left', this.LEFT_START + listWidth + 1); //+1 for border

		$('.with-scroll-handler').each(function() {
			if ($(this).data('scroll_handler')) {
				$(this).data('scroll_handler').updateSize();
			}
		});

		this.fireEvent('resized', [this]);
	}
});
