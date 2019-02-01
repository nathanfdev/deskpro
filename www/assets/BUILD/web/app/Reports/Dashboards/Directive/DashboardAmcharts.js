define(['handlebars'], function(Handlebars) {
  const Reports_Directive_Amcharts = ['$compile', '$state', 'DashboardWidgetService', '$timeout', ($compile, $state, DashboardWidgetService, $timeout) =>
    ({
      restrict: 'E',
      replace: true,
      scope: {
        widgetId: '@',
        chartData: '@',
        jsCode: '@',
        reportLevelVars: '@',
        renderType: '@',
        options: '@',
        version: '@',
        widgetType: '@',
        chartType: '@',
        loaded: '@',
        control: '='
      },

      link(scope, element) {
        window.initHandlebars(Handlebars);

        let template = `\
<div>
  <div ng-hide='loaded' class="box stat-box"><div class="stat-value no-data">loading...</div></div>
  <div ng-show='loaded && noData' class="box stat-box"><div class="stat-value no-data">no data</div></div>
  <div ng-show='loaded' id="ch${scope.widgetId}"></div>
</div>\
`;
        const linkFn = $compile(template);
        const content = linkFn(scope);
        element.replaceWith(content);
        let chart = false;
        scope.loaded = false;
        scope.noData = false;



        const chartDiv    = angular.element(document.getElementById(`ch${scope.widgetId}`));
        const chartParent = chartDiv.parent().parent();
        const chartHeader = chartDiv.parent().siblings('.box-header');
        let chartData   = scope.chartData ? JSON.parse(scope.chartData) : [];
        let { options }     = scope;
        let drawn       = false;
        let interval    = false;

        scope.control.print = function() {
          if (chart) {
            return chart.export.capture(
              {},
              function() {
                return this.toPRINT();
            });
          }
        };

        scope.$watch('chartData', function(n) {
          if (!drawn) { return; }
          chartData = n ? JSON.parse(n) : [];
          return initChart();
        });

        scope.$watch('options', function(n) {
          let newOptions;
          if (!drawn) { return; }
          try {
            newOptions = JSON.parse(n);
          } catch (e) {
            newOptions = {};
          }
          if (!angular.equals(newOptions, options)) {
            options = angular.copy(newOptions);
            return drawWidget(chartData);
          }
        });

        var initChart = function() {
          if (scope.widgetType !== 'graph') {
            return;
          }

          // this is valid for serial and pie charts, gauge has not dataProvider
          if ((chartData && (chartData.dataProvider != null)) || ((scope.chartType === 'gauge') && (__guard__(chartData.axes != null ? chartData.axes[0] : undefined, x => x.bands) != null))) {
            return $timeout(() => drawWidget(chartData)
            , 1);
          } else if (scope.jsCode) {
            try {
              eval(scope.jsCode);
            } catch (e) {
              console.log(e);
            }

            if (promise && promise.then) {
              return promise.then(function(response) {
                scope.loaded = true;
                scope.noData = true;

                return drawWidget(response);
              });
            }
          } else if (DashboardWidgetService.widgetsResults && DashboardWidgetService.widgetsResults[scope.widgetId]) {
            return DashboardWidgetService.widgetsResults[scope.widgetId].promise.then(renderedResult => {
              scope.loaded = true;
              scope.noData = true;
              if (renderedResult && (renderedResult.dataProvider || (__guard__(renderedResult.axes != null ? renderedResult.axes[0] : undefined, x1 => x1.bands) != null))) {
                return drawWidget(renderedResult);
              }
            });
          } else {
            return DashboardWidgetService
              .getWidget(scope.widgetId || 0)
              .then(widget => {
                scope.loaded = true;
                scope.noData = true;

                if ((widget != null) && widget.rendered_result && (widget.rendered_result.dataProvider || (__guard__(widget.rendered_result.axes != null ? widget.rendered_result.axes[0] : undefined, x1 => x1.bands) != null))) {
                  return drawWidget(widget.rendered_result);
                }
            });
          }
        };

        var drawWidget = widget =>
          $timeout(function() {
            scope.loaded = true;
            scope.noData = false;
            return doDrawWidget(widget);
          }
          , 1)
        ;

        var doDrawWidget = function(widget) {
          let dataItem;
          drawn = true;
          if (interval) { clearInterval(interval); }
          if (chart) {
            chart.clear();
            chart.destroy();
            chart = null;
          }

          try {
            options = scope.options ? JSON.parse(scope.options) : {};
          } catch (e) {
            options = {};
            console.warn("invalid options");
            console.log(e);
          }

          options.theme = 'light';

          if ((widget.dataProvider != null) && (widget.dataProvider[0] != null) && ((Object.keys(widget.dataProvider[0]).length > 6) || ((widget.type === 'pie') && (widget.dataProvider.length > 6)))) {
            widget.legend = false;
          }

          if (widget.valueAxes && widget.valueAxes[0] && (widget.valueAxes[0].hash || widget.valueAxes[0].labelTemplate)) {
            widget.valueAxes[0].labelFunction = function(value) {
              const { hash } = widget.valueAxes[0];
              let finalValue = value;
              if (hash && hash[value]) {
                finalValue = hash[value];
              }
              if (widget.valueAxes[0].labelTemplate) {
                template = Handlebars.compile(widget.valueAxes[0].labelTemplate);
                finalValue = template({ 'value': finalValue });
              }

              return finalValue;
            };
          }

          if (widget.valueAxes && widget.valueAxes[1] && widget.valueAxes[1].hash) {
            widget.valueAxes[1].labelFunction = function(value) {
              const { hash } = widget.valueAxes[1];
              if (hash[value]) { return hash[value]; } else { return ''; }
            };
          }

          if (widget.categoryAxis && widget.categoryAxis.labelTemplate) {
            widget.categoryAxis.labelFunction = function(value) {
              template = Handlebars.compile(widget.categoryAxis.labelTemplate);
              return template({ category: value });
            };
          }

          if (widget.graphs) {
            widget.graphs = widget.graphs.map(function(g) {
              if (g.balloonTextTemplate) {
                g.balloonFunction = function(item, graph) {
                  const vars = { item, graph };
                  Object.keys(item.dataContext).forEach(k => vars[k] = item.dataContext[k]);
                  if (!vars.value && graph.valueField) {
                    vars.value = item.dataContext[graph.valueField];
                  }

                  // handles cases where a layered graph was collapsed down into a single one
                  if (!vars['0_value'] && graph.valueField) {
                    vars['0_value'] = item.dataContext[graph.valueField];
                  }
                  if (!vars['1_value'] && graph.valueField) {
                    vars['1_value'] = item.dataContext[graph.valueField];
                  }

                  return Handlebars.compile(g.balloonTextTemplate)(vars);
                };
              }

              return g;
            });
          }

          const mergedData = lodashMerge(widget, options);
          if (!widget.dataProvider) {
            if (options.allGraphs && widget.graphs) {
              widget.graphs = widget.graphs.map(function(g) {
                g = lodashMerge(g, options.allGraphs);
                return g;
              });
            }
            if (options.allValueAxis && widget.valueAxis) {
              widget.valueAxis = widget.valueAxis.map(function(va) {
                va = lodashMerge(va, options.allValueAxis);
                return va;
              });
            }
          }

          if (window.DP_DEBUG) {
            console.log(`--- WidgetID: ${scope.widgetId} ---`);
            console.log(mergedData);
          }

          chart = new AmCharts.makeChart(`ch${scope.widgetId}`, mergedData);

          chartDiv.height(chartParent.height() - chartHeader.outerHeight());
          chart.validateData();

          if (options.click_url != null) {

            let eventType;
            if (widget.type === 'pie') {
              eventType = 'clickSlice';
              dataItem = 'dataItem';
            } else {
              eventType = 'clickGraphItem';
              dataItem = 'item';
            }

            const vars = {};
            const matches = options.click_url.match(/\$\{([a-zA-z0-9_]+)\}/);
            for (let index = 0; index < matches.length; index++) {
              const match = matches[index];
              if ((index % 2) === 1) {
                vars[match] = matches[index - 1];
              }
            }

            chart.addListener(eventType, function(event) {
              let url = options.click_url;
              for (let key in vars) {
                const variable = vars[key];
                if (event[dataItem].dataContext[key]) {
                  url = url.replace(variable, event[dataItem].dataContext[key]);
                }
              }
              return window.open(url);
            });

          } else if (widget.multiplePies != null) {
            const defaultDataProvider = widget.dataProvider;
            chart.addListener("clickSlice", function(event) {
              let selected;
              if (event.dataItem.dataContext.id !== undefined) {
                selected = event.dataItem.dataContext.id;
              } else {
                selected = undefined;
              }
              if (selected != null) {
                const data = [];
                angular.forEach(defaultDataProvider, function(element, index) {
                  if (index === selected) {
                    return angular.forEach(widget.pies[selected].dataProvider, function(pie) {
                      pie.color = `#${Math.floor(Math.random()*16777215).toString(16)}`;
                      return data.push(pie);
                    });
                  } else {
                    return data.push(element);
                  }
                });
                chart.dataProvider = data;
              } else {
                chart.dataProvider = defaultDataProvider;
              }
              return chart.validateData();
            });
          }

          let width = chartParent.height();
          let height = chartParent.width();

          return interval = setInterval( 
            function() {
              const w = chartParent.width();
              const h = chartParent.height();

              if ((h !== height) || (width !== w)) {
                chartDiv.height(chartParent.height() - chartHeader.outerHeight());
                chart.invalidateSize();

                width = w;
                return height = h;
              }
            }
          , 1000);
        };

        return initChart();
      }
    })
  
  ];

  return Reports_Directive_Amcharts;
});
function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}