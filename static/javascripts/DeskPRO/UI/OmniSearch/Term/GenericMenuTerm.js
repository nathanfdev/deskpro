Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A context is a group of search terms
 *
 * @option {jQuery} menuEl
 * @option {Object} fields
 * @option {String} inputName
 * @option {String} menuDataKey
 */
DeskPRO.UI.OmniSearch.Term.GenericMenuTerm = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.MenuTermAbstract,

	getHiddenFields: function() {
		return this.options.fields;
	},

	getInputName: function() {
		return this.options.inputName;
	},

	getDataKey: function() {
		return this.options.menuDataKey;
	}
});