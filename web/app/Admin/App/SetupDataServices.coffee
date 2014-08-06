define [
	'Admin/Main/DataService/EntityManager',

	'Admin/FeedbackStatuses/DataService/FeedbackStatuses',
	'Admin/FeedbackTypes/DataService/FeedbackTypes',
	'Admin/FeedbackCategories/DataService/FeedbackCategories',
	'Admin/ChannelSms/DataService/SmsAccounts',
	'Admin/TicketAccounts/DataService/TicketAccounts',
	'Admin/Labels/Service/LabelManager'

	'Admin/OptionBuilder/TypesDef/TicketCriteria',
	'Admin/OptionBuilder/TypesDef/TicketActions',
	'Admin/OptionBuilder/TypesDef/TicketFilter',

	'Admin/Main/Service/DataServiceManager',
], (
	Admin_Main_DataService_EntityManager,
	Admin_FeedbackStatuses_DataService_FeedbackStatuses,
	Admin_FeedbackTypes_DataService_FeedbackTypes,
	Admin_FeedbackCategories_DataService_FeedbackCategories,
	Admin_ChannelSms_DataService_SmsAccounts,
	Admin_TicketAccounts_DataService_TicketAccounts,
	Admin_Labels_Service_LabelManager,

	Admin_OptionBuilder_TypesDef_TicketCriteria,
	Admin_OptionBuilder_TypesDef_TicketActions,
	Admin_OptionBuilder_TypesDef_TicketFilter,

	Admin_Main_Service_DataServiceManager
) ->
	return (Module) ->
		Module.service('em', [ ->
			return new Admin_Main_DataService_EntityManager()
		])

		Module.service('FeedbackStatusesData', ['em', 'Api', '$q', (em, Api, $q) ->
			return new Admin_FeedbackStatuses_DataService_FeedbackStatuses(em, Api, $q)
		])

		Module.service('FeedbackTypesData', ['em', 'Api', '$q', (em, Api, $q) ->
			return new Admin_FeedbackTypes_DataService_FeedbackTypes(em, Api, $q)
		])

		Module.service('FeedbackCategoriesData', ['em', 'Api', '$q', (em, Api, $q) ->
			return new Admin_FeedbackCategories_DataService_FeedbackCategories(em, Api, $q)
		])

		Module.service('SmsAccountsData', ['em', 'Api', '$q', (em, Api, $q) ->
			return new Admin_ChannelSms_DataService_SmsAccounts(em, Api, $q)
		])

		Module.service('TicketAccountsData', ['em', 'Api', '$q', (em, Api, $q) ->
			return new Admin_TicketAccounts_DataService_TicketAccounts(em, Api, $q)
		])

		Module.service('LabelManager', ['Api', '$q', (Api, $q) ->
			return new Admin_Labels_Service_LabelManager(Api, $q)
		])

		Module.factory('dpObTypesDefTicketCriteria', [ '$q', 'Api', 'dpTemplateManager', ($q, Api, dpTemplateManager) ->
			return new Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, dpTemplateManager)
		])

		Module.factory('dpObTypesDefTicketActions', [ '$q', 'Api', 'dpTemplateManager', ($q, Api, dpTemplateManager) ->
			return new Admin_OptionBuilder_TypesDef_TicketActions($q, Api, dpTemplateManager)
		])

		Module.factory('dpObTypesDefTicketFilter', [ '$q', 'Api', 'dpTemplateManager', ($q, Api, dpTemplateManager) ->
			return new Admin_OptionBuilder_TypesDef_TicketFilter($q, Api, dpTemplateManager)
		])

		Module.factory('DataService', [ '$injector', ($injector) ->
			return new Admin_Main_Service_DataServiceManager($injector)
		])
