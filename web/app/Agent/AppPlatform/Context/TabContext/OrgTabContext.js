define(['Agent/AppPlatform/Context/TabContext/TabContext'], function(TabContext) {
	return new Orb.Class({
		Extends: TabContext,

		getInjectables: function() {
			return [
				['$org', this.getFragment().meta.api_data]
			]
		},

		getOrgData: function() {
			return this.getFragment().meta.api_data;
		}
	});
});