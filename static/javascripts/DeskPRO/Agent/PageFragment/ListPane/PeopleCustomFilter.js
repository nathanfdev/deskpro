Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PeopleCustomFilter = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.BasicPeopleResults,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'people-custom-filter';

		this.resultTypeName = 'filter';
		this.resultTypeId = 0;
	},

	initPage: function(el) {
		this.parent(el);
		this.resultTypeId = this.getMetaData('cache_id');
	}
});
