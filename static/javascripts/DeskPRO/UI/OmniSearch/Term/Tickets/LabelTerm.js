Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A context is a group of search terms
 */
DeskPRO.UI.OmniSearch.Term.Tickets.LabelTerm = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.InputTermAbstract,

	init: function() {

	},

	getTriggerWords: function() {
		return ['label', 'labels', 'labelled'];
	},

	getHiddenFields: function() {
		return {
			'type': 'label',
			'op': 'is'
		};
	},

	getLabel: function() {
		return 'Label';
	},

	getInputName: function() {
		return 'input';
	}
});