define [
	'Admin/Main/DataService/BaseListEdit',
], (
	Admin_Main_DataService_BaseListEdit,
)  ->
	class Admin_RoundRobin_DataService_RoundRobin extends Admin_Main_DataService_BaseListEdit
		@$inject = ['Api', '$q']

		url: -> '/round_robin'
