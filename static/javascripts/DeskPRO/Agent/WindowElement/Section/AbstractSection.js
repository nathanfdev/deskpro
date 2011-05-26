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
		this.addEvent('show', this._onShowLoadList);
		this.addEvent('show', this._onFirstShowFire);
		this.addEvent('show', this._onShowSetVisible);
		this.addEvent('firstshow', this.onFirstShow);
		this.addEvent('hide', this.onHide);
		this.addEvent('hide', this._onHideSetVisible);

		this._isVisible = false;

		this.init();
	},

	init: function() {},

	_onShowSetVisible: function() { this._isVisible = true },
	_onHideSetVisible: function() { this._isVisible = false },

	_onShowLoadList: function() {

		var el = $('.auto-load-route', this.sectionEl);
		if (!el.length || !el.data('route')) {
			return;
		}

		DeskPRO_Window.runPageRoute(el.data('route'));
	},

	_onFirstShowFire: function() {
		if (this.has_shown) return;
		this.has_shown = true;

		this.fireEvent('firstshow');
	},

	getSectionElement: function() {
		if (this.sectionEl) {
			return this.sectionEl;
		}

		return null;
	},

	setSectionElement: function(el) {
		if (this.sectionEl) {
			this.sectionEl.remove();
		}

		if (!el) {
			el = $('<section></section>');
			el.attr('id', Orb.getUniqueId('outline_'));
		}

		this.sectionEl = el;
		if (!el.parent().is('#deskpro_outline')) {
			this.sectionEl.detach().appendTo('#deskpro_outline');
		}
	},

	isVisible: function() {
		return this._isVisible;
	},

	onShow: function() { },
	onFirstShow: function() { },
	onHide: function() { }
});