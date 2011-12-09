Orb.createNamespace('DeskPRO.Agent.UI.OmniSearch.Context');

/**
 * A context is a group of search terms
 */
DeskPRO.Agent.UI.OmniSearch.Context.OrganizationsContext = new Orb.Class({
	Extends: DeskPRO.UI.OmniSearch.Context.ContextAbstract,

	init: function() {

	},

	getSearchTermsForm: function() {
		return $('#org_search_terms_global');
	}
});
