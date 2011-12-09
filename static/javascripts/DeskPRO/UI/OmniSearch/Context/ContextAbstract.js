Orb.createNamespace('DeskPRO.UI.OmniSearch.Context');

/**
 * A context is a group of search terms
 */
DeskPRO.UI.OmniSearch.Context.ContextAbstract = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = this.getDefaultOptions();
		this.setOptions(options);

		this.terms = {};

		this.init();

		this.triggerWords = {};
		this.menu = null;
		this.menuEl = null;
	},

	init: function() {},

	getSearchTermsForm: function() {
		return null;
	},


	/**
	 * Get the term menu
	 *
	 * @return {DeskPRO.UI.Menu}
	 */
	getMenu: function() {
		if (!this.menu) {
			this.menu = new DeskPRO.UI.Menu({
				element: this.menuEl
			});
		}

		return this.menu;
	},


	/**
	 * Set default options
	 */
	getDefaultOptions: function() {
		return {};
	},


	/**
	 * Add a term type to this context
	 * @param id
	 * @param term
	 */
	addTerm: function(id, term) {

		if (!this.menuEl) {
			this.menuEl = $('<ul />');
		}

		this.menuEl.append('<li data-rule-type="' + id + '">' + term.getLabel() + '</li>');

		this.terms[id] = term;

		Array.each(term.getTriggerWords(), function(word) {
			this.triggerWords[word] = id;
		}, this);
	},


	/**
	 * Get a term handler
	 *
	 * @param {String} id The ID of the term
	 * @return {DeskPRO.UI.OmniSearch.TermAbstract}
	 */
	getTerm: function(id) {
		return this.terms[id] || null;
	},


	/**
	 * Gets a id=>label for all terms.
	 *
	 * @return {Object}
	 */
	getTermLabels: function() {
		var labels = {};

		Object.each(this.terms, function(term,id) {
			labels[id] = term.getLabel();
		});

		return labels;
	},


	/**
	 * Get a term ID for a trigger word
	 * @param {String} triggerWord
	 */
	getTermIdByTrigger: function(triggerWord) {
		return this.triggerWords[triggerWord.toLowerCase()] || null;
	},


	/**
	 * Get the term for a trigger
	 *
	 * @param triggerWord
	 */
	getTermByTrigger: function(triggerWord) {
		var termId = this.getTermIdFromTrigger(triggerWord);
		if (!termId) {
			return null;
		}

		return this.getTerm(termId);
	}
});
