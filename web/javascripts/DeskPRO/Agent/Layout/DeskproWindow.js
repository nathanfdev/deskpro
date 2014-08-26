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

		// Width of the collapsed source pane placeholder
		this.SOURCE_PLACE_WIDTH = 23;
		this.SOURCE_WIDTH = 214;

		this.listWidthRatio = 0.40;

		this.enableHashUpdate = true;

		$(window).on('resize', function() {
			self.doResize(true);
		});

		var listSizer = $('#dp_list_resizer').draggable({
			axis: 'x'
		}).on('dragstart', function() {
			$('body').addClass('with-tabresizing');
		}).on('dragstop', function() {
			$('body').removeClass('with-tabresizing');
			var pos = parseInt(listSizer.css('left').replace(/px/, ''));
			self.doResize();
		});

		//--------------------------------
		// Source pane toggle
		//--------------------------------

		var openSourceOverlay = function() {
			if (DeskPRO_Window.paneVis.source) return;
			$('#dp_source').css({
				left: -self.SOURCE_WIDTH,
				display: 'block'
			});
			if (DeskPRO_Window.openSection) {
				DeskPRO_Window.openSection.updateUi();
			}
			$('#dp_source').stop().animate({left: 0 }, {
				duration: 350,
				complete: function() {
					if (!isSourceOver && !isSourceNavOver && !isSourcePlaceOver && !sourceOutTimeout) {
						sourceOutTimeout = window.setTimeout(function() {
							sourceOutTimeout = null;
							if (!isSourceOver) {
								closeSourceOverlay();
							}
						}, 380);
					}
				}
			});
		};
		this.openSourceOverlay = openSourceOverlay;

		var closeSourceOverlay = function() {
			if (DeskPRO_Window.paneVis.source) return;
			isSourceClosing = true;
			$('#dp_source').stop().animate({left: -self.SOURCE_WIDTH }, {
				duration: 350,
				complete: function() {
					isSourceClosing = false;
					$('#dp_source').css('display', 'none');
				}
			});
		};

		$('#dp_source_place').on('click', function() {
			openSourceOverlay();
		});

		var isSourceOver = false;
		var sourceOutTimeout = null;
		var isSourceClosing = false;
		var openSourceTimeout = null;
		var isSourcePlaceOver = false;
		var isSourceNavOver = false;
		var cancelCloseSourceOverlayTimeout = function() {
			if (sourceOutTimeout) {
				window.clearTimeout(sourceOutTimeout);
				sourceOutTimeout = null;
			}
			if (isSourceClosing) {
				isSourceClosing = false;
				$('#dp_source').stop().animate({left: 0 }, {
					duration: 150
				});
			}
		};
		var startCloseSourceOverlayTimeout = function() {
			if (!sourceOutTimeout) {
				sourceOutTimeout = window.setTimeout(function() {
					sourceOutTimeout = null;
					if (!isSourceOver && !isSourcePlaceOver && !isSourceNavOver) {
						closeSourceOverlay();
					}
				}, 380);
			}
		};


		$('#dp_source').on('mouseover', function() {
			isSourceOver = true;
			cancelCloseSourceOverlayTimeout();
		});
		$('#dp_source').on('mouseout', function() {
			isSourceOver = false;
			startCloseSourceOverlayTimeout();
		});

		$('#dp_source_place').on('mouseover', function() {
			isSourcePlaceOver = true;
			window.setTimeout(function() {
				openSourceTimeout = null;
				if (isSourcePlaceOver) {
					openSourceOverlay();
				}
			}, 250);
		}).on('mouseout', function() {
			isSourcePlaceOver = false;
			if (openSourceTimeout) {
				window.clearTimeout(openSourceTimeout);
				openSourceTimeout = null;
			}
		});
		$('#dp_nav_sections').on('mouseover', function() {
			isSourceNavOver = true;
			cancelCloseSourceOverlayTimeout();
		}).on('mouseout', function() {
			isSourceNavOver = false;
			startCloseSourceOverlayTimeout();
		});
	},

	doResize: function(widthCalc) {
		var listSizer = $('#dp_list_resizer');
		var paneVis = DeskPRO_Window.paneVis;

		var newWidth = $(window).width();

		var totalWidth = newWidth - this.LEFT_START - this.CENTER_START;

		var listWidth;
		if (widthCalc) {
			listWidth = totalWidth * this.listWidthRatio;
			if (typeof Modernizr != 'undefined' && Modernizr.ipad) {
				if (listWidth < 370) {
					listWidth = 370;
				}
			} else {
				if (listWidth < 370) {
					listWidth = 370;
				}
			}
		} else {
			listWidth = parseInt(listSizer.css('left').replace(/px/, '')) - this.LEFT_START;
			this.listWidthRatio = listWidth / totalWidth;
		}

		$('#dp_list').width(listWidth);
		$('#dp_omnibox_wrap').width(listWidth-1); // -1 for border
		$('#dp_omnibox').width(listWidth-56); // -1 for border
		$('#dp_content').css('left', this.LEFT_START + listWidth + 1); //+1 for border

		$('.with-scroll-handler').each(function() {
			if ($(this).data('scroll_handler') && $(this).is(':visible')) {
				$(this).data('scroll_handler').updateSize();
			}
		});

		if (!paneVis.tabs) {
			$('#dp_content').hide();
			$('#dp_list').css({
				width: 'auto',
				right: 0
			});
		} else {
			$('#dp_content').show();
			$('#dp_list').css({
				right: 'auto'
			});
		}

		var left = 0, sourceLeft = 0, visibleLeft = 0;
		if (!paneVis.source) {
			$('#dp_source').stop().hide().css('left', 0);
			$('#dp_source_btn').find('.collapse-btn').hide();
			$('#dp_source_btn').find('.pin-btn').show();
			$('#dp_source_place').show();
			$('#dp_center').css('left', 55);
			left += this.SOURCE_PLACE_WIDTH;
		} else {
			$('#dp_source').stop().show().css('left', 0);
			$('#dp_source_btn').find('.collapse-btn').show();
			$('#dp_source_btn').find('.pin-btn').hide();
			$('#dp_source_place').hide();
			$('#dp_nav').show();
			$('#dp_center').css('left', 55);
			left += 215;
		}

		if (!paneVis.list) {
			$('#dp_list').hide();
			listSizer.hide();
		} else {
			$('#dp_list').show();
			listSizer.show();
			if (!paneVis.source) {
				$('#dp_list').css('left', this.SOURCE_PLACE_WIDTH);
				left += listWidth;
			} else {
				$('#dp_list').css('left', this.LEFT_START);
				left += listWidth;
			}
		}

		if (left) {
			$('#dp_content').css('left', left);
		} else {
			$('#dp_content').css('left', 0);
		}

		if (paneVis.source) {
			sourceLeft += 270;
		} else {
			sourceLeft += 55 + this.SOURCE_PLACE_WIDTH;
		}
		visibleLeft += 270;
		visibleLeft += listWidth;

		$('#dp_header_listpane_aligned').width(listWidth);
		$('#dp_header_contentpane_aligned').css('left', visibleLeft);

		if (paneVis.tabs) {
			if (paneVis.list) {
				$('#tabNavigationPane').css('left', sourceLeft + listWidth + 1);
			} else {
				$('#tabNavigationPane').css('left', sourceLeft);
			}
		} else {
			$('#tabNavigationPane').css('left', sourceLeft);
		}

		listSizer.css('left', left-2);

		if (this.enableHashUpdate) {
			DeskPRO_Window.updateWindowUrlFragment();
		}

		if (DeskPRO_Window.openSection) {
			DeskPRO_Window.openSection.updateUi();
		}

		this.fireEvent('resized', [this]);
	}
});
