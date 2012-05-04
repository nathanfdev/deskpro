Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.PublishValidatingContent = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.validating.list-remove', function (info) {
			var el = $('article.' + info.typename + '-' + info.contentId).slideUp();
			self.listRemove(el);
		});

		this.actionsMenu = new DeskPRO.UI.Menu({
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
							$(lines).fadeOut().each(function() {
								self.listRemove($(this));
							});
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
		this.ownObject(this.actionsMenu);

		this.selectionBar = new DeskPRO.Agent.PageHelper.SelectionBar(this, {
			onButtonClick: function(ev) {
				self.actionsMenu.open(ev);
			}
		});
		this.ownObject(this.selectionBar);

		this.enableHighlightOpenRows('feedback', 'feedback_id', '.row-item.feedback-');
	},

	listRemove: function(el) {
		DeskPRO_Window.util.modCountEl($('#publish_validating_count'), '-');
		DeskPRO_Window.sections.publish_section.recountBadge();
		this.selectionBar.checkNone();
	}
});
