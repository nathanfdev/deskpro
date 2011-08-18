Orb.createNamespace('DeskPRO.UI.OmniSearch.Term');

/**
 * A context is a group of search terms
 */
DeskPRO.UI.OmniSearch.Term.TermAbstract = new Orb.Class({
	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = this.getDefaultOptions();
		this.setOptions(options);

		this.terms = {};

		this.init();
	},

	init: function() {},


	/**
	 * Create a new term element.
	 * This should return a jQuery object with one element, the wrapper
	 * around the term to be appended to the wrapper.
	 *
	 * @return {jQuery}
	 */
	createTermElement: function(searchBox) {
		var tpl = document.getElementById('omnisearch_term_tpl').innerHTML;
		var el = $(tpl);
		$('.label', el).text(this.getLabel());
		$('.value', el).text('double-click to edit');

		return el;
	},


	/**
	 * Set default options
	 */
	getDefaultOptions: function() {
		return {};
	},

	
	/**
	 * Get the words that should trigger this term
	 *
	 * @return {Array}
	 */
	getTriggerWords: function() {
		console.error('Abstract method');
	},

	
	/**
	 * Get the label for this context
	 *
	 * @return {String}
	 */
	getLabel: function() {
		console.error('Abstract method');
	}
});