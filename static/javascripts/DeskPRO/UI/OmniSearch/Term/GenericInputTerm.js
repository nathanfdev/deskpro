Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A context is a group of search terms
 */
DeskPRO.UI.OmniSearch.Term.GenericInputTerm = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.InputTermAbstract,

	getInputName: function() {
		return this.options.inputName;
	}
});