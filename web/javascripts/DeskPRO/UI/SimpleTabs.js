Orb.createNamespace('DeskPRO.UI');

/**
 * This is a simple tabbing system where the tab triggers and tab contents can be completely
 * separate, and are linked by attribuets in the source. This tab system simply
 * toggles an 'on' CSS class on elements, so it's up to you to style the elements. For example,
 * content elements without 'on' sholud be display:none etc.
 *
 * There are two elements: tab triggers and tab content. Tab triggers are attached a click event
 * that stitches the 'on' state of all the tab contents. So the tab clicked becoems "on" (and your
 * CSS makes it visible, and the rest invisible).
 *
 * <code>
 *     <li data-tab-for=".some-tab">Some Tab</li>
 *     ...
 *     <div class="some-tab"></div>
 * </code>
 */
DeskPRO.UI.SimpleTabs = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {

		// Initial values
		this.options = {
			/**
			 * Elements that will act as tab triggers. Tabs
			 * must have a data-tab-for attribute with a jQuery selector
			 * to define which tab they activate.
			 *
			 * You can add new triggers dynamically with addTriggerElement() later
			 */
			triggerElements: '.tab-trigger',

			/**
			 * This classname is added to tabs and tab content wrappers when they're activated
			 */
			activeClassname: 'on',

			/**
			 * The context for tab contents when executing data-tab-for
			 */
			context: document,

			autoSelectFirst: true
		};

		this.lastActiveTab = null;
		this.triggerEls = null;

		if (options) this.setOptions(options);

		this.triggerEls = this.options.triggerElements;

		if (typeOf(this.triggerEls) == 'string') {
			this.triggerEls = $(this.triggerEls, this.options.context);
		}

		var self = this;
		this.triggerEls.on('click', function(ev) {
			ev.cancel = false;
			ev.tabEl = $(this);

			self.fireEvent('tabClick', [ev]);

			if (!ev.cancel) {
				self._handleTabClick(this, ev);
			}
		});

		if (this.options.autoSelectFirst) {
			var firstTab = this.triggerEls.filter('.' + this.options.activeClassname + ':first');
			if (!firstTab.length) {
				firstTab = this.triggerEls.first();
			}

			// Check again, there might not be any tabs
			if (firstTab.length) {
				// need to hide all others
				var self = this;
				this.triggerEls.each(function() {
					self.getContentElFromTab($(this)).hide();
				});

				this.activateTab(firstTab);
			}
		}
	},

	addTriggerElement: function(el) {
		var self = this;

		this.triggerEls.add(el);
		el.on('click', function(ev) {
			ev.cancel = false;
			ev.tabEl = $(this);

			self.fireEvent('tabClick', [ev]);

			if (!ev.cancel) {
				self._handleTabClick(this, ev);
			}
		});
	},

	_handleTabClick: function(el, event) {
		var tab = $(el);
		this.activateTab(tab, event);
	},

	activateTab: function(tabEl, event) {

		if (!tabEl) {
			return;
		}

		var eventData = {
			event: event || null,
			tabEl: tabEl,
			lastTabEl: this.lastActiveTab,
			tabContent: this.getContentElFromTab(tabEl),
			manager: this,
			cancel: false
		};

		this.fireEvent('beforeTabSwitch', eventData);

		if (eventData.cancel) {
			return;
		}

		if (this.lastActiveTab && this.lastActiveTab.data('tab-on-hide')) {
			this.lastActiveTab.data('tab-on-hide')(eventData);
		}
		if (this.lastActiveTabContent && this.lastActiveTabContent.data('tab-on-hide')) {
			this.lastActiveTabContent.data('tab-on-hide')(eventData);
		}

		delete eventData['cancel'];

		if (this.lastActiveTab) {
			this.lastActiveTab.removeClass(this.options.activeClassname);
			this.getContentElFromTab(this.lastActiveTab).removeClass(this.options.activeClassname).hide();
			this.lastActiveTab = null;
		}

		this.lastActiveTab = tabEl;
		this.lastActiveTab.addClass(this.options.activeClassname);
		eventData.tabContent.addClass(this.options.activeClassname).show();

		this.lastActiveTabContent = eventData.tabContent;

		var parentContainer = eventData.tabContent.closest('.tabViewDetailContent, .with-page-fragment').first();
		if (parentContainer) {
			if (parentContainer.data('page-fragment')) {
				parentContainer.data('page-fragment').updateUi();
			} else {
				parentContainer.find('.with-scroll-handler').each(function() {
					if ($(this).data('scroll_handler')) {
						$(this).data('scroll_handler').updateSize();
					}
				});
			}
		}

		if (this.lastActiveTab && this.lastActiveTab.data('tab-on-show')) {
			this.lastActiveTab.data('tab-on-show')(eventData);
		}
		if (this.lastActiveTabContent && this.lastActiveTabContent.data('tab-on-show')) {
			this.lastActiveTabContent.data('tab-on-show')(eventData);
		}

		this.fireEvent('tabSwitch', eventData);
	},

	getActiveTab: function() {
		return this.lastActiveTab;
	},

	getActiveTabContent: function() {
		return this.getContentElFromTab(this.getActiveTab());
	},

	getContentElFromTab: function(tabEl) {
		if (!tabEl || !tabEl.data || !tabEl.data('tab-for')) {
			DP.console.error('tab has no tab-for: %o', tabEl);
			if (console && console.trace) console.trace();
			return $();
		}

		var el = $(tabEl.data('tab-for'), this.options.context);

		if (el.length < 1) {
			DP.console.error('no tab content exists for tab: %o', tabEl);
			console.trace();
		}

		return el;
	},

	destroy: function() {

	}
});
