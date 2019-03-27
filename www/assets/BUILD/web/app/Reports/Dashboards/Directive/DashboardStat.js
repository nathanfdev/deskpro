define(function() {
  const Reports_Directive_DashboardStat = ['$state', 'DashboardWidgetService', '$timeout', ($state, DashboardWidgetService, $timeout) =>
    ({
      restrict: 'E',
      replace:  true,
      scope:    {
        widgetId: '@',
        loaded:   '@'
      },
      template: `\
<div style="height: auto">
  <div ng-hide='loaded' class="stat-value no-data">loading...</div>
  <div ng-show='loaded && noData' class="box stat-box"><div class="stat-value no-data">no data</div></div>
  <div ng-show='loaded' class="stat">
      <div class="stat-value"></div>
      <div class="stat-description"></div>
  </div>
</div>\
`,
      link(scope, element, attrs) {
        scope.loaded = false;

        const initValue = function (result) {
          let options;
          scope.loaded = true;

          const el = $(element);
          const box = el.parent();

          if (!result) {
            return;
          }

          scope.noData = false;

          try {
            options = result.options ? JSON.parse(result.options) : {};
          } catch (e) {
            options = {};
            console.warn('invalid options');
            console.log(e);
          }

          const data = result;

          if (options.click_url != null) {
            let url = options.click_url;

            box.css({ cursor: 'pointer' });
            box.click(() => document.dispatchEvent(new CustomEvent('dpWidgetClick', {
              detail: {
                type: 'stat',
                options: options,
                url: url,
                data: result
              }
            })));
          }

          const valueElement = el.find('.stat-value');
          valueElement.html(result.value);
          if (result.description) {
            return el.find('.stat-description').html(result.description);
          }
          return el.find('.stat-description').remove();
        };

        if (attrs.jsCode) {
          var promise = null;
          try {
            eval(attrs.jsCode);
          } catch (error) {
            const e = error;
            console.log(e);
          }

          if (promise && promise.then) {
            promise.then((response) => {
              scope.loaded = true;
              scope.noData = true;
              return $timeout(() => initValue(response)
            , 1);
            });
          }
        } else if (attrs.value) {
          $timeout(() => initValue(attrs)
        , 1);
        } else if (DashboardWidgetService.widgetsResults && DashboardWidgetService.widgetsResults[scope.widgetId]) {
          DashboardWidgetService.widgetsResults[scope.widgetId].promise.then((renderedResult) => {
            scope.loaded = true;
            scope.noData = true;
            if (renderedResult) {
              return initValue(renderedResult);
            }
          });
        } else {
          DashboardWidgetService
          .getWidget(scope.widgetId || 0)
          .then((widget) => {
            scope.loaded = true;
            scope.noData = true;
            if ((widget != null) && widget.rendered_result) {
              return initValue(widget.rendered_result);
            }
          });
        }

      // dynamic handler position
        const el = $(element);
        const box = el.parent();
        const listItem = box.parent();

        return listItem.scroll(() => {
          const valueElementTop = box.offset().top - 47 - listItem.offset().top;

          const resHandlers = listItem.find('.gridster-item-resizable-handler');

          return resHandlers.each(function (index, element) {
            const h = $(this);
            const c = 1 + valueElementTop;
            return h[0].style.bottom = `${c}px`;
          });
        });
      }


    })

  ];

  return Reports_Directive_DashboardStat;
});
