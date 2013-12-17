Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Content');

/**
 * Delete/spam things. Toggles visibility of status section, and notice bar,
 * and sens appropriate save ajax.
 */
DeskPRO.Agent.PageFragment.Page.Content.DeleteControl = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.options = {
			ajaxSaveUrl: '',
			type: 'delete'
		};

		this.setOptions(options);
		this.page = page;

		this.deleteBtn      = $('.' + this.options.type, this.page.getEl('action_buttons'));
		this.deletedNotice  = $('.' + this.options.type + '-notice:first', this.page.wrapper);
		this.statusBtn      = $('.the-status:first', this.page.wrapper);
		this.undeleteBtn    = $('.un' + this.options.type, this.deletedNotice);

		this.otherDeleteBtns = $('.delete-type:not(.' + this.options.type + ')', this.page.getEl('action_buttons'));

		this.deleteBtn.on('click', function() {
			self.handleDeleted();
			$.ajax({
				url: self.options.ajaxSaveUrl,
				data: { action: self.options.type },
				type: 'GET',
				dataType: 'json',
				error: function() {
					// just revert UI elements
					self.handleUndelete();
				},
				success: function(html) {
					DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.content_deleted.' + page.TYPENAME);

					if (self.options.reloadSelf && self.page && self.page.meta.refreshUrl) {
						DeskPRO_Window.loadPage(self.page.meta.refreshUrl, {ignoreExist:true});
						self.page.closeSelf();
					}
				}
			});
		});

		this.undeleteBtn.on('click', function() {
			self.handleUndelete();
			$.ajax({
				url: self.options.ajaxSaveUrl,
				data: { action: 'un' + self.options.type },
				type: 'GET',
				dataType: 'json',
				error: function() {
					// just revert UI elements
					self.handleDelete();
				},
				success: function(html) {
					DeskPRO_Window.getMessageBroker().sendMessage('agent.ui.content_undeleted.' + page.TYPENAME);

					if (self.options.reloadSelf && self.page && self.page.meta.refreshUrl) {
						DeskPRO_Window.loadPage(self.page.meta.refreshUrl, {ignoreExist:true});
						self.page.closeSelf();
					}
				}
			});
		});
	},

	undelete: function() {

	},

	handleDeleted: function() {
		this.deleteBtn.hide();
		this.statusBtn.hide();
		this.deletedNotice.show();
		this.otherDeleteBtns.hide();
	},

	handleUndelete: function() {
		this.deleteBtn.show();
		this.statusBtn.show();
		this.deletedNotice.hide();
		this.otherDeleteBtns.show();
	}
});
