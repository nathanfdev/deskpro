Orb.createNamespace('DeskPRO.Agent.WindowElement.Section');

/**
 * A section is button in the first column and a corresponding 'section' in
 * the outline (col2) pane. When a button is clicked, the section element,
 * if it exists, is displayed automatically by the Window object.
 *
 * But its really up to this section handler how things are actually loaded.
 * Use the events if you want to unload/reload things when the section changes.
 *
 * If you set a section element, make sure it's retrievable via getSectionElement
 * or it wont be displayed. By default it'll return this.sectionEl.
 *
 * Generally here's how things work:
 * - The section element is loaded or created on init() and set using setSectionElement()
 * - Some kind of data poller may be set up to fetch updates for the section, but using
 * the onShow/onHide events the frequency might be increased/decreased based on if its in view or not
 */
DeskPRO.Agent.WindowElement.Section.AbstractSection = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function() {
		this.addEvent('show', this.onShow);
		this.addEvent('show', this._onFirstShowFire);
		this.addEvent('show', this._onShowSetVisible);
		this.addEvent('show', this._onShowActivateList);
		this.addEvent('firstshow', this.onFirstShow);
		this.addEvent('hide', this.onHide);
		this.addEvent('hide', this._onHideSetVisible);
		this.addEvent('hide', this._onHideDeactivateList);

		this._isVisible = false;

		this.init();
	},

	/**
	 * Override init method
	 */
	init: function() {},

	onShow: function() { },
	onFirstShow: function() { },
	onHide: function() { },

	setHasInitialLoaded: function() {
		this.hasLoaded = true;
		$('#deskpro_outline_loading').removeClass('on');
	},


	/**
	 * Sets the standard button element, and then you can use this.buttonEl thereafter.
	 *
	 * @param {jQuery}
	 */
	setButtonElement: function(el) {
		this.buttonEl = el;
	},


	/**
	 * Get the button element (or use this.buttonEl)
	 *
	 * @return {jQuery}
	 */
	getButtonElement: function() {
		return this.buttonEl;
	},


	/**
	 * Get the section element (this.sectionEl).
	 *
	 * @return {jQuery}
	 */
	getSectionElement: function() {
		if (this.sectionEl) {
			return this.sectionEl;
		}

		return null;
	},


	/**
	 * Get the list element (this.listEl). If it doesn't exist, it'll be created automatically.
	 *
	 * @return {jQuery}
	 */
	getListElement: function() {
		if (!this.listEl) {
			this.setListElement();
		}

		return this.listEl;
	},


	/**
	 * Sets the section element (this.sectionEl), and it's inner content element (this.contentEl).
	 * The content element is where you should actually render content to. Generally a scrollbar
	 * is attached, and the section element is fixed and the content element overflows.
	 *
	 * @param {jQuery} el
	 * @param {jQuery} contentEl
	 */
	setSectionElement: function(el, contentEl) {
		if (this.sectionEl) {
			this.sectionEl.remove();
		}

		if (!el) {
			el = $('<section></section>');
			el.attr('id', Orb.getUniqueId('outline_'));
		}

		this.sectionEl = el;
		if (!el.parent().is('#DP-SourceList')) {
			this.sectionEl.detach().appendTo('#DP-SourceList');
		}

		if (!contentEl) {
			contentEl = $('section.content', el);
			if (!contentEl.length) {
				var html = [];
				html.push('<div class="with-scrollbar ' + this.sectionEl.attr('id') + '">');
				html.push('<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>');
				html.push('<div class="scroll-viewport"><div class="scroll-content">');
				html = html.join('');

				el = $(html);
				this.sectionEl.append(el);
				contentEl = $('div.scroll-content:first', el);
			}
		}
		this.contentEl = contentEl;

		var scrollEl = $('.with-scrollbar:first', this.sectionEl);
		if (scrollEl.length) {
			console.log('re');
			this.scrollerHandler = new DeskPRO.Agent.ScrollerHandler(this, scrollEl, {
				showEvent: 'show',
				hideEvent: 'hide'
			});
		}
	},


	/**
	 * Sets the list element (this.listEl) and the inner list content (this.listContentEl). Same idea
	 * as section element, except its the list column.
	 *
	 * This is a wrapper for 'pages'.
	 *
	 * @param {jQuery} el
	 * @param {jQuery} contentEl
	 */
	setListElement: function(el, contentEl) {
		if (this.listEl) {
			this.listEl.remove();
		}

		if (!el) {
			el = $('<section></section>');
			el.attr('id', Orb.getUniqueId('list_'));
		}

		this.listEl = el;
		if (!el.parent().is('#DP-TicketList')) {
			this.listEl.detach().appendTo('#DP-TicketList');
		}

		if (!contentEl) {
			contentEl = $('<section class="content"></section>');
		}
		this.listEl.append(contentEl);
		this.listContentEl = contentEl;
	},


	/**
	 * Set the Page on the list column
	 *
	 * @param {DeskPRO.Agent.PageFragment.ListPane.Basic} page
	 */
	setListPageFragment: function(page) {

		if (this.listPage) {
			this.listPage.fireEvent('destroy');
			this.listPage = null;
		}

		this.listPage = page;
		var contentEl = $('section.content:first', this.getListElement());
		contentEl.empty();
		contentEl.html(page.html);

		this.getListElement().addClass('on');

		$('#deskpro_list_loading').removeClass('on');

		page.fireEvent('render', [contentEl]);
		page.fireEvent('activate');
	},


	/**
	 * Check if this section is currently enabled
	 *
	 * @return {Boolean}
	 */
	isVisible: function() {
		return this._isVisible;
	},


	/**
	 * Update the badge number on the icon
	 *
	 * @param {Integer} count
	 */
	updateBadge: function(count) {
		var el = $('.nav-counter', this.buttonEl);
		var elCount = $('span', el);

		var count = parseInt(count);
		var countStr = count;
		if (count) {
			if (countStr >= 1000) {
				countStr = '1000+';
			}
			elCount.html(countStr);
			el.show();
		} else {
			count = 0;
			elCount.html('0');
			el.hide();
		}

		this.badgeCount = count;
		DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.badge_updated', {
			section: this,
			sectionId: this.buttonEl.attr('id'),
			count: count
		});
	},

	getBadgeCount: function() {
		return this.badgeCount || 0;
	},

	_onShowSetVisible: function() { this._isVisible = true },
	_onHideSetVisible: function() { this._isVisible = false },

	_onFirstShowFire: function() {
		if (this.has_shown) return;
		this.has_shown = true;

		this.fireEvent('firstshow');

		this._loadAutoLoadRoutes();
	},

	_loadAutoLoadRoutes: function() {
		if (DeskPRO_Window.getDebug('noAutoLoadList')) {
			return;
		}

		var el = $('.auto-load-route', this.sectionEl);
		if (!el.length || !el.data('route')) {
			return;
		}

		DeskPRO_Window.runPageRoute(el.data('route'));
	},

	_onShowActivateList: function() {
		if (this.listPage) {
			this.listPage.fireEvent('activate');
		}

		if (this.hasLoaded) {
			$('#deskpro_outline_loading').hide();
		}
	},

	_onHideDeactivateList: function() {
		if (this.listPage) {
			this.listPage.fireEvent('deactivate');
		}
	}
});
