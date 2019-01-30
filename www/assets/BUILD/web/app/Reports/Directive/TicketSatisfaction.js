define ['DeskPRO/Util/Strings'], (Strings) ->
  Reports_Directive_TicketSatisfaction = ['$compile', 'TicketSatisfactionService', '$sce', '$http', ($compile, TicketSatisfactionService, $sce, $http) ->
    return {
    restrict: 'E'
    replace: true
    scope:
      innerType: '@'
      myIndex: '@'
      outerType: '@'

    link: (scope, element) ->
      scope.service = TicketSatisfactionService
      scope.dp_spin_els = scope.service.dp_spin_els
      switch scope.innerType
        when 'feed'
          TicketSatisfactionService.loadFeedResults()
        when 'summary'
          TicketSatisfactionService.loadSummaryResults()
      templateUrl = "ReportsInterfaceBundle:TicketSatisfaction:#{scope.innerType}-wrapper.html"
      $http.get(templateUrl).then (response) ->
        tpl = $sce.trustAsHtml response.data
        template = $sce.getTrustedHtml tpl
        linkWith = $compile template
        content = linkWith scope
        element.replaceWith content
    }
  ]

  return Reports_Directive_TicketSatisfaction