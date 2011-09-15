Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');
DeskPRO.Agent.PageFragment.ListPane.Basic = new Class({
	Extends: DeskPRO.Agent.PageFragment.Basic,

	initialize: function(html) {
		this.parent(html);

		this.addEvent('activate', function() {
			DeskPRO_Window.getMessageBroker().sendMessage('list-page-fragment.activated', { page: this });
		}, this);
	}
});
