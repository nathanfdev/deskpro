// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(function() {
  const Reports_Directive_Widget= ['DashboardWidgetService', DashboardWidgetService =>
    ({
      restrict: 'A',
      replace: false,
      scope: {
        widgetId: '@',
        options: '@',
        title: '@'
      },

      link(scope) {
        return scope.$on('gridster-item-transition-end', item => {
          const { gridsterItem } = item.targetScope;
          gridsterItem.id = scope.widgetId;
          gridsterItem.title = scope.title;
          gridsterItem.options = scope.options;
          return DashboardWidgetService.saveWidget(gridsterItem);
        });
      }
    })
  
  ];

  return Reports_Directive_Widget;
});