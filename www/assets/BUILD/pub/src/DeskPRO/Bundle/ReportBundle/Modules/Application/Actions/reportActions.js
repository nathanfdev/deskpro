import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { setCollection, addToCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const reportsLoaded = createAction(
  'REPORTS_LOADED'
);

export const loadReports = createAction(
  'REPORTS_LOAD_REPORTS',
  () => dispatch => repository('Reports')
    .loadAll()
    .success((response) => {
      dispatch(setCollection('Reports', 'all', response.reports));
      dispatch(setCollection('ReportsLabels', 'all', response.labels));
      dispatch(reportsLoaded());
    })
);

export const loadReport = createAction(
  'REPORTS_LOAD_REPORT',
  reportId => dispatch => new Promise(resolve => api
    .sendGet(
    `DP_API_OLD/reports/widget/${reportId}`,
    {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    })
    .success((response) => {
      dispatch(addToCollection('Reports', 'all', { [response.id]: response }, [response.id]));
      return resolve(response);
    })
));

export const runReport = createAction(
  'REPORTS_RUN_REPORT',
  (reportId, data) => (dispatch) => {
    const dataToSend = {
      report: {
        title:         data.title,
        description:   data.desc,
        display_types: data.display_types,
        variables:     data.vars,
        labels:        data.labels,
      },
      parts: {
        select:  data.select,
        from:    data.from,
        where:   data.where,
        splitBy: data.splitBy,
        groupBy: data.groupBy,
        orderBy: data.orderBy,
        limit:   data.limit,
        offset:  data.offset
      }
    };

    return new Promise(resolve => api
      .sendPost(
      `DP_API_OLD/reports/widget/test/${reportId}`,
      dataToSend,
      {
        headers: {
          'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
          'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
          'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
        }
      })
      .success((response) => {
        dispatch(addToCollection('Reports', 'all', { [response.id]: response }, [response.id]));
        return resolve(response);
      })
    );
  });

export const saveAndRun = createAction(
  'REPORTS_SAVE_AND_RUN_REPORT',
  data => (dispatch) => {
    const dataToSend = {
      report: {
        title:         data.title,
        description:   data.desc,
        display_types: data.display_types,
        variables:     data.vars,
        labels:        data.labels,
      },
      parts: {
        select:  data.select,
        from:    data.from,
        where:   data.where,
        splitBy: data.splitBy,
        groupBy: data.groupBy,
        orderBy: data.orderBy,
        limit:   data.limit,
        offset:  data.offset
      },
      displayOnly: data.displayOnly,
    };

    const config = {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    };

    const promise = api.sendPost(`DP_API_OLD/reports/widget/${data.id}`, dataToSend, config);
    return promise.success((response) => { if (response.id) { dispatch(runReport(response.id, data)); } });
  }
);

export const loadGroupParams = createAction(
  'REPORTS_LOAD_GROUP_PARAMS',
  () => () => new Promise(resolve => api
    .sendGet(
    'DP_API_OLD/reports/widget/group-params',
    {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    })
    .success(response => resolve(response))
));

export const saveReport = createAction(
  'REPORTS_SAVE_REPORT',
  data => (dispatch) => {
    const dataToSend = {
      report: {
        title:         data.title,
        description:   data.desc,
        display_types: data.display_types,
        variables:     data.vars,
        labels:        data.labels,
      },
      parts: {
        select:  data.select,
        from:    data.from,
        where:   data.where,
        splitBy: data.splitBy,
        groupBy: data.groupBy,
        orderBy: data.orderBy,
        limit:   data.limit,
        offset:  data.offset
      },
      displayOnly: data.displayOnly,
    };

    const config = {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    };

    let promise;
    if (data.id > 0) {
      promise = api.sendPost(`DP_API_OLD/reports/widget/${data.id}`, dataToSend, config);
    } else {
      promise = api.sendPut('DP_API_OLD/reports/widget', dataToSend, config);
    }

    return promise.success((response) => { if (response.id) { dispatch(loadReport(response.id)); } });
  }
);

export const newReport = createAction(
  'REPORTS_NEW_REPORT',
  () => new Promise((resolve) => {
    const newReportObject = {
      id:            0,
      unique_key:    '',
      title:         'new report',
      description:   '',
      query:         '',
      labels:        [],
      display_order: 10,
      display_types: [],
      variables:     [],
      query_parts:   {
        display:    ['TABLE', 'BAR'],
        select:     '',
        from:       '',
        where:      '',
        splitBy:    '',
        groupBy:    '',
        orderBy:    '',
        withRollup: false,
        limit:      '',
        offset:     ''
      },
      is_custom: true,
      is_new:    true,
    };
    return resolve(newReportObject);
  })
);
