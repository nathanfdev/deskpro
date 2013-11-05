define [
	'Admin/TicketTriggers/DataService/BaseTriggers'
], (
	BaseTriggers,
)  ->
	class Admin_TicketTriggers_DataService_TriggersUpdate extends BaseTriggers
		@$inject = ['Api', '$q']

		init: ->
			@type = 'update'