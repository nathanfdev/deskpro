define(['DeskPRO/App/Context/TabContext/TabContext'], function(TabContext) {
	return new Orb.Class({
		Extends: TabContext,

		getInjectables: function() {
			return [
				['$ticket', this.getFragment().meta.api_data],
				['$person', this.getFragment().meta.api_data.person]
			]
		}
	});
});