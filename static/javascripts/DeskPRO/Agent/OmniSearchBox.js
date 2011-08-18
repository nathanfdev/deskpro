Orb.createNamespace('DeskPRO.Agent');

DeskPRO.Agent.OmniSearchBox = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.SearchBox,

	getDefaultOptions: function() {
		return {
			wrapperEl: '#omnisearch',
			inputEl: '#omnisearch_input',
			contextBtnEl: '#omnisearch_type'
		};
	},

	init: function() {
		//-----
		// Tickets
		//-----

		context = new DeskPRO.UI.OmniSearch.Context.TicketsContext();

		term = new DeskPRO.UI.OmniSearch.Term.GenericInputTerm({
			triggerWords: ['label', 'labels', 'labelled'],
			fields: {
				'type': 'label',
				'op': 'is'
			},
			label: 'Label',
			inputName: 'label'
		});
		context.addTerm('label', term);

		term = new DeskPRO.UI.OmniSearch.Term.GenericMenuTerm({
			menuEl: $('#department_menu'),
			menuDataKey: 'department-id',
			triggerWords: ['dep', 'department'],
			fields: {
				'type': 'department',
				'op': 'is'
			},
			label: 'Department',
			inputName: 'department'
		});
		context.addTerm('department', term);

		this.addContext('tickets', context);
	}
});