Orb.createNamespace('DeskPRO.Agent.UI.OmniSearch.Context');

DeskPRO.Agent.UI.OmniSearch.Context.TicketsContext = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Context.ContextAbstract,

	init: function() {

	},

	getSearchTermsForm: function() {
		return $('#ticket_search_terms_global');
	}
});
