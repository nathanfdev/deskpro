Orb.createNamespace('DeskPRO.Agent.UI.OmniSearch.Context');

/**
 * A context is a group of search terms
 */
DeskPRO.Agent.UI.OmniSearch.Context.PeopleContext = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Context.ContextAbstract,

	init: function() {

	},

	getSearchTermsForm: function() {
		return $('#people_search_terms_global');
	}
});
