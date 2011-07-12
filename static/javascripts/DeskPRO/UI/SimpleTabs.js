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
			triggerElements: '.tab-trigger',
			activeClassname: 'on',
			context: document
		};

		this.lastActiveTab = null;
		this.triggerEls = null;
		
		if (options) this.setOptions(options);

		this.triggerEls = this.options.triggerElements;

		if (typeOf(this.triggerEls) == 'string') {
			this.triggerEls = $(this.triggerEls, this.options.context);
		}

		var self = this;
		this.triggerEls.click(function(ev) {
			self._handleTabClick(this, ev);
		});

		var firstTab = this.triggerEls.filter('.on:first');
		if (!firstTab.length) {
			firstTab = this.triggerEls.first();
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