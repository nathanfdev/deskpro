Orb.createNamespace('DeskPRO.Agent.Widget');

/**
 * A filter group editor positions rows in a popover overlay with rows
 * that match up to filter titles on the left.
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
			 * The list that is also attacehd to this editor
			 *
			 * @param {jQuery}
			 */
			boundListElement: null,

			/**
			 * The elements we'll apply this grouping on. This is
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
			controlElement: null,

			/**
			 * Provide a selector or an element to automatically configure a trigger to open the eidtor
			 *
			 * @option {String|jQuery}
			 */
			triggerElement: null,

			/**
			 * How often to sync scrollbars
			 */
			scrollWatchTimeout: 100,

			/**
			 * Margin for positioning control element.
			 */
			marginTop: 25,

			/**
			 * Whether or not the id is an integer (will parseInt if true).
			 */
			useIntId: true
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
		this.boundListElement = $(this.options.boundListElement);

		this.elements = this.options.elements;
		if (typeOf(this.elements) == 'string') {
			this.elements = $(this.elements, this.listElement);
		}

		this.controlElement = $(this.options.controlElement);
		this.controlElement.detach().hide().appendTo('body');

		this.controlElement.on('click', this.close.bind(this));

		this.controlRealEl = $('.filter-group-editor', this.controlElement);

		// Dont bubble clicks in the el to the underlaying control el,
		// that would close the overlay
		this.controlRealEl.on('click', function(ev) {
			ev.stopPropagation();
		});

		this.editorRowTpl    = $('.editor-row-tpl', this.controlElement).first().get(0).innerHTML.trim();
		this.editorFieldsTpl = $('.editor-fields-tpl', this.controlElement).first().get(0).innerHTML.trim();

		this.elements.each((function(i, el) {
			el = $(el);
			var id = el.data('filter-id');

			var row = $(this.editorRowTpl);
			var field = $(this.editorFieldsTpl);
			field.addClass('field-option');
			$('.field-wrap', row).append(field);

			// See if there are any options that need to be removed
			var ignore = el.data('grouping-ignore');
			if (ignore) {
				ignore = ignore.split(',');
				Array.each(ignore, function(ig) {
					$('[value="' + ig + '"]', field).remove();
				});
			}

			if (el.data('initial-grouping')) {
				field.val(el.data('initial-grouping'));
			}

			var self = this;
			field.on('change', function() {
				self.fireEvent('groupingChanged',
					[this.options.useIntId ? parseInt(id) : id, field.val(), field, self]);
			});

			row.addClass('filter-' + id);

			row.appendTo(this.controlRealEl);

		}).bind(this));

		this.backdrop = $('<div class="backdrop" />').hide().appendTo('body');
		this.backdrop.on('click', this.close.bind(this));

		this.backdrop2 = $('<div class="backdrop" />').hide().appendTo('body');
		this.backdrop2.on('click', this.close.bind(this));

		$('.close', this.controlRealEl).first().on('click', (function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			this.close();
		}).bind(this));

		this.fireEvent('init', [this]);
	},


	/**
	 * Configure a trigger element
	 *
	 * @param el
	 */
	enableTriggerElement: function(el) {
		$(el).on('click', (function(ev) {
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
		return this.controlElement;
	},


	/**
	 * Is the control currently open?
	 *
	 * @return {Boolean}
	 */
	isOpen: function() {
		if (!this._hasInit) return false;
		return this.controlElement.is('.open');
	},


	/**
	 * Opent he control element if it isnt already
	 */
	open: function() {
		if (this.isOpen()) return;
		this._initControl();

		this.controlElement.addClass('open');

		var containPos    = this.containerElement.offset();
		var containHeight = this.containerElement.outerHeight();
		var containWidth  = this.containerElement.outerWidth();

		// The container elemenet is the fixed element, we can mimic its size
		this.controlElement.css({
			top: containPos.top,
			bottom: 0,
			left: containPos.left + containWidth + 1, //+1 border
			right: 0
		});

		this.updatePositions();

		this.controlElement.addClass('open');
		this.controlElement.fadeIn();

		this.backdrop.css({
			left: containPos.left + containWidth // so the sidebar remains functional/scrollable
		});

		this.backdrop2.css({
			left: 0,
			width: containPos.left + containWidth
		});

		this.backdrop.show();
		this.backdrop2.show();

		this._startScrollWatch();
	},


	/**
	 * Update the positions of the rows in the control element. This is needed
	 * if someonthing in the main list changes, like new subgrouping.
	 */
	updatePositions: function() {

		var listEl = this.listElement;
		var boundMode = false;

		if (!listEl.is(':visible')) {
			listEl = this.boundListElement;
			boundMode = true;
		}

		var listHeight = listEl.height();

		this.controlRealEl.css({
			height: listHeight,
			top: 0,
			left: 0
		});

		// Update where the position of the container is relative to the outer wrapper
		// hard-coded value: offset of list from top of pane. aka height of header that says "INBOX"
		var top = this.options.marginTop;

		var evData = {marginTop: top};
		this.fireEvent('setMarginTop', evData);
		top = evData.marginTop;

		this.controlRealEl.css({
			'margin-top': top /* so the sync below doesnt need to worry about where it is */
		});

		$(this.options.elements, listEl).each((function(i, el) {
			el = $(el);
			var id = el.data('filter-id');

			// Get its position within the wrapper, we'll copy it over
			var pos = el.position();

			if (boundMode) {
				var otherFilterId = $('.filter-' + el.data('filter-name').replace('_w_hold', ''), this.listElement).data('filter-id');
				var editEl = $('.filter-' + otherFilterId, this.controlElement);
			} else {
				var editEl = $('.filter-' + id, this.controlElement);
			}

			editEl.css({
				top: pos.top-top-1,
				height: el.height()
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

		var realTop = containerTop;

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

		this.controlElement.removeClass('open');
		this.controlElement.fadeOut();
		this.backdrop.hide();
		this.backdrop2.hide();
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
			this.controlElement.remove();
			this.backdrop.remove();
			this.backdrop2.remove();
		}
	}
});
