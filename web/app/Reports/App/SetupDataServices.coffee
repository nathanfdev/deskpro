define [
	'Admin/Main/DataService/EntityManager',
	'Reports/Main/Service/DataServiceManager',
], (
	Admin_Main_DataService_EntityManager,
	Reports_Main_Service_DataServiceManager
) ->
	return (Module) ->

		Module.service('em', [ ->
			return new Admin_Main_DataService_EntityManager()
		])

		Module.factory('DataService', [ '$injector', ($injector) ->
			return new Reports_Main_Service_DataServiceManager($injector)
		])