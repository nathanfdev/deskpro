Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.BackgroundPopout = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			/**
			 * The URL that'll load the newticket page
			 */
			loadUrl: null,

			/**
			 * The route to load when clicking on move to tab
			 */
			tabRoute: null,

			/**
			 * The initial timeout before we load the newticket tpl
			 * in the background
			 */
			initialTimeout: 12000, // 15 seconds

			/**
			 * Periodically update the template to account for changes
			 * (usergroups/companies? new custom fields?)
			 */
			periodicalTimeout: 600000, // 10 minutes

			/**
			 * Auto-start the timeout timer to load in the bg
			 */
			autostart: true
		};

		this.setOptions(options);

		/**
		 * The latest Page code
		 */
		this.template = null;

		/**
		 * Any active ajax request
		 */
		this.xhr = null; //active ajax

		/**
		 * The current timeout
		 */
		this.timeout = null;

		/**
		 * The currently created popover
		 */
		this.pop = null;

		if (this.autostart) {
			this.startTimeout();
		}
	},


	/**
	 * Start the auto-update timer
	 */
	startTimeout: function() {
		var t;

		if (this.timeout) return;

		if (this.template) {
			t = this.options.periodicalTimeout;
		} else {
			t = this.options.initialTimeout;
		}

		this.timeout = window.setTimeout(this.loadTemplate.bind(this), t);
	},


	/**
	 * Reloads the template
	 */
	loadTemplate: function(callback) {

		if (this.timeout) {
			window.clearTimeout(this.timeout);
			this.timeout = null;
		}

		if (this.xhr) {
			return;
		}

		this.xhr = $.ajax({
			url: this.options.loadUrl,
			type: 'GET',
			dataType: 'html',
			context: this,
			success: function(html) {
				this.template = html;

				if (callback) {
					callback.call(this, html);
				}
			},
			complete: function() {
				this.startTimeout();
				this.xhr = null;
			}
		});
	},


	/**
	 * Call when you know the template is stale.
	 */
	invalidateTemplate: function() {
		this.loadTemplate();
	},


	/**
	 * Get the template
	 *
	 * @return {String}
	 */
	getTemplate: function() {
		return this.template;
	},


	/**
	 * Opens the page in the popout
	 */
	open: function(callback) {

		if (this.pop) {
			this.pop.open();
			if (callback) {
				callback(this.pop.page);
			}
			return;
		}

		var self = this;
		var pop = new DeskPRO.Agent.PageHelper.Popover({
			tabRoute: this.options.tabRoute,
			onPageInit: function(pop, page) {
				page.addEvent('closeSelf', function(ev) {
					ev.cancel = true;
					self.clear();
				});

				if (callback) {
					callback(page);
				}
			}
		});

		var tpl = this.getTemplate();
		if (tpl) {
			pop.setHtml(tpl);
		} else {
			this.loadTemplate(function(html) {
				pop.setHtml(html);
			});
		}

		this.pop = pop;
		pop.open();
	},

	toggle: function() {
		if (!this.pop) {
			this.open();
			return;
		}
		this.pop.toggle();
	},

	close: function() {
		if (!this.pop) return;
		this.pop.close();
	},

	/**
	 * Clears the currently loaded page (it'll be reloaded next time this is opened)
	 * Same as desotryPop except the template is also removed, so it means
	 * a new ajax request to fetch the page is needed.
	 */
	clear: function() {
		if (this.pop) {
			this.pop.destroy();
			this.pop = null;
		}
	},


	destroyPop: function() {
		if (!this.pop) return;
		this.pop.destroy();
		this.pop = null;
	}
});
