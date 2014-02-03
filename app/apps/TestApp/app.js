define(['com.deskpro.apps.test/js/TicketController'], function(TicketController) {
	return {
		init: function() {
			this.register('ticket', TicketController)
		}
	}
});