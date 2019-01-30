/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['DeskPRO/Util/Arrays'], function(Arrays) {
  let DashboardWidgetService;
  return (DashboardWidgetService = class DashboardWidgetService {

    constructor(Api, Api2, $q) {
      this.Api = Api;
      this.Api2 = Api2;
      this.$q = $q;
      this.data = {};
      this.storage = {reports: [], labels: [], reportsByLabels: {}};
      this.groupParams = [];
      this.widgets = {};
      this.widgetsResults = {};
    }

    loadGroupParams() {
      return this.Api2.sendGet('report_widgets/group-params').then(response => {
        return this.groupParams = response.data;
      });
    }

    getIndexById(storage, id) {
      let index = -1;
      index = Arrays.findIndex(storage,
        function(v) {
          if ((v != null) && (v.id === id)) {
            return true;
          }
      });
      return index;
    }

    updateWidgetsSize(widgets, cols) {
      const ws = [];
      for (let i = 0; i < widgets.length; i++) { const widget = widgets[i]; if ((widget !== 'last') && (widget.size_x > cols)) { ws.push(widget); } }
      return ws;
    }

    getReports() {
      const deferred = this.$q.defer();
      if (this.storage.reports.length === 0) {
        return this.Api2
          .sendGet("/report_widgets")
          .then(result => {
            this.storage.reports = result.data.data;
            this.storage.labels = [];
            for (let report of Array.from(result.data.data)) {
              for (let label of Array.from(report.labels)) {
                if (this.storage.labels.indexOf(label) === -1) {
                  this.storage.labels.push(label);
                }
              }
            }
            deferred.resolve(this.storage);
            return deferred.promise;
        });
      } else {
        deferred.resolve(this.storage);
        return deferred.promise;
      }
    }

    isActiveLabel(storage, label) {
      let index = -1;
      index = Arrays.findIndex(storage,
        function(v) {
          if ((v != null) && (v === label)) { return true; }
      });
      if (index > 0) {
        return true;
      } else {
        return false;
      }
    }

    saveWidget(widget) {
      return this.Api2.sendPutJson( 
        `/dashboard_report_widgets/${widget.id}`,
        {
          "size_x":  widget.sizeX,
          "size_y":  widget.sizeY,
          "col":     widget.col,
          "row":     widget.row,
          "title":   widget.title,
          "options": widget.options
        });
    }

    setDashboardService(service) {
      return this.dashboardService = service;
    }

    addWidget(report, widget) {
      const widgetVars = [];
      for (let name in widget.variables) {
        const variable = widget.variables[name];
        variable.name = name;
        widgetVars.push(variable);
      }

      const url = "/dashboard_report_widgets";
      const data = {
        title: widget.title,
        type: widget.type,
        col: widget.col,
        row: widget.row,
        size_x: widget.sizeX,
        size_y: widget.sizeY,
        report: report.id,
        widget_variables: widgetVars
      };

      if (widget.widget_id === 'advanced') {
        data.js_code = widget.js_code;
      } else {
        data.widget = widget.widget_id;
      }

      const deferred = this.$q.defer();

      this.Api2
      .sendPostJson(url, data)
      .then(response => {
        return deferred.resolve(response.data.data);
    }).catch(response => {
        return deferred.reject(response.data);
      });

      return deferred.promise;
    }


    testWidget(reportWidget) {
      const url = `/report_widgets/test/${reportWidget.id}?include=rendered_result,report_widget&inline_sideloads=1`;
      const dataToSend = {
        title:         'test widget',
        display_types: reportWidget.display_types,
        variables:     reportWidget.variables,
        input_mode:    'form',
        query_parts:   reportWidget.query_parts
      };

      return this.Api2
        .sendPostJson(url, dataToSend);
    }


    removeWidget(widget) {
      return this.Api2.sendDelete(`/dashboard_report_widgets/${widget.id}`);
    }

    getWidgets(reportId) {
      const deferred = this.$q.defer();
      this.widgetsResults = {};

      this.Api2
      .sendGet(`/dashboard_reports/${reportId}/widgets?include=report_widget&inline_sideloads=1`)
      .then(resp => {
        const widgets = resp.data.data;

        for (var widget of Array.from(widgets)) {
          widget.sizeX = widget.size_x;
          widget.sizeY = widget.size_y;

          this.widgetsResults[widget.id] = this.$q.defer();
        }

        // load widget rendered results in batches
        const widgetIds = widgets.map(widget => widget.id);
        const idBatches = ((() => {
          const result = [];
          while (widgetIds.length) {
            result.push(widgetIds.splice(0, 10));
          }
          return result;
        })());
        for (let idBatch of Array.from(idBatches)) {
          this.Api2
            .sendGet(`/dashboard_reports/${reportId}/widgets?include=rendered_result,report_widget&inline_sideloads=1&ids=${idBatch}`)
            .then(batchResp => {
              const batchWidgets = batchResp.data.data;
              return (() => {
                const result1 = [];
                for (widget of Array.from(batchWidgets)) {
                  result1.push(this.widgetsResults[widget.id].resolve(widget.rendered_result));
                }
                return result1;
              })();
          });
        }

        return deferred.resolve(widgets);
      });

      return deferred.promise;
    }

    getWidget(id) {
      const deferred = this.$q.defer();

      this.Api2
        .sendGet(`/dashboard_report_widgets/${id}?include=rendered_result,report_widget&inline_sideloads=1`)
        .then(resp => {
          const widget = resp.data.data;
          widget.sizeX = widget.size_x;
          widget.sizeY = widget.size_y;

          if ((widget.rendered_result == null) || (widget.rendered_result === false) || (widget.rendered_result === '')) {
            widget.rendered_result = null;
          }

          this.widgetsResults[widget.id].resolve(widget.rendered_result);

          return deferred.resolve(widget);
      });
      return deferred.promise;
    }
  });
});
