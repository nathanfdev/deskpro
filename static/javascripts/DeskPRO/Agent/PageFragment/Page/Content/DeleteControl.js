Orb.createNamespace('DeskPRO.Agent.PageFragment.Page.Content');

/**
 * Management of participants in the ticket
 */
DeskPRO.Agent.PageFragment.Page.Content.DeleteControl = new Orb.Class({
	Implements: [Orb.Util.Events, Orb.Util.Options],

	initialize: function(page, options) {
		var self = this;

		this.options = {
			ajaxSaveUrl: '',
			statusMenu: null
		};

		this.setOptions(options);
		this.page = page;

		this.deleteBtn      = $('.delete', this.page.getEl('action_buttons'));
		this.deletedNotice  = $('.deleted-notice:first', this.page.wrapper);
		this.statusBtn      = $('.the-status:first', this.page.wrapper);
		this.undeleteBtn    = $('.undelete', this.deletedNotice);

		this.undeleteBtn.click(function(ev) {
			ev.customEvents = new Orb.Util.EventObj({
				onItemClicked: function() {
					self.handleUndelete();
				}
			});
			self.options.statusMenu.open(ev);
		});

		this.deleteBtn.click(function() {
			self.handleDeleted();
			$.ajax({
				url: self.options.ajaxSaveUrl,
				data: { action: 'delete' },
				type: 'GET',
				dataType: 'json',
				error: function() {
					// just revert UI elements
					self.handleUndelete();
				},
				success: function(html) {

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
	},

	handleUndelete: function() {
		this.deleteBtn.show();
		this.statusBtn.show();
		this.deletedNotice.hide();
	}
});
