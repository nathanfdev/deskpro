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

				var sendFn = function() {
					$.ajax({
						url: BASE_URL + 'agent/publish/content/validating-mass-actions/' + action,
						data: data,
						type: 'POST',
						dataType: 'json',
						success: function() {
							$(lines).fadeOut();
						}
					});
				}

				if (action == 'disapprove') {
					DeskPRO_Window.showPrompt("Enter a reason or comment to send to the authors", function(reason) {
						data.push({
							name: 'reason',
							value: reason
						});
						sendFn();
					});
				} else {
					sendFn();
				}
			}
		});
	}
});
