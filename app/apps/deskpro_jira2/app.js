define(['deskpro_jira2/Ticket/ListCtrl'], function(ListCtrl) {
	return {
		init: function() {
			this.registerAppWidget('ticket', 'Ticket/list.html', ListCtrl);
		}
	}
});