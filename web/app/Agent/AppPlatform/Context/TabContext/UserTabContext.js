define(['Agent/AppPlatform/Context/TabContext/TabContext'], function(TabContext) {
	return new Orb.Class({
		Extends: TabContext,

		getInjectables: function() {
			return [
				['$person', this.getFragment().meta.api_data],
				['$user', this.getFragment().meta.api_data],  // alias
			]
		},

		getPersonData: function() {
			return this.getFragment().meta.api_data;
		}
	});
});