Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.OrganizationCustomFilter = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicOrganizationResults,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'organization-custom-filter';

		this.resultTypeName = 'filter';
		this.resultTypeId = 0;
	},

	initPage: function(el) {
		this.parent(el);
		this.resultTypeId = this.getMetaData('cache_id');
	}
});
