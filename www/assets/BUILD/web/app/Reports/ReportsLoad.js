define([
  'angular',
  'angularAnimate',
  'angularSanitize',
  'angularBootstrap',
  'angularSelect2',
  'angularUiRouter',
  'angularUiSortable',
  'angular-moment',
  'angularFileUpload',
  'angularSlider',

  'moment',

  'jquery',
  'jqueryUi',
  'underscore',
  'stacktrace',

  'bootstrapTooltip',

  'select2',
  'toastr',

  'DeskPRO/OptionBuilder/Module',
  'DeskPRO/CategoryBuilder/Module',
  'DeskPRO/Directive/DpDateTimePicker',

  'Reports/App/App',

  'Reports/Main/Ctrl/MainPage',
  'Reports/Main/Ctrl/Bare',

  'Reports/Main/Ctrl/BackToAgent',
  'Reports/Main/Ctrl/GoToAdmin',
  'Reports/Main/Ctrl/GoToBilling',
  'Reports/Main/Ctrl/GoToUser',

  'Reports/Overview/Ctrl/Overview',
  'Reports/Builder/Ctrl/List',
  'Reports/Builder/Ctrl/Edit',
  'Reports/AgentActivity/Ctrl/AgentActivity',
  'Reports/AgentHours/Ctrl/AgentHours',
  'Reports/TicketSatisfaction/Ctrl/TicketSatisfaction',
  'Reports/Billing/Ctrl/List',
  'Reports/Billing/Ctrl/View',
  window.DP_REPORT_BUNDLE_PATH
], function(angular) {

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
      window.dp_get_uid = function() {
        return window.DP_UID_COUNTER++;
      };
      var $html = angular.element(document.getElementsByTagName('html')[0]);

      // All target=blanks need to null out window.opener
      $(document).on('click', 'a[target="_blank"]', function(ev) {
        ev.preventDefault();
        var o = window.open($(this).attr('href'));
        o.opener = null;
      });

      angular.element().ready(function() {
        $html.addClass('ng-app');

        if (window.DP_CTRL_REG) {
          var module = angular.module('Reports_App');
          for (var x = 0; x < window.DP_CTRL_REG.length; x++) {
            module.controller(window.DP_CTRL_REG[x][0], window.DP_CTRL_REG[x][1]);
          }
        }

        angular.bootstrap($html, ['Reports_App']);
      });
    }
  };
});
