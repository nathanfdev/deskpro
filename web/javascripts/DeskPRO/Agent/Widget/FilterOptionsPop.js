Orb.createNamespace('DeskPRO.Agent.Widget');

/**
 * Positions a popover that scrolls with filter items
 */
DeskPRO.Agent.Widget.FilterOptionsPop = new Orb.Class({

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
			controlEl: '#ticket_customfilter_group_editor',

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
		var self = this;
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

		this.controlEl.on('click', this.close.bind(this));

		this.controlRealEl = $('.filter-group-editor', this.controlEl);

		this.controlRealEl.on('click', '.edit', function(ev) {
			ev.stopPropagation();
			var field = $(this).closest('.filter-row');
			var id = field.data('filter-id');

			self.close();
			DeskPRO_Window.runPageRoute('poppage:' + BASE_URL + 'agent/settings/ticket-filters/'+id+'/edit');
		});

		// Dont bubble clicks in the el to the underlaying control el,
		// that would close the overlay
		this.controlRealEl.on('click', function(ev) {
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

			row.addClass('filter-' + id);
			row.addClass('filter-row');
			row.data('filter-id', id);

			this.fireEvent('initRow', [row, id, this]);

			DeskPRO_Window.util.dpCheckbox($('input.dp-checkbox', row));
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

		this.fireEvent('preOpen', [this]);

		this.controlEl.addClass('open');

		var containPos    = this.containerElement.offset();
		var containHeight = this.containerElement.outerHeight();
		var containWidth  = this.containerElement.outerWidth();

		// The container elemenet is the fixed element, we can mimic its size
		this.controlEl.css({
			top: containPos.top,
			bottom: 0,
			left: containPos.left + containWidth + 1, //+1 border
			right: 0
		});

		this.updatePositions();

		this.controlEl.addClass('open');
		this.controlEl.fadeIn();

		this.backdrop.css({
			left: containPos.left + containWidth // so the sidebar remains functional/scrollable
		});

		this.backdrop2.css({
			left: 0,
			width: containPos.left
		});

		this.backdrop.show();
		this.backdrop2.show();

		this._startScrollWatch();

		this.fireEvent('open', [this]);
	},


	/**
	 * Update the positions of the rows in the control element. This is needed
	 * if someonthing in the main list changes, like new subgrouping.
	 */
	updatePositions: function() {

		var listEl = this.listElement;

		var listHeight = listEl.outerHeight();
		this.lastListElHeight = listHeight;

		this.controlRealEl.css({
			height: listHeight,
			top: 0,
			left: 0
		});

		// Update where the position of the container is relative to the outer wrapper
		// hard-coded value: offset of list from top of pane. aka height of header that says "INBOX"
		var top = this.listElement.offset().top - 86;

		this.controlRealEl.css({
			'margin-top': top-1 /* so the sync below doesnt need to worry about where it is */
		});

		$(this.options.elements, listEl).each((function(i, el) {
			el = $(el);
			var id = el.data('filter-id');

			// Get its position within the wrapper, we'll copy it over
			var pos = el.position();
			var editEl = $('.filter-' + id, this.controlEl);

			editEl.css({
				top: pos.top-top
			});
		}).bind(this));
	},


	/**
	 * Update the scroll position in the element so it matches that of the main list
	 */
	syncScroll: function() {

		var h = this.listElement.outerHeight();
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

		this.fireEvent('preClose', [this]);

		this._stopScrollWatch();

		this.controlEl.removeClass('open');
		this.controlEl.fadeOut();
		this.backdrop.hide();
		this.backdrop2.hide();

		this.fireEvent('close', [this]);
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
			this.backdrop.remove();
			this.backdrop2.remove();
		}
	}
});
