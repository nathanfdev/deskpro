define([
  'Admin/Main/DataService/EntityManager',

  'Admin/FeedbackStatuses/DataService/FeedbackStatuses',
  'Admin/FeedbackTypes/DataService/FeedbackTypes',
  'Admin/FeedbackCategories/DataService/FeedbackCategories',
  'Admin/ChannelSms/DataService/SmsAccounts',
  'Admin/Brand/DataService/Brands',
  'Admin/ChannelFacebook/DataService/FacebookPages',
  'Admin/TicketAccounts/DataService/TicketAccounts',
  'DeskPRO/Service/LabelDefinition',

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
	Admin_Brand_DataService_Brands,
	Admin_ChannelFacebook_DataService_FacebookPages,
	Admin_TicketAccounts_DataService_TicketAccounts,
	DeskPRO_Service_LabelDefinition,

	Admin_OptionBuilder_TypesDef_TicketCriteria,
	Admin_OptionBuilder_TypesDef_TicketActions,
	Admin_OptionBuilder_TypesDef_TicketFilter,

	Admin_Main_Service_DataServiceManager
) =>
	function (Module) {
  Module.service('em', [() => new Admin_Main_DataService_EntityManager()
  ]);

  Module.service('FeedbackStatusesData', ['em', 'Api', '$q', (em, Api, $q) => new Admin_FeedbackStatuses_DataService_FeedbackStatuses(em, Api, $q)
  ]);

  Module.service('FeedbackTypesData', ['em', 'Api', '$q', (em, Api, $q) => new Admin_FeedbackTypes_DataService_FeedbackTypes(em, Api, $q)
  ]);

  Module.service('FeedbackCategoriesData', ['em', 'Api', '$q', (em, Api, $q) => new Admin_FeedbackCategories_DataService_FeedbackCategories(em, Api, $q)
  ]);

  Module.service('SmsAccountsData', ['em', 'Api', '$q', (em, Api, $q) => new Admin_ChannelSms_DataService_SmsAccounts(em, Api, $q)
  ]);

  Module.service('BrandData', ['em', 'Api', '$q', (em, Api, $q) => new Admin_Brand_DataService_Brands(em, Api, $q)
  ]);

  Module.service('FacebookPagesData', ['em', 'Api', '$q', (em, Api, $q) => new Admin_ChannelFacebook_DataService_FacebookPages(em, Api, $q)
  ]);

  Module.service('TicketAccountsData', ['em', 'Api', '$q', (em, Api, $q) => new Admin_TicketAccounts_DataService_TicketAccounts(em, Api, $q)
  ]);

  Module.factory('dpObTypesDefTicketCriteria', ['$q', 'Api', 'Api2', 'dpTemplateManager', ($q, Api, Api2, dpTemplateManager) => new Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, Api2, dpTemplateManager)
  ]);

  Module.factory('dpObTypesDefTicketActions', ['$q', 'Api', 'Api2', 'dpTemplateManager', ($q, Api, Api2, dpTemplateManager) => new Admin_OptionBuilder_TypesDef_TicketActions($q, Api, Api2, dpTemplateManager)
  ]);

  Module.factory('dpObTypesDefTicketFilter', ['$q', 'Api', 'Api2', 'dpTemplateManager', ($q, Api, Api2, dpTemplateManager) => new Admin_OptionBuilder_TypesDef_TicketFilter($q, Api, Api2, dpTemplateManager)
  ]);

  Module.factory('DataService', ['$injector', $injector => new Admin_Main_Service_DataServiceManager($injector)
  ]);

  return Module.service('LabelDefinition', ['Api', '$q', (Api, $q) => new DeskPRO_Service_LabelDefinition($q, Api.sendGet('/labels/definitions'))
  ]);
}
);
