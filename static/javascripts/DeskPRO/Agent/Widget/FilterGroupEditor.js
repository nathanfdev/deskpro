Orb.createNamespace('DeskPRO.Agent.Widget');

/**
 * A filter group editor positions rows in a popover overlay with rows
 * that match up to filter titles on the left.
 *
 * (This is just a bit specific to the structure of ticket filters
 * in the position aspects)
 */
DeskPRO.Agent.Widget.FilterGroupEditor = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {

			/**
			 * The parent container that the list element scrolls within
			 */
			containerElement: null,

			/**
			 * The main container that contains all editabls.
			 * We use this to generate a true height, and we bind scrollables.
			 *
			 * @param {jQuery}
			 */
			listElement: null,

			/**
			 * The elements we'll apply this goruping on. This is
			 * either a selector (run in the context of listElement),
			 * or actual elements.
			 *
			 * @option {jQuery}
			 */
			elements: '.filter',

			/**
			 * The widget control element that is already rendered on the page
			 * and contains the special 'editor-fields' and 'editor-
			 *
			 * @option {jQuery}
			 */
			controlEl: '#ticket_filter_group_editor',

			/**
			 * Provide a selector or an element to automatically configure a trigger to open the eidtor
			 *
			 * @option {String|jQuery}
			 */
			triggerElement: null,

			/**
			 * How often to sync scrollbars
			 */
			scrollWatchTimeout: 100
		};

		this.setOptions(options);

		if (this.options.triggerElement) {
			this.enableTriggerElement(this.options.triggerElement);
		}
	},

	_initControl: function() {
		if (this._hasInit) return;
		this._hasInit = true;

		this.fireEvent('preInit', [this]);

		this.containerElement = $(this.options.containerElement);
		this.listElement = $(this.options.listElement);

		this.elements = this.options.elements;
		if (typeOf(this.elements) == 'string') {
			this.elements = $(this.elements, this.listElement);
		}

		this.controlEl = $(this.options.controlEl);
		this.controlEl.detach().hide().appendTo('body');

		this.controlEl.click(this.close.bind(this));

		this.controlRealEl = $('.filter-group-editor', this.controlEl);

		// Dont bubble clicks in the el to the underlaying control el,
		// that would close the overlay
		this.controlRealEl.click(function(ev) {
			ev.stopPropagation();
		});

		this.editorRowTpl    = $('.editor-row-tpl', this.controlEl).first().get(0).innerHTML.trim();
		this.editorFieldsTpl = $('.editor-fields-tpl', this.controlEl).first().get(0).innerHTML.trim();

		this.elements.each((function(i, el) {
			el = $(el);
			var id = el.data('filter-id');

			var row = $(this.editorRowTpl);
			var field = $(this.editorFieldsTpl);
			field.addClass('field-option');
			$('.field-wrap', row).append(field);

			var self = this;
			field.change(function() {
				self.fireEvent('groupingChanged', [parseInt(id), field.val(), field, self]);
			});

			row.addClass('filter-' + id);

			row.appendTo(this.controlRealEl);

		}).bind(this));

		this.backdrop = $('<div class="backdrop" />').hide().appendTo('body');
		this.backdrop.click(this.close.bind(this));

		this.fireEvent('init', [this]);
	},


	/**
	 * Configure a trigger element
	 *
	 * @param el
	 */
	enableTriggerElement: function(el) {
		$(el).click((function(ev) {
			ev.stopPropagation();
			ev.preventDefault();

			this.toggle();
		}).bind(this));
	},


	/**
	 * Get the main wrapper element around the contorl.
	 *
	 * @return {jQuery}
	 */
	getElement: function() {
		return this.controlEl;
	},


	/**
	 * Is the control currently open?
	 *
	 * @return {Boolean}
	 */
	isOpen: function() {
		if (!this._hasInit) return false;
		return this.controlEl.is('.open');
	},


	/**
	 * Opent he control element if it isnt already
	 */
	open: function() {
		if (this.isOpen()) return;
		this._initControl();

		this.controlEl.addClass('open');

		var containPos    = this.containerElement.offset();
		var containHeight = this.containerElement.outerHeight();
		var containWidth  = this.containerElement.outerWidth();

		// The container elemenet is the fixed element, we can mimic its size
		this.controlEl.css({
			top: containPos.top,
			bottom: 0,
			left: containPos.left + containWidth,
			right: 0
		});

		this.updatePositions();

		this.controlEl.addClass('open');
		this.controlEl.fadeIn();

		this.backdrop.css({
			left: containPos.left + containWidth // so the sidebar remains functional/scrollable
		});

		this.backdrop.show();

		this._startScrollWatch();
	},


	/**
	 * Update the positions of the rows in the control element. This is needed
	 * if someonthing in the main list changes, like new subgrouping.
	 */
	updatePositions: function() {

		var listHeight = this.listElement.outerHeight();

		this.controlRealEl.css({
			height: listHeight,
			top: 0,
			left: 0
		});

		// Update where the position of the container is relative to the outer wrapper
		var top = 0;
		var el = this.listElement;
		while (el) {
			top += el.position().top;
			if (el.parent().get(0) == this.containerElement.get(0)) {
				el = null;
				break;
			} else {
				el = el.parent();
			}
		}

		this.controlRealEl.css({
			'margin-top': top /* so the sync below doesnt need to worry about where it is */
		});

		this.elements.each((function(i, el) {
			el = $(el);
			var id = el.data('filter-id');

			// Get its position within the wrapper, we'll copy it over
			var pos = el.position();

			var editEl = $('.filter-' + id, this.controlEl);
			editEl.css({
				top: pos.top
			});

		}).bind(this));
	},


	/**
	 * Update the scroll position in the element so it matches that of the main list
	 */
	syncScroll: function() {

		var h = this.listElement.height();
		if (!this.lastListElHeight || h != this.lastListElHeight) {
			this.updatePositions();
		}

		var containerTop = this.containerElement.position().top;
		var listTop = this.listElement.position().top;

		var realTop = listTop + containerTop;

		this.controlRealEl.css('top', realTop + 'px');
	},


	_startScrollWatch: function() {
		if (this.scrollWatchTimer) {
			window.clearTimeout(this.scrollWatchTimer);
		}

		this.scrollWatchTimer = window.setInterval(this.syncScroll.bind(this), this.options.scrollWatchTimeout);
	},

	_stopScrollWatch: function() {
		if (this.scrollWatchTimer) {
			window.clearTimeout(this.scrollWatchTimer);
			this.scrollWatchTimer = null;
		}
	},


	/**
	 * Close the control elelment if its open
	 */
	close: function() {
		if (!this.isOpen()) return;

		this._stopScrollWatch();

		this.controlEl.removeClass('open');
		this.controlEl.fadeOut();
		this.backdrop.hide();
	},


	/**
	 * Toggle the visibility of ht element
	 */
	toggle: function() {
		if (this.isOpen()) {
			this.close();
		} else {
			this.open();
		}
	},


	/**
	 * Destroys elements moved out of the original scope, or new
	 * elements created.
	 */
	destroy: function() {
		if (this._hasInit) {
			this.controlEl.remove();
		}
	}
});
