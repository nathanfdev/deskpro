define ->
  Reports_Directive_Widget= ['DashboardWidgetService', (DashboardWidgetService) ->
    return {
      restrict: 'A'
      replace: false
      scope:
        widgetId: '@'
        options: '@'
        title: '@'

      link: (scope) ->
        scope.$on('gridster-item-transition-end', (item) =>
          gridsterItem = item.targetScope.gridsterItem
          gridsterItem.id = scope.widgetId
          gridsterItem.title = scope.title
          gridsterItem.options = scope.options
          DashboardWidgetService.saveWidget(gridsterItem)
        )
    }
  ]

  return Reports_Directive_Widget