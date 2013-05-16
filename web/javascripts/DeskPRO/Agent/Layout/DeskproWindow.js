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
		var leftHide = $('#dp_left_collapsed');
		var rightHide = $('#dp_right_collapsed');
		var paneVis = DeskPRO_Window.paneVis;

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

		if (!paneVis.tabs) {
			rightHide.show();
			$('#dp_content').hide();
			$('#dp_list').css({
				width: 'auto',
				right: 26
			});
		} else {
			rightHide.hide();
			$('#dp_content').show();
			$('#dp_list').css({
				right: 'auto'
			});
		}

		var left = 0;
		if (!paneVis.source) {
			$('#dp_source').hide();
		} else {
			$('#dp_source').show();
			left += 215;
		}

		if (!paneVis.list) {
			$('#dp_list').hide();
		} else {
			$('#dp_list').show();
			if (!paneVis.source) {
				$('#dp_list').css('left', 26);
				left += listWidth;
			} else {
				$('#dp_list').css('left', 215);
				left += listWidth;
			}
		}

		if (!paneVis.list || !paneVis.source) {
			left += 26;
		}

		if (left) {
			$('#dp_content').css('left', left);
		} else {
			$('#dp_content').css('left', 26);
		}

		if (!paneVis.source || !paneVis.list) {
			leftHide.show();
			leftHide.find('li').hide();

			if (!paneVis.source) {
				leftHide.find('.source_pane').show();
				leftHide.css('left', 0);
			} else {
				leftHide.css('left', 215);
			}
			if (!paneVis.list) {
				leftHide.find('.list_pane').show();
			}
		} else {
			leftHide.hide();
		}

		this.fireEvent('resized', [this]);
	}
});
