(function() {
  define(['Admin/Main/DataService/EntityManager', 'Admin/FeedbackStatuses/DataService/FeedbackStatuses', 'Admin/FeedbackTypes/DataService/FeedbackTypes', 'Admin/FeedbackCategories/DataService/FeedbackCategories', 'Admin/ChannelSms/DataService/SmsAccounts', 'Admin/ChannelFacebook/DataService/FacebookPages', 'Admin/TicketAccounts/DataService/TicketAccounts', 'DeskPRO/Service/LabelDefinition', 'Admin/OptionBuilder/TypesDef/TicketCriteria', 'Admin/OptionBuilder/TypesDef/TicketActions', 'Admin/OptionBuilder/TypesDef/TicketFilter', 'Admin/Main/Service/DataServiceManager'], function(Admin_Main_DataService_EntityManager, Admin_FeedbackStatuses_DataService_FeedbackStatuses, Admin_FeedbackTypes_DataService_FeedbackTypes, Admin_FeedbackCategories_DataService_FeedbackCategories, Admin_ChannelSms_DataService_SmsAccounts, Admin_ChannelFacebook_DataService_FacebookPages, Admin_TicketAccounts_DataService_TicketAccounts, DeskPRO_Service_LabelDefinition, Admin_OptionBuilder_TypesDef_TicketCriteria, Admin_OptionBuilder_TypesDef_TicketActions, Admin_OptionBuilder_TypesDef_TicketFilter, Admin_Main_Service_DataServiceManager) {
    return function(Module) {
      Module.service('em', [
        function() {
          return new Admin_Main_DataService_EntityManager();
        }
      ]);
      Module.service('FeedbackStatusesData', [
        'em', 'Api', '$q', function(em, Api, $q) {
          return new Admin_FeedbackStatuses_DataService_FeedbackStatuses(em, Api, $q);
        }
      ]);
      Module.service('FeedbackTypesData', [
        'em', 'Api', '$q', function(em, Api, $q) {
          return new Admin_FeedbackTypes_DataService_FeedbackTypes(em, Api, $q);
        }
      ]);
      Module.service('FeedbackCategoriesData', [
        'em', 'Api', '$q', function(em, Api, $q) {
          return new Admin_FeedbackCategories_DataService_FeedbackCategories(em, Api, $q);
        }
      ]);
      Module.service('SmsAccountsData', [
        'em', 'Api', '$q', function(em, Api, $q) {
          return new Admin_ChannelSms_DataService_SmsAccounts(em, Api, $q);
        }
      ]);
      Module.service('FacebookPagesData', [
        'em', 'Api', '$q', function(em, Api, $q) {
          return new Admin_ChannelFacebook_DataService_FacebookPages(em, Api, $q);
        }
      ]);
      Module.service('TicketAccountsData', [
        'em', 'Api', '$q', function(em, Api, $q) {
          return new Admin_TicketAccounts_DataService_TicketAccounts(em, Api, $q);
        }
      ]);
      Module.factory('dpObTypesDefTicketCriteria', [
        '$q', 'Api', 'dpTemplateManager', function($q, Api, dpTemplateManager) {
          return new Admin_OptionBuilder_TypesDef_TicketCriteria($q, Api, dpTemplateManager);
        }
      ]);
      Module.factory('dpObTypesDefTicketActions', [
        '$q', 'Api', 'dpTemplateManager', function($q, Api, dpTemplateManager) {
          return new Admin_OptionBuilder_TypesDef_TicketActions($q, Api, dpTemplateManager);
        }
      ]);
      Module.factory('dpObTypesDefTicketFilter', [
        '$q', 'Api', 'dpTemplateManager', function($q, Api, dpTemplateManager) {
          return new Admin_OptionBuilder_TypesDef_TicketFilter($q, Api, dpTemplateManager);
        }
      ]);
      Module.factory('DataService', [
        '$injector', function($injector) {
          return new Admin_Main_Service_DataServiceManager($injector);
        }
      ]);
      return Module.service('LabelDefinition', [
        'Api', '$q', function(Api, $q) {
          return new DeskPRO_Service_LabelDefinition($q, Api.sendGet('/labels/definitions'));
        }
      ]);
    };
  });

}).call(this);

//# sourceMappingURL=SetupDataServices.js.map
