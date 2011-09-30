Orb.createNamespace('DeskPRO.Agent.PageHelper');

DeskPRO.Agent.PageHelper.Popover_Instances = {};

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
			 * The page to load. False to not use the loader in this class,
			 * you can use setHtml() instead.
			 */
			pageUrl: '',

			/**
			 * Callback method for loading the page instead of using default ajax loader.
			 */
			pageCallback: null,

			/**
			 * The route to load when clicking "move to tab"
			 */
			tabRoute: false,

			/**
			 * Destroy the popover when it closes?
			 */
			destroyOnClose: false,

			overFrom: '#dp_content'
		};

		DeskPRO.Agent.PageHelper.Popover_Instances[this.OBJ_ID] = this;

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

		if (this.options.loadTimeout) {
			this.autoloadTimeout = window.setTimeout(this._loadPage.bind(this), this.options.loadTimeout);
		}
	},

	_loadPage: function() {

		if (this.options.pageCallback) {
			return this.options.pageCallback(this.setHtml.bind(this));
		}

		if (!this.options.pageUrl) {
			return;
		}

		if (this._isLoading) return;
		this._isLoading = true;

		if (this.autoloadTimeout) {
			window.clearTimeout(this.autoloadTimeout);
			this.autoloadTimeout = null;
		}

		$.ajax({
			dataType: 'text',
			url: this.options.pageUrl,
			type: 'GET',
			context: this,
			success: function(html) {
				this._isLoading = false;
				this.setHtml(html);
			}
		});
	},

	setHtml: function(html) {
		this.pageSource = html;
		if (this.isWaiting) {
			this.isWaiting = false;
			this._initFragment();
			this.open();
		}
	},

	_initPopover: function() {

		if (this._hasInit) return;
		this._hasInit = true;

		this.popoverOuter = $($('#popover_tpl').get(0).innerHTML);
		this.popover = $('.popover-inner', this.popoverOuter).first();

		this.popoverOuter.detach().appendTo('body');

		// Prevent bubbling up from clicks on the popover page
		this.popoverOuter.click(function(ev) {
			ev.stopPropagation();
		});

		var pos = $(this.options.overFrom).offset();
		var top = pos.top;
		var width = pos.left - 30;

		this.popoverOuter.css({
			'position': 'absolute',
			'display': 'none',
			'z-index': 999997,
			'width': width+2+6, //2px for thi sborder, 6px for the popover border
			'overflow': 'auto',
			'top': top-3,
			'left': 9,
			'bottom': 20 // account for border+shadows
		});

		$('.close', this.popoverOuter).first().click((function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			this.isWaiting = false;

			var ev = {pop: this, cancel: false};
			this.fireEvent('closeTabClick', ev);
			if (ev.cancel) {
				return;
			}

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

		this.fireEvent('pageInit', [this, this.page]);
	},

	isOpen: function() {
		if (this.popover && this.popover.is(':visible')) {
			return true;
		}

		return false;
	},

	open: function() {

		this._initPopover();
		this._initFragment();

		if (!this.page && !this.pageSource) {
			this.isWaiting = true;
			this._loadPage();
		}

		// Already open
		if (this.isOpen()) {
			return;
		}

		// Go through other instances and make sure the others arent open
		Object.each(DeskPRO.Agent.PageHelper.Popover_Instances, function(inst) {
			if (inst.isOpen()) {
				inst.close();
			}
		});

		this.popoverOuter.show();
	},

	toggle: function() {
		if (this.isOpen()) {
			this.close();
		} else {
			this.open();
		}
	},

	close: function() {

		var ev = {pop: this, cancel: false};
		this.fireEvent('close', ev);
		if (ev.cancel) {
			return;
		}

		this.popoverOuter.hide();

		if (this.options.destroyOnClose) {
			this.destroy();
		}
	},

	destroy: function() {

		if (this.page) {
			this.page.fireEvent('destroy');
			this.page = null;
		}

		if (this.popover) {
			this.popoverOuter.remove();
		}

		this.popoverOuter = null;
		this.popover = null;
		this.options = null;

		delete DeskPRO.Agent.PageHelper.Popover_Instances[this.OBJ_ID];
	}
});
