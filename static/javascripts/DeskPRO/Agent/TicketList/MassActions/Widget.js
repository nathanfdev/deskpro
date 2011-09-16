Orb.createNamespace('DeskPRO.Agent.TicketList.MassActions');

DeskPRO.Agent.TicketList.MassActions.Widget = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options)  {
		this.page = page;

		this.options = {
			/**
			 * The object that will handle updating the actual
			 * list for the user
			 */
			viewHandler: null,

			/**
			 * The selection bar we can hook into to figure out
			 * when an item is selected or deselected
			 */
			selectionBar: null,

			/**
			 * The URL we'll post IDs to to get updated items
			 */
			fetchPreviewUrl: null,

			/**
			 * The button to trigger openeing the overlay.
			 * Null: defaults to 'wrapper .perform-actions-trigger',
			 * False: dont auto-assign trigger
			 */
			triggerElement: null,

			/**
			 * The HTML element with the actual controls etc we'll use for this
			 * Defaults to 'wrapper .mass-actions-overlay'
			 */
			templateElement: null
		};

		this.setOptions(options);

		this.viewHandler     = this.options.viewHandler;
		this.selectionBar    = this.options.selectionBar || page.selectionbar;
		this.fetchPreviewUrl = this.options.fetchPreviewUrl || page.meta.fetchResultsUrl;

		this.wrapper = this.options.templateElement || $('div.mass-actions-overlay-container', page.wrapper);
		this.backdropEls = null;

		var trigger = null;
		if (this.options.triggerElement === null) {
			trigger = $('.perform-actions-trigger', page.wrapper);
		} else if (this.options.triggerElement) {
			trigger = this.options.triggerElement;
		}

		if (trigger) {
			trigger.click((function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				this.open();
			}).bind(this));
		}
	},

	/**
	 * Get the main wrapper element around the mass actions UI controls.
	 *
	 * @return {jQuery}
	 */
	getElement: function() {
		return this.wrapper;
	},


	/**
	 * Inits the overlay controls lazily on first open
	 */
	_initOverlay: function() {
		if (this._hasInit) return;
		this._hasInit = true;

		this.wrapper.detach().appendTo('body');
		this.wrapper.css('z-index', '1000100');

		this.wrapper.click(function(ev) {
			ev.stopPropagation();
		});

		// Three backdrops to surround each side of the list pane: left, right, top
		var back1 = $('<div class="backdrop mass-actions" />');
		var back2 = $('<div class="backdrop mass-actions" />');
		var back3 = $('<div class="backdrop mass-actions" />');

		this.backdropEls = $([back1.get(0), back2.get(0), back3.get(0)]);
		this.backdropEls.css('z-index', '1000000').hide().appendTo('body');

		this.backdropEls.click((function(ev) {
			ev.stopPropagation();
			this.close();
		}).bind(this));

		//------------------------------
		// Convert radios
		//------------------------------

		var tpl = DeskPRO_Window.util.getPlainTpl($('.radio-tpl', this.wrapper));

		var groupedRadios = {};
		$(':radio', this.wrapper).each(function() {
			var name = $(this).attr('name');
			if (!groupedRadios[name]) {
				groupedRadios[name] = [];
			}

			groupedRadios[name].push(this);
		});

		Object.each(groupedRadios, function(els) {
			var newEls = [];
			els = $(els);

			var clickFn = function() {
				var boundId = $(this).data('bound-id');
				var radio = $('#' + boundId);

				// Toggle off already checked (ie none selected now)
				if (radio.is(':checked')) {
					radio.attr('checked', false);

				// Normal radio behavior
				} else {
					radio.attr('checked', true);
					newEls.removeClass('radio-on');
					$(this).addClass('radio-on');
				}
			};

			els.each(function() {

				var wrapper = $(this).parent();
				var title = $('.radio-title', wrapper).text().trim();

				var newEl = $(tpl);
				$('.radio-title', newEl).text(title);

				if (!$(this).attr('id')) {
					$(this).attr('id', Orb.getUniqueId());
				}

				newEl.data('bound-id', $(this).attr('id'));

				newEl.click(clickFn);

				wrapper.hide();
				newEl.insertAfter(wrapper);

				newEls.push(newEl.get(0));
			});

			newEls = $(newEls);
		});
	},


	/**
	 * Update the positions of the elements
	 */
	updatePositions: function() {

		//------------------------------
		// The wrapper overlaps the content pane section
		//------------------------------

		var pos = $('#dp_content').offset();
		this.wrapper.css({
			top: pos.top + 3,
			left: pos.left + 3,
			right: 3,
			bottom: 3
		});

		//------------------------------
		// The backdrops surround each side of the list pane
		//------------------------------

		var leftEnd = 269; // Where the left ends (aka where listpane starts)
		var topEnd = 41; // Where the top ends (aka header height)
		var contentStart = pos.left;

		this.backdropEls.eq(0).css({
			top: 0,
			width: leftEnd,
			bottom: 0,
			left: 0
		});

		this.backdropEls.eq(1).css({
			top: 0,
			height: topEnd,
			right: contentStart - 62,
			left: leftEnd
		});

		this.backdropEls.eq(2).css({
			top: 0,
			right: 0,
			bottom: 0,
			left: contentStart
		});
	},


	/**
	 * Is the overlay currently open?
	 *
	 * @return {Boolean}
	 */
	isOpen: function() {
		if (!this._hasInit || !this.wrapper.is('.open')) {
			return false;
		}

		return true;
	},


	/**
	 * Open this overlay
	 */
	open: function() {
		this._initOverlay();
		this.updatePositions();

		this.wrapper.addClass('open');
		this.backdropEls.show();

		this.wrapper.addClass('open');
	},


	/**
	 * Close the overlay
	 */
	close: function() {
		if (!this.isOpen()) {
			return false;
		}

		this.wrapper.removeClass('open');
		this.backdropEls.hide();
		this.fireEvent('closed', [this]);
	},


	destroy: function() {
		if (this._hasInit) {
			this.wrapper.remove();
			this.backdropEls.remove();
		}
	}
});
