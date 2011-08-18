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
			wrapperEl: null
		};

		this.setOptions(options);

		this.wrapperEl = $(this.options.wrapperEl);
		this.inputEl   = $('#omnisearch_input');

		$('.clear-trigger:first', this.wrapperEl).click((function(ev) {
			ev.preventDefault();
			this.clear();
		}).bind(this));

		$('.builder-trigger:first', this.wrapperEl).click((function(ev) {
			ev.preventDefault();
			this.showBuilder();
		}).bind(this));

		this.contexts = {};
		this.activeContextId = null;

		this.wrapperEl.click(function(ev) {
			if (ev.target == this) {
				$('#omnisearch_input').focus();
			}
		});

		var self = this;
		$('#omnisearch_input').keypress(function(ev) {
			if (ev.which == 58 /* colon : key */) {
				ev.preventDefault();
				var val = $(this).val().trim();
				$(this).val('');
				$(this).blur();

				self.addSearchTermByTrigger(val);
			} else if (ev.which == 8 /* backspace */) {
				if (!$(this).val().trim().length) {
					var prevTerm = $('#omnisearch_input').prev();
					if (prevTerm.length && prevTerm.is('.term')) {
						prevTerm.remove();
					}
				}
			}
		});

		this.addEvent('termInputDone', function() {
			$('#omnisearch_input').focus();
		});
	},


	/**
	 * Lazy-init menu element and return it.
	 * Use in addContext to add the context to the menu
	 */
	_getContextMenuEl: function() {
		if (!this.contextMenuEl) {
			this.contextMenuEl = $('<ul style="display:none;" id="omnisearch_context_menu" />');
			this.contextMenuEl.appendTo('body');

			this.contextMenu = new DeskPRO.UI.Menu({
				triggerElement: $('#omnisearch_type'),
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

		$('#omnisearch_type .label').text(context.getLabel());
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
			removeTrigger.click(function() {
				el.remove();
			});
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