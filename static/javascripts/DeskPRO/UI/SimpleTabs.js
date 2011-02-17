Orb.createNamespace('DeskPRO.UI');

/**
 * This is a simple tabbing system where the tab triggers and tab contents can be completely
 * separate, and are linked by attribuets in the source. This tab system simply
 * toggles an 'on' CSS class on elements, so it's up to you to style the elements. For example,
 * content elements without 'on' sholud be display:none etc.
 */
DeskPRO.UI.SimpleTabs = new Class({
	Implements: [Options, Events],

	options: {
		triggerElements: '.tab-trigger',
		activeClassname: 'on',
		context: document
	},

	lastActiveTab: null,
	triggerEls: null,

	initialize: function(options) {
		if (options) this.setOptions(options);

		this.triggerEls = this.options.triggerElements;

		if (typeOf(this.triggerEls) == 'string') {
			this.triggerEls = $(this.triggerEls, this.options.context);
		}

		var self = this;
		this.triggerEls.click(function(ev) {
			self._handleTabClick(this, ev);
		});

		// If none are active, then go and activate the first
		if (this.triggerEls.is(this.options.activeClassname)) {
			var firstTab = $(this.options.activeClassname + ':first', this.triggerEls);
		} else {
			var firstTab = this.triggerEls.first();
		}

		this.activateTab(firstTab);
	},

	_handleTabClick: function(el, event) {
		var tab = $(el);
		this.activateTab(tab, event);
	},

	activateTab: function(tabEl, event) {

		var eventData = {
			event: event || null,
			tabEl: tabEl,
			lastTabEl: this.lastActiveTab,
			manager: this,
			cancel: false
		};
		this.fireEvent('beforeTabSwitch', eventData);

		if (eventData.cancel) {
			return;
		}

		delete eventData['cancel'];


		if (this.lastActiveTab) {
			this.lastActiveTab.removeClass(this.options.activeClassname);
			this.getContentElFromTab(this.lastActiveTab).removeClass(this.options.activeClassname).hide();
			this.lastActiveTab = null;
		}

		this.lastActiveTab = tabEl;
		this.lastActiveTab.addClass(this.options.activeClassname);
		this.getContentElFromTab(this.lastActiveTab).addClass(this.options.activeClassname).show();

		this.fireEvent('tabSwitch', eventData);
	},

	getContentElFromTab: function(tabEl) {
		if (!tabEl.data('tab-for')) {
			console.warn('tab has no tab-for: %o', tabEl);
			return $();
		}

		var el = $(tabEl.data('tab-for'), this.options.context);

		if (el.length < 1) {
			console.warn('no tab content exists for tab: %o', tabEl);
		}

		return el;
	}
});