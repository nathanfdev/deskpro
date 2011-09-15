Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishDraftsList = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		this.wrapper = el;

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {});
		this.ownObject(this.selectionBar);

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.drafts.list-remove', function (info) {
			$('article.' + info.typename + '-' + info.contentId, this.wrapper).slideUp();
		}, this);

		this.actionsMenu = new DeskPRO.UI.Menu({
			triggerElement: $('button.perform-actions-trigger:first', this.wrapper),
			menuElement: $('ul.actions-menu:first', this.wrapper),
			onItemClicked: function(info) {
				var data = [];
				var lines = [];
				$('input.item-select:checked', this.wrapper).each(function() {
					lines.push($(this).parent().get(0));
					var typename = $(this).data('content-type');
					var id = $(this).data('content-id');

					data.push({
						name: 'content[' + typename + '][]',
						value: id
					});
				});

				if (!data.length) {
					return;
				}

				var action = $(info.itemEl).data('action');

				$.ajax({
					url: BASE_URL + 'agent/publish/drafts/mass-actions/' + action,
					data: data,
					type: 'POST',
					dataType: 'json',
					context: this,
					success: function(data) {
						if (data.affected) {
							Array.each(data.affected, function(info) {
								DeskPRO_Window.getMessageBroker().sendMessage('publish.drafts.list-remove', info);
							}, this);
						}
					}
				});
			}
		});
		this.ownObject(this.actionsMenu);
	}
});
