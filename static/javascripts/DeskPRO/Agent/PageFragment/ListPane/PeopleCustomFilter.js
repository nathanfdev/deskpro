Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PeopleCustomFilter = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicPeopleResults,

	TYPENAME: 'people-custom-filter',

	resultTypeName: 'filter',
	resultTypeId: 0,

	initPage: function(el) {
		this.parent(el);
		this.resultTypeId = this.getMetaData('cache_id');
	}
});