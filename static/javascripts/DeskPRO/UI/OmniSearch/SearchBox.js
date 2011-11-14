Orb.createNamespace('DeskPRO.UI.OmniSearch');

/**
 * The searchbox is the main omnisearch controller.
 * It contains references to the main elements and definitions
 * for the contexts/terms, and fires off the appropriate events
 * to initiate user input.
 */
DeskPRO.UI.OmniSearch.SearchBox = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			/**
			 * The wrapper element. Usually this is the thing that mimics the look
			 * of an input box, but its just where the terms etc are appended to.
			 */
			wrapperEl: null,

			/**
			 * Input element, usually within wrapper
			 */
			inputEl: null,

			/**
			 * Context type button
			 */
			contextBtnEl: null
		};

		this.setOptions(this.getDefaultOptions());
		this.setOptions(options);

		var self = this;

		this.wrapperEl    = $(this.options.wrapperEl);
		this.inputEl      = $(this.options.inputEl);
		this.contextBtnEl = $(this.options.contextBtnEl);

		$('.clear-trigger:first', this.wrapperEl).on('click', (function(ev) {
			ev.preventDefault();
			this.clear();
		}).bind(this));

		$('.builder-trigger:first', this.wrapperEl).on('click', (function(ev) {
			ev.preventDefault();
			this.showBuilder();
		}).bind(this));

		this.contexts = {};
		this.activeContextId = null;

		this.wrapperEl.on('click', function(ev) {
			if (ev.target == this) {
				self.inputEl.focus();
			}
		});

		this.inputEl.on('keypress', function(ev) {
			if (ev.which == 58 /* colon : key */ || ev.which == 61 /* equals = */) {
				ev.preventDefault();
				var val = $(this).val().trim();
				$(this).val('');
				$(this).blur();

				self.addSearchTermByTrigger(val);
			} else if (ev.which == 8 /* backspace */) {
				if (!$(this).val().trim().length) {
					var prevTerm = self.inputEl.prev();
					if (prevTerm.length && prevTerm.is('.term')) {
						self.removeSearchTerm(prevTerm);
					}
				}
			}

			$(this).attr('size', $(this).val().length+2);
		});

		this.inputEl.on('focus', function() {
			self.wrapperEl.addClass('focus');
		}).on('blur', function() {
			self.wrapperEl.removeClass('focus');
		});

		$(this.wrapperEl).on('click', function() {
			self.inputEl.focus();
		});

		$('.remove-all-terms-trigger:first', this.wrapperEl).on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();

			$('.term', self.wrapperEl).each(function() {
				self.removeSearchTerm($(this));
			});

			self.inputEl.focus();
		});

		this.addEvent('termInputDone', function() {
			self.inputEl.focus();
		});

		this.init();
	},

	getDefaultOptions: function() {
		return {};
	},

	init: function() {},


	/**
	 * Lazy-init menu element and return it.
	 * Use in addContext to add the context to the menu
	 */
	_getContextMenuEl: function() {
		if (!this.contextMenuEl) {
			this.contextMenuEl = $('<ul style="display:none;" id="omnisearch_context_menu" />');
			this.contextMenuEl.appendTo('body');

			this.contextMenu = new DeskPRO.UI.Menu({
				triggerElement: this.contextBtnEl,
				menuElement: this.contextMenuEl,
				defaultEventContext: this,
				onItemClicked: function(info) {
					var contextId = $(info.itemEl).data('context');
					this.activateContext(contextId);
				}
			});
		}

		return this.contextMenuEl;
	},


	/**
	 * Add a context
	 *
	 * @param {String} id
	 * @param {DeskPRO.UI.OmniSearch.ContextAbstract} context
	 */
	addContext: function(id, context) {
		this.contexts[id] = context;

		var menuLi = $('<li />');
		menuLi.text(context.getLabel());
		menuLi.data('context', id);

		menuLi.appendTo(this._getContextMenuEl());

		if (!this.activeContext) {
			this.activateContext(id);
		}
	},


	/**
	 * Get a context
	 *
	 * @param {String} id The context ID
	 * @return {DeskPRO.UI.OmniSearch.ContextAbstract}
	 */
	getContext: function(id) {
		return this.contexts[id];
	},


	/**
	 * Change the active context
	 *
	 * @param {String} id The context ID
	 */
	activateContext: function(id) {

		// Deactivate old one
		if (this.activeContextId) {
			this.getContext(this.activeContextId).fireEvent('deactivate');
			this.activeContextId = null;
		}

		this.activeContextId = id;
		var context = this.getContext(id);
		context.fireEvent('activate');

		$('.label', this.contextBtnEl).text(context.getLabel());
	},


	/**
	 * Get the active context
	 *
	 * @return {DeskPRO.UI.OmniSearch.ContextAbstract}
	 */
	getActiveContext: function() {
		return this.getContext(this.activeContextId);
	},


	/**
	 * Get the ID of the active context
	 *
	 * @return {String}
	 */
	getActiveContextId: function() {
		return this.activeContextId;
	},


	/**
	 * Add a search term to the search box
	 *
	 * @param {String} triggerWord
	 */
	addSearchTermByTrigger: function(triggerWord) {
		var context = this.getActiveContext();
		var termId = context.getTermIdByTrigger(triggerWord);

		if (!termId) {
			return;
		}

		var term = context.getTerm(termId);

		var el = term.createTermElement(this);
		el.data('term', termId);

		el.insertBefore(this.inputEl);

		if (el.data('handler') && el.data('handler').afterAdd) {
			el.data('handler').afterAdd(el);
		}

		var removeTrigger = $('.remove-term-trigger:first', el);
		if (removeTrigger.length) {
			removeTrigger.on('click', (function() {
				this.removeSearchTerm(el);
			}).bind(this));
		}

		this.wrapperEl.addClass('with-terms');
	},


	/**
	 * Remove a search term
	 *
	 * @param {jQuery} el
	 */
	removeSearchTerm: function(el) {

		if (el.data('handler') && el.data('handler').destroy) {
			el.data('handler').destroy();
		}

		el.remove();

		if (!$('.term:first', this.wrapperEl).length) {
			this.wrapperEl.removeClass('with-terms');
		}
	},

	/**
	 * Drop down the search builder for the current context
	 */
	showBuilder: function() {

	},


	/**
	 * Clear the search terms
	 */
	clear: function() {

	},

	/**
	 * Get the prefix name for search term form elements
	 * @param name
	 */
	getFormTermNamePrefix: function() {
		return 'terms[' + Orb.uuid() + ']';
	}
});
