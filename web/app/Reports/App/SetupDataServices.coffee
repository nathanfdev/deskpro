define [
	'Admin/Main/DataService/EntityManager',
], (
	Admin_Main_DataService_EntityManager,
) ->
	return (Module) ->

		Module.service('em', [ ->
			return new Admin_Main_DataService_EntityManager()
		])