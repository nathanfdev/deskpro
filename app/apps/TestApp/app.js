define(['com.deskpro.apps.test/TicketController'], function(TicketController) {
	return {
		run: function() {
			this.registerController('ticket', TicketController)
		}
	}
});