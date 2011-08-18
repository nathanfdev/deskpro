Orb.createNamespace('DeskPRO.UI.OmniSearch.Term.Tickets');

/**
 * A context is a group of search terms
 */
DeskPRO.UI.OmniSearch.Term.Tickets.DepartmentTerm = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Term.MenuTermAbstract,

	getDefaultOptions: function() {
		return {
			menuEl: $('#department_menu')
		};
	},

	getTriggerWords: function() {
		return ['dep', 'department'];
	},

	getHiddenFields: function() {
		return {
			'type': 'department',
			'op': 'is'
		};
	},

	getLabel: function() {
		return 'Department';
	},

	getInputName: function() {
		return 'department';
	},

	getDataKey: function() {
		return 'department-id';
	}
});