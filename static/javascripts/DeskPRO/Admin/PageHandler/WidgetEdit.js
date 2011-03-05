Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.WidgetEdit = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	widget_id: 0,
	initialize: function(widget_id) {
		this.widget_id = widget_id;
	},

	initPage: function() {
		var self = this;
		$('.save-trigger').click(function() {
			$('form:first').submit();
		});
		$('.cancel-trigger').click(function() {
			self.closeThisPopout();
		});
	},

	tellParentUpdated: function() {
		var parent_win = this.getOpenerDeskPRO('DeskPRO_Page_TicketWidgets');
		if (!parent_win) return;

		parent_win.getMessageBroker().sendMessage('widget.change', {widget_id: this.widget_id});
	},

	updateParentListRow: function(row_html) {
		var parent_win = this.getOpenerDeskPRO('DeskPRO_Page_TicketWidgets');
		if (!parent_win) return;

		var data = {};
		data['item_selector'] = 'li.widget-' + this.widget_id;
		data['row_html'] = row_html;

		parent_win.getMessageBroker().sendMessage('list.change', data);
	}
});