Orb.createNamespace('DeskPRO.Admin.PageHandler');

DeskPRO.Admin.PageHandler.TicketAutoCloseList = new Class({
	Extends: DeskPRO.Admin.PageHandler.Basic,

	initPage: function() {
		$('#autoclose_options_form').ajaxForm({
			dataType: 'json'
		});
	}
});