define([
  'angular',
  'Admin/App/App',

  'Admin/Apps/Ctrl/List',
  'Admin/Apps/Ctrl/EditInstance',
  'Admin/Apps/Ctrl/EditInstanceV2',
  'Admin/Apps/Ctrl/EditCustomInstance',
  'Admin/Apps/Ctrl/InstallProgress',
  'Admin/Apps/Ctrl/PackageInfo',
  'Admin/Apps/Ctrl/PackageInstall',
  'Admin/Apps/Ctrl/ImportersList',
  'Admin/Apps/Ctrl/ImportersView',
  'Admin/Apps/Ctrl/InstallAppV2',
  'Admin/Apps/Ctrl/UpdateAppV2',
  'Admin/Apps/Ctrl/Resync',
  'Admin/Agents/Ctrl/Edit',
  'Admin/Agents/Ctrl/EditProfile',
  'Admin/Agents/Ctrl/DeletedList',
  'Admin/Agents/Ctrl/DeletedRestore',
  'Admin/Agents/Ctrl/List',
  'Admin/Agents/Ctrl/Logs',
  'Admin/Agents/Ctrl/Import',
  'Admin/Agents/Ctrl/AuditLogs',
  'Admin/Agents/Ctrl/AuditLogsView',
  'Admin/AgentGroups/Ctrl/Edit',
  'Admin/AgentGroups/Ctrl/List',
  'Admin/AgentTeams/Ctrl/Edit',
  'Admin/AgentTeams/Ctrl/List',
  'Admin/EmailStatus/Ctrl/SendmailList',
  'Admin/EmailStatus/Ctrl/SourceList',
  'Admin/EmailStatus/Ctrl/ViewSend',
  'Admin/EmailStatus/Ctrl/ViewSource',
  'Admin/Main/Ctrl/Bare',
  'Admin/Main/Ctrl/BareList',
  'Admin/Main/Ctrl/MainBody',
  'Admin/Main/Ctrl/MainPage',
  'Admin/Main/Ctrl/Home',
  'Admin/Main/Ctrl/BackToAgent',
  'Admin/Main/Ctrl/GoToReports',
  'Admin/Main/Ctrl/GoToUser',
  'Admin/Main/Ctrl/Nav',
  'Admin/CustomFields/Tickets/Ctrl/Edit',
  'Admin/CustomFields/Chat/Ctrl/Edit',
  'Admin/CustomFields/User/Ctrl/Edit',
  'Admin/CustomFields/Org/Ctrl/Edit',
  'Admin/CustomFields/Billing/Ctrl/Edit',
  'Admin/Labels/Ctrl/List',
  'Admin/Labels/Ctrl/Edit',
  'Admin/Languages/Ctrl/Edit',
  'Admin/Languages/Ctrl/Install',
  'Admin/Languages/Ctrl/List',
  'Admin/Languages/Ctrl/PhraseList',
  'Admin/Languages/Ctrl/PhraseGroup',
  'Admin/Languages/Ctrl/PhraseResGroup',
  'Admin/Languages/Ctrl/Settings',
  'Admin/Languages/Ctrl/TranslateMapModal',
  'Admin/Languages/Ctrl/TranslateModal',
  'Admin/License/Ctrl/License',
  'Admin/License/Ctrl/UpgradeLicenseModal',
  'Admin/Tasks/Ctrl/Edit',
  'Admin/Templates/Ctrl/EmailTemplateEditor',
  'Admin/Templates/Ctrl/EmailGroupList',
  'Admin/Templates/Ctrl/EmailGroupListOld',
  'Admin/Templates/Ctrl/EmailList',
  'Admin/Templates/Ctrl/NewEmailTemplateEditor',
  'Admin/Templates/Ctrl/TemplateEditor',
  'Admin/Templates/Ctrl/TemplateGroupList',
  'Admin/Templates/Ctrl/TemplateList',
  'Admin/ChannelSms/Ctrl/List',
  'Admin/ChannelSms/Ctrl/Edit',
  'Admin/ChannelFacebook/Ctrl/List',
  'Admin/ChannelFacebook/Ctrl/Edit',
  'Admin/ChannelFacebook/Ctrl/Create',
  'Admin/TicketAccounts/Ctrl/List',
  'Admin/TicketAccounts/Ctrl/Edit',
  'Admin/TicketAccounts/Ctrl/Settings',
  'Admin/TicketBilling/Ctrl/Fields',
  'Admin/TicketDeps/Ctrl/List',
  'Admin/TicketDeps/Ctrl/Edit',
  'Admin/TicketEscalations/Ctrl/List',
  'Admin/TicketEscalations/Ctrl/Edit',
  'Admin/TicketEscalations/Ctrl/EditSatisfaction',
  'Admin/TicketEscalations/Ctrl/EditStatuses',
  'Admin/TicketFields/Ctrl/EditCategories',
  'Admin/TicketFields/Ctrl/EditPriorities',
  'Admin/TicketFields/Ctrl/EditProducts',
  'Admin/TicketFields/Ctrl/EditWorkflows',
  'Admin/TicketFields/Ctrl/List',
  'Admin/TicketFilters/Ctrl/List',
  'Admin/TicketFilters/Ctrl/Edit',
  'Admin/TicketMacros/Ctrl/List',
  'Admin/TicketMacros/Ctrl/Edit',
  'Admin/TicketSlas/Ctrl/List',
  'Admin/TicketSlas/Ctrl/Edit',
  'Admin/TicketSettings/Ctrl/TicketSettings',
  'Admin/TicketSettings/Ctrl/FwdSettings',
  'Admin/TicketStatuses/Ctrl/List',
  'Admin/TicketStatuses/Ctrl/EditAwaitingAgent',
  'Admin/TicketStatuses/Ctrl/EditAwaitingUser',
  'Admin/TicketStatuses/Ctrl/EditArchived',
  'Admin/TicketStatuses/Ctrl/EditHiddenDeleted',
  'Admin/TicketStatuses/Ctrl/EditHiddenSpam',
  'Admin/TicketStatuses/Ctrl/EditHiddenValidating',
  'Admin/TicketStatuses/Ctrl/EditResolved',
  'Admin/TicketTriggers/Ctrl/EditDepartmentTrigger',
  'Admin/TicketTriggers/Ctrl/EditEmailAccountTrigger',
  'Admin/TicketTriggers/Ctrl/EditSatisfactionTrigger',
  'Admin/TicketTriggers/Ctrl/Edit',
  'Admin/TicketTriggers/Ctrl/List',
  'Admin/TicketUrgencies/Ctrl/List',
  'Admin/TicketProblems/Ctrl/Settings',
  'Admin/FeedbackStatuses/Ctrl/List',
  'Admin/FeedbackStatuses/Ctrl/Edit',
  'Admin/FeedbackTypes/Ctrl/List',
  'Admin/FeedbackTypes/Ctrl/Edit',
  'Admin/FeedbackCategories/Ctrl/List',
  'Admin/FeedbackCategories/Ctrl/Edit',
  'Admin/TwitterSetup/Ctrl/TwitterSetup',
  'Admin/TwitterAccounts/Ctrl/List',
  'Admin/TwitterAccounts/Ctrl/Edit',
  'Admin/Portal/Ctrl/Nav',
  'Admin/Portal/Ctrl/PortalEditor',
  'Admin/Portal/Ctrl/WidgetEditor',
  'Admin/Portal/Ctrl/TicketFormWidget',
  'Admin/Portal/Ctrl/Setup',
  'Admin/Server/Ctrl/ServerReqs',
  'Admin/Server/Ctrl/ServerPhpInfo',
  'Admin/Server/Ctrl/ServerMysqlInfo',
  'Admin/Server/Ctrl/ServerMysqlStatus',
  'Admin/Server/Ctrl/ServerMysqlSortOrder',
  'Admin/Server/Ctrl/ServerErrorLogs',
  'Admin/Server/Ctrl/ServerErrorLogsView',
  'Admin/Server/Ctrl/ServerIncidents',
  'Admin/Server/Ctrl/ServerIncidentsView',
  'Admin/Server/Ctrl/ServerIncidentsEvent',
  'Admin/Server/Ctrl/ServerTaskQueue',
  'Admin/Server/Ctrl/ServerCronList',
  'Admin/Server/Ctrl/ServerCronLogs',
  'Admin/Server/Ctrl/ServerEnc',
  'Admin/Server/Ctrl/ServerFileUploads',
  'Admin/Server/Ctrl/ServerFileCheck',
  'Admin/Server/Ctrl/ServerReportFile',
  'Admin/Server/Ctrl/ServerJobsList',
  'Admin/Server/Ctrl/ServerJobsView',
  'Admin/Settings/Ctrl/AdvancedSettings',
  'Admin/Settings/Ctrl/ElasticSearch',
  'Admin/Settings/Ctrl/GeneralSettings',
  'Admin/Settings/Ctrl/PortalSettings',
  'Admin/Settings/Ctrl/RegSettings',
  'Admin/Settings/Ctrl/PasswordSettings',
  'Admin/Settings/Ctrl/ServerSettings',
  'Admin/Settings/Ctrl/UpdaterSettings',
  'Admin/Settings/Ctrl/ResetHelpdesk',
  'Admin/UserGroups/Ctrl/List',
  'Admin/UserGroups/Ctrl/Edit',
  'Admin/Usersources/Ctrl/UsersourcesList',
  'Admin/Usersources/Ctrl/Edit',
  'Admin/Usersources/Ctrl/New',
  'Admin/Usersources/Ctrl/EditInstance',
  'Admin/Usersources/Ctrl/EditDeskproInstance',
  'Admin/Usersources/Ctrl/SyncInformation',
  'Admin/Usersources/Helper/UsersourceTypeDecider',
  'Admin/ChatFields/Ctrl/List',
  'Admin/ChatDeps/Ctrl/List',
  'Admin/ChatDeps/Ctrl/Edit',
  'Admin/ApiKeys/Ctrl/List',
  'Admin/ApiKeys/Ctrl/Edit',
  'Admin/ApiKeys/Ctrl/Logs',
  'Admin/ApiKeys/Ctrl/LogsView',
  'Admin/UserFields/Ctrl/List',
  'Admin/OrgFields/Ctrl/List',
  'Admin/Banning/Ctrl/List',
  'Admin/Banning/Ctrl/EditIp',
  'Admin/Banning/Ctrl/EditEmail',
  'Admin/ImportCsv/Ctrl/ImportCsv',
  'Admin/ExportCsv/Ctrl/ExportCsv',
  'Admin/UserRules/Ctrl/List',
  'Admin/UserRules/Ctrl/Edit',
  'Admin/KbSettings/Ctrl/KbSettings',
  'Admin/DownloadsSettings/Ctrl/DownloadsSettings',
  'Admin/NewsSettings/Ctrl/NewsSettings',
  'Admin/FeedbackSettings/Ctrl/FeedbackSettings',
  'Admin/GuidesSettings/Ctrl/GuidesSettings',
  'Admin/RoundRobin/Ctrl/List',
  'Admin/RoundRobin/Ctrl/Edit',
  'Admin/ChatRoundRobin/Ctrl/List',
  'Admin/ChatRoundRobin/Ctrl/Edit',
  'Admin/Icons/Ctrl/List',
  'Admin/CustomFields/Ctrl/Edit',
  'Admin/AntiAbuse/Ctrl/CaptchaSettings',
  'Admin/AntiAbuse/Ctrl/EmailRateLimiting',
  'Admin/AntiAbuse/Ctrl/PortalRateLimiting',
  'Admin/ReactRoutes/Ctrl/ReactComponent',
  'Admin/Main/Ctrl/Features',
  'Admin/Settings/Ctrl/Notifications/List',

  'CloudAdminLoad',
  window.DP_ADMIN_BUNDLE_PATH

], function(angular) {

  /**
   * ace editor hotfix
   * see https://github.com/angular-ui/ui-ace/issues/104
   * @type {Function}
   */
  var old = window.ace.edit;
  window.ace.edit = function() {
    var instance = old.apply(old, arguments);
    instance.$blockScrolling = Infinity;
    return instance;
  };

  if (!window.console) {
    window.console = {
      log: function(){},
      warn: function(){},
      error: function(){}
    };
  }

  return {
    start: function() {
      window.DP_UID_COUNTER = 0;
      window.dp_get_uid = function () {
        return window.DP_UID_COUNTER++;
      };
      var $html = angular.element(document.getElementsByTagName('html')[0]);

      // All target=_blanks need to null out window.opener
      // to prevent malicious third-parties from trying to redirect us
      $(document).on('click', 'a[target="_blank"]', function(ev) {
        ev.preventDefault();
        var o = window.open($(this).attr('href'));
        o.opener = null;
      });

      // Add special 'bare-page' class to indicate
      // that we aren't in agent wrapper
      if (!window.parent || !window.parent.DP_SPA_PAGE_ID || window.parent.DP_SPA_PAGE_ID !== 'agent') {
        $('body').addClass('bare-page');
      }

      angular.element().ready(function () {
        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          var module = angular.module('Admin_App');
          for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
            module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        angular.bootstrap($html, ['Admin_App']);

        $('#admin_loading').remove();
      });
    }
  };
});
