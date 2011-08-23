Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishValidatingContent = new Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	wrapper: null,

	initPage: function(el) {
		this.wrapper = el;
		this.initRoutesOnCollection($('.with-route', el));

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {

		});

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.validating.list-remove', function (info) {
			$('article.' + info.typename + '-' + info.contentId).slideUp();
		});
	}
});
