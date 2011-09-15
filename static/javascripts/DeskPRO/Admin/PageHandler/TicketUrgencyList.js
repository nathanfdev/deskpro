Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TicketUrgencyList = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		this.initPopoutTriggers();
		DeskPRO_Window.getMessageBroker().addMessageListener('ticket_urgency.list.change', this.handleListChange, this);

		$('#urgency_options_form').ajaxForm({
			dataType: 'json'
		});
	},

	handleListChange: function(info) {
		var list = $('ul.item-list:first');
		var exist = $('li.locale-'+info.locale_id, list);

		var row = $(info.row_html);
		this.initPopoutTriggers(row);

		if (exist.length) {
			exist.replaceWith(row);
		} else {
			list.prepend(row);
		}
	}
});
