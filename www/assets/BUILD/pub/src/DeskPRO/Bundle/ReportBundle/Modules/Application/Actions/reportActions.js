import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection, addToCollection, removeFromCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { transformReportDataToApi } from '../../Stats/Components/helper';

export const reportsLoaded = createAction('REPORTS_LOADED');
export const addLabels = createAction('REPORTS_ADD_LABELS');

export const loadReports = createAction(
  'REPORTS_LOAD_REPORTS',
  () => dispatch => repository('Reports')
    .loadAll()
    .success((response) => {
      dispatch(setCollection('Reports', 'all', response.data));
      dispatch(reportsLoaded());

      const labels = [];
      response.data.forEach((report) => {
        report.labels.forEach((label) => {
          labels.push(label);
        });
      });

      dispatch(addLabels(labels));
    })
);

export const loadReport = createAction(
  'REPORTS_LOAD_REPORT',
  id => (dispatch) => {
    const promise = repository('Reports').load(id);
    promise.success((response) => {
      dispatch(addToCollection('Reports', 'all', { [response.data.id]: response.data }, [response.data.id]));
    });

    return promise;
  }
);

export const deleteReport = createAction(
  'REPORTS_DELETE_REPORT',
  id => (dispatch) => {
    const promise = repository('Reports').remove(id);
    promise.success(() => {
      dispatch(removeFromCollection('Reports', 'all', [id]));
    });

    return promise;
  }
);

export const runReport = createAction(
  'REPORTS_RUN_REPORT',
  (reportId, report) => (dispatch) => {
    const data = {
      display_types: report.display_types,
      variables:     report.vars,
      input_mode:    report.extended_query ? 'dpql' : 'form',
      title:         report.title,
      labels:        report.labels
    };
    if (report.extended_query) {
      data.query = report.query;
    } else {
      data.query_parts = {
        select:      report.select,
        from:        report.from,
        where:       report.where,
        split_by:    report.split_by,
        group_by:    report.group_by,
        with_rollup: report.with_rollup,
        order_by:    report.order_by,
        limit:       report.limit,
        offset:      report.offset
      };
    }

    const promise = api.sendPost(`DP_API/report_widgets/test/${reportId}?include=rendered_result,reports,report_dashboard&inline_sideloads=1`, data);
    promise.success((response) => {
      if (response.data.reports) {
        const reports = {};
        const ids = [];
        const dashboards = {};
        const dashboardIds = [];
        response.data.reports.forEach((item) => {
          reports[item.id] = item;
          ids.push(item.id);
          if (response.linked.report_dashboard[item.dashboard]) {
            dashboards[item.dashboard] = response.linked.report_dashboard[item.dashboard];
            dashboardIds.push(item.dashboard);
          }
        });
        dispatch(addToCollection('DashboardReports', 'all', reports, ids));
        dispatch(addToCollection('Dashboards', 'all', dashboards, dashboardIds));
      }
    });
    return promise;
  });

export const downloadReport = createAction(
  'REPORTS_DOWNLOAD_REPORT',
  (reportId, report, type) => {
    const data = {
      display_types: report.display_types,
      variables:     report.vars,
      input_mode:    report.extended_query ? 'dpql' : 'form',
      title:         report.title,
    };
    if (report.extended_query) {
      data.query = report.query;
    } else {
      data.query_parts = {
        select:      report.select,
        from:        report.from,
        where:       report.where,
        split_by:    report.split_by,
        group_by:    report.group_by,
        with_rollup: report.with_rollup,
        order_by:    report.order_by,
        limit:       report.limit,
        offset:      report.offset
      };
    }

    api.sendPost(`DP_API/report_widgets/download/${reportId}/${type}`, data).success(
      (response) => {
        window.location = `/api/v2/report_widgets/download/generated/${response.data.auth}`;
      }
    );
  });

export const loadGroupParams = createAction(
  'REPORTS_LOAD_GROUP_PARAMS',
  () => new Promise(resolve => api.sendGet('DP_API/report_widgets/group-params').success(response => resolve(response)))
);

export const saveReport = createAction(
  'REPORTS_SAVE_REPORT',
  report => (dispatch) => {
    const data = transformReportDataToApi(report);

    let promise;
    if (report.id > 0) {
      promise = api.sendPut(`DP_API/report_widgets/${report.id}?follow_location=1`, data);
    } else {
      promise = api.sendPost('DP_API/report_widgets', data);
    }

    return promise.success((response) => {
      if (response.data.id) {
        dispatch(loadReport(response.data.id));
        const labels = [];
        response.data.labels.forEach((label) => {
          labels.push(label);
        });

        dispatch(addLabels(labels));

        return response;
      }
      return response;
    });
  }
);

export const saveAndRun = createAction(
  'REPORTS_SAVE_AND_RUN_REPORT',
  data => (dispatch) => {
    const promise = dispatch(saveReport(data));
    return promise.success((response) => {
      if (response.data.id) {
        dispatch(runReport(response.data.id, data));
      }
    });
  }
);
