Orb.createNamespace('DeskPRO.Agent.UI.OmniSearch.Context');

DeskPRO.Agent.UI.OmniSearch.Context.EverythingContext = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Context.ContextAbstract,

	init: function() {

	},

	getSearchTermsForm: function() {
		return $('#everything_search_terms_global');
	}
});
