define(['com.deskpro.apps.test/js/TicketController'], function(TicketController) {
	return {
		init: function() {
			this.registerWidgetTab('ticket', '@properties.tab', "Test Tab ({{tab.count}})", 'Ticket/after-props.html', function($scope, $app) {
				$scope.name = $app.getPackageName();
			});
		}
	}
});