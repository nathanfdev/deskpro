define [
	'Admin/TicketTriggers/DataService/BaseTriggers'
], (
	BaseTriggers,
)  ->
	class Admin_TicketTriggers_DataService_TriggersNew extends BaseTriggers
		@$inject = ['Api', '$q']

		init: ->
			@type = 'newticket'