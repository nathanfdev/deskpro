// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Strings'], function(Strings) {
  const Reports_Directive_TicketSatisfaction = ['$compile', 'TicketSatisfactionService', '$sce', '$http', ($compile, TicketSatisfactionService, $sce, $http) =>
    ({
    restrict: 'E',
    replace: true,
    scope: {
      innerType: '@',
      myIndex: '@',
      outerType: '@'
    },

    link(scope, element) {
      scope.service = TicketSatisfactionService;
      scope.dp_spin_els = scope.service.dp_spin_els;
      switch (scope.innerType) {
        case 'feed':
          TicketSatisfactionService.loadFeedResults();
          break;
        case 'summary':
          TicketSatisfactionService.loadSummaryResults();
          break;
      }
      const templateUrl = `ReportsInterfaceBundle:TicketSatisfaction:${scope.innerType}-wrapper.html`;
      return $http.get(templateUrl).then(function(response) {
        const tpl = $sce.trustAsHtml(response.data);
        const template = $sce.getTrustedHtml(tpl);
        const linkWith = $compile(template);
        const content = linkWith(scope);
        return element.replaceWith(content);
      });
    }
    })
  
  ];

  return Reports_Directive_TicketSatisfaction;
});