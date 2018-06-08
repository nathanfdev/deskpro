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
  id => dispatch => new Promise(resolve => repository('Reports')
    .load(id)
    .success((response) => {
      dispatch(addToCollection('Reports', 'all', { [response.data.id]: response.data }, [response.data.id]));
      resolve(response.data);
    })
));

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
  (reportId, report) => {
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
        select:   report.select,
        from:     report.from,
        where:    report.where,
        split_by: report.split_by,
        group_by: report.group_by,
        order_by: report.order_by,
        limit:    report.limit,
        offset:   report.offset
      };
    }

    return new Promise(resolve => api
      .sendPost(`DP_API/report_widgets/test/${reportId}?include=rendered_result&inline_sideloads=1`, data)
      .success(response => resolve(response.data))
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

export const newReport = createAction(
  'REPORTS_NEW_REPORT',
  report => new Promise((resolve) => {
    let toClone = report;
    if (!report) {
      toClone = {};
    }
    const newReportObject = {
      id:            0,
      unique_key:    '',
      title:         '',
      description:   '',
      query:         toClone.query || '',
      labels:        [],
      display_order: 10,
      display_types: [],
      variables:     toClone.variables || [],
      query_parts:   toClone.query_parts || {
        select:      '',
        from:        '',
        where:       '',
        split_by:    '',
        group_by:    '',
        order_by:    '',
        with_rollup: false,
        limit:       '',
        offset:      ''
      },
      is_custom: true,
      is_new:    true
    };
    return resolve(newReportObject);
  })
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

export const cloneReport = createAction(
  'REPORTS_CLONE_REPORT',
  report => (dispatch) => {
    dispatch(newReport(report));
  }
);
