Orb.createNamespace('DeskPRO.Agent.SourcePane');

/**
 * A SearchForm represents an entire source pane search form:
 * - Many inputs
 * - Many panels
 *
 * @type {Orb.Class}
 */
DeskPRO.Agent.SourcePane.SearchForm = new Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function(el) {
		var self = this;
		this.el = el;
		this.hasInit = false;

		this.formPanels = [];

		this.el.addClass('dp-with-activate-listener');
		this.el.on('dp_activated', function() {
			self.initPanel();
		});
	},

	initPanel: function() {
		if (this.hasInit) return;
		this.hasInit = true;

		var self = this;

		this.el.find('.trigger-open-panel').each(function() {
			var panelTrigger = $(this);
			var panelEl      = self.el.find('.' + panelTrigger.data('panel-id')).first();
			var panel        = new DeskPRO.Agent.SourcePane.SearchFormPanel(panelEl);

			panelTrigger.on('click', function(ev) {
				Orb.cancelEvent(ev);
				panel.open(this);
			})
		});

		this.el.find('.dp-select-widget-simple').each(function() {
			var widget = new DeskPRO.UI.Select.WidgetSimple($(this));
		});
	},


	/**
	 * Destroys els that were detached
	 */
	destroy: function() {
		Array.each(this.formPanels, function(formPanel) {
			formPanel.destroy();
		});
		this.formPanels = [];
	}
});


/**
 * A "panel" is a form that opens to the side of the source pane.
 *
 * @type {Orb.Class}
 */
DeskPRO.Agent.SourcePane.SearchFormPanel = new Orb.Class({
	Implements: [Orb.Util.Events],

	initialize: function(el) {
		this.el         = el;
		this._isOpen    = false;
		this.hasInit    = false;
		this.shim       = null;
	},

	initPanel: function() {
		if (this.hasInit) return;
		this.hasInit = false;

		var self = this;

		// For absolute positioning over things
		this.el.detach().appendTo('body');

		this.shim = $('<div class="dp-shim"></div>');
		this.shim.appendTo('body');

		this.shim.on('click', function(ev) {
			Orb.cancelEvent(ev);
			self.close();
		});
	},


	/**
	 * @returns {jQuery}
	 */
	getEl: function() {
		return this.el;
	},


	/**
	 * Is the side panel open?
	 *
	 * @returns {Boolean}
	 */
	isOpen: function() {
		return this._isOpen;
	},


	/**
	 * Open the side panel. If nearEl is specified, then we will try to open the side panel "near" this element.
	 *
	 * @param {HTMLElement} nearEl The element to open the panel near
	 */
	open: function(nearEl) {
		if (this._isOpen) return;
		this._isOpen = true;

		// Actual panel events are lazy inited on first open
		this.initPanel();

		this.el.show();
		this.shim.show();

		var left = 269;
		var top  = 200;

		if (nearEl) {
			top = $(nearEl).offset().top;
		}

		var winH = $(window).height();
		var maxH = winH - top - 80;

		if (maxH < 500) {
			if (winH > 500) {
				top -= (500 - maxH);
			} else {
				top = 60;
			}

			maxH = winH - top - 80;
		}

		if (winH / 1.5 > 500) {
			maxH = parseInt(Math.min(maxH, winH / 1.5));
		}

		this.el.css({
			left: left,
			top: top,
			'max-height': maxH
		});
	},


	/**
	 * CLose the panel if its open
	 */
	close: function() {
		if (!this._isOpen) return;
		this._isOpen = false;

		this.el.hide();
		this.shim.hide();
	},


	/**
	 * Destroy the panel by removing any additionally attached elements
	 */
	destroy: function() {
		this.close();

		if (this.hasInit) {
			this.el.detach();
			this.shim.detach();
		}
	}
});