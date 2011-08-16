Orb.createNamespace('DeskPRO.Agent.Widget');

/**
 * This is just a loader page. The newticket template is pre-loaded (and reloaded occasionally),
 * and the main functionality is in the NewTicket Page. This just handles opening the page in a
 * popout, and the template loading.
 */
DeskPRO.Agent.Widget.NewTicket = new Orb.Class({
	
	Implements: [Orb.Util.Options, Orb.Util.Events],
	
	initialize: function(options) {
		this.options = {
			/**
			 * The URL that'll load the newticket page
			 */
			loadUrl: null,
			
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
			
			tabRoute: null,
			
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
	open: function() {
		
		if (this.pop) {
			this.pop.show();
			return;
		}

		var self = this;
		var pop = new DeskPRO.Agent.PageHelper.Popover({
			tabRoute: this.options.tabRoute,
			onPageInit: function(pop, page) {
				page.addEvent('closeSelf', function(ev) {
					ev.cancel = true;
					self.destroyPop();
				});
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
		
		pop.open();
		this.pop = pop;
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
	
	destroyPop: function() {
		if (!this.pop) return;
		this.pop.destroy();
		this.pop = null;
	}
});