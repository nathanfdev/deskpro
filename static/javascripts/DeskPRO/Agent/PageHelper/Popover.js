Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.Popover = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(options) {
		this.options = {
			/**
			 * How long after this is initialized to wait before
			 * auto-loading the person page in the bg. 0 disables
			 * and loads on-demand.
			 */
			loadTimeout: 0,

			/**
			 * The page to load
			 */
			pageUrl: '',

			/**
			 * The route to load when clicking "move to tab"
			 */
			tabRoute: false,

			overFrom: '#deskpro_content'
		};

		this.setOptions(options);

		/**
		 * The page source if the page isnt initialized yet.
		 * This deferres processing the page fragment until the user wants to
		 * see the page and the source is loaded in the bg
		 */
		this.pageSource = null;

		/**
		 * The page fragment once its initialized
		 */
		this.page = null;

		/**
		 * True if the user has requested to see the page but it hasnt been loaded
		 * yet. After its loaded, this flag is checked to see if the page should
		 * be immediately displayed.
		 */
		this.isWaiting = false;

		// Various popover element handlers
		this.popover = null;
		this.popoverOuter = null;
		this.popoverTabs = null;

		if (this.options.loadTimeout) {
			window.setTimeout(this.options.loadTimeout, this._loadPage.bind(this));
		}
	},

	_loadPage: function() {

		if (this._isLoading) return;
		this._isLoading = true;

		$.ajax({
			dataType: 'text',
			url: this.options.pageUrl,
			type: 'GET',
			context: this,
			success: function(html) {
				this._isLoading = false;
				
				this.pageSource = html;
				if (this.isWaiting) {
					this.isWaiting = false;
					this._initFragment();
					this.open();
				}
			}
		});
	},

	_initPopover: function() {

		if (this._hasInit) return;
		this._hasInit = true;

		var tpl = $($('#popover_tpl').get(0).innerHTML);

		this.popover = tpl.filter('.popover-inner');
		this.popoverOuter = tpl.filter('.popover-outer');
		this.popoverTabs = tpl.filter('.popover-tabs');

		this.popover.detach().appendTo('body');
		this.popoverOuter.detach().appendTo('body');
		this.popoverTabs.detach().appendTo('body');

		// Prevent bubbling up from clicks on the popover page
		this.popoverOuter.click(function(ev) {
			ev.stopPropagation();
		});

		var pos = $(this.options.overFrom).offset();
		var top = pos.top;
		var width = pos.left - 30;
		
		this.popover.css({
			'position': 'absolute',
			'display': 'none',
			'z-index': 999998,
			'overflow': 'auto',
			top: top,
			left: 10,
			width: width,
			bottom: 30
		});

		this.popoverOuter.css({
			'position': 'absolute',
			'display': 'none',
			'z-index': 999997,
			'width': width+2+6, //2px for thi sborder, 6px for the popover border
			'overflow': 'auto',
			'top': top-1,
			'left': 9,
			'bottom': 29 //popover bottom (30) -1 for the white border
		});

		this.popoverTabs.css({
			'z-index': 999996,
			'display': 'none',
			'top': top - 24,
			'left': width - 210
		});

		$('.close:first', this.popoverTabs).click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			this.isWaiting = false;
			
			this.close();
		}).bind(this));

		if (this.options.tabRoute) {
			$('.move-to-tab:first', this.popoverTabs).click((function(ev) {
				ev.preventDefault();
				ev.stopPropagation();

				this.isWaiting = false;

				DeskPRO_Window.runPageRoute(this.options.tabRoute);
				this.close();
			}).bind(this));
		} else {
			$('.move-to-tab:first', this.popoverTabs).remove();
		}
	},

	_initFragment: function() {
		if (this.page) return;
		if (!this.pageSource) return;

		this.page = DeskPRO_Window.createPageFragment(this.pageSource);
		this.popover.html(this.pageSource);
		this.page.initPage(this.popover);
		this.pageSource = null;
	},

	open: function() {

		this._initPopover();
		this._initFragment();

		if (!this.page && !this.pageSource) {
			this.isWaiting = true;
			this._loadPage();
		}

		// Already open
		if (this.popover.is(':visible')) {
			return;
		}

		this.popover.show();
		this.popoverOuter.show();
		this.popoverTabs.show();
	},

	close: function() {
		this.popover.hide();
		this.popoverOuter.hide();
		this.popoverTabs.hide();
	},

	destroy: function() {
		if (this.popover) {
			this.popover.remove();
			this.popoverOuter.remove();
			this.popoverTabs.remove();
		}
	}
});