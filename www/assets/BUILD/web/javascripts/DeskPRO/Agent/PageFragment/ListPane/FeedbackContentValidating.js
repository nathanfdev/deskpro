Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.FeedbackContentValidating = new Orb.Class({
	Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

	initPage: function(el) {
		var self = this;
		this.wrapper = el;
		var btn  = this.wrapper.find('.list-selection-bar .perform-actions-trigger');
		var load = this.wrapper.find('.list-selection-bar .ajax-loading');

		DeskPRO_Window.getMessageBroker().addMessageListener('publish.validating.list-remove', function (info) {
			self.selectionBar.checkNone();
		});

		this.actionsMenu = new DeskPRO.UI.Menu({
			menuElement: $('ul.actions-menu:first', this.wrapper),
			triggerElement: $('.perform-actions-trigger:first', this.wrapper),
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

				btn.hide();
				load.show();

				var sendFn = function() {
					$.ajax({
						url: BASE_URL + 'agent/feedback/validating-mass-actions/' + action,
						data: data,
						type: 'POST',
						dataType: 'json',
						complete: function() {
							load.hide();
							btn.show();
						},
						success: function() {
							$(lines).fadeOut().each(function() {
								self.listRemove($(this));
							});
						}
					});
				};

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
	}
});
