import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadReports = createAction(
  'REPORTS_LOAD_REPORTS',
  () => () => new Promise(resolve => api
    .sendGet(
    'DP_API_OLD/dashboards/widgets/reports/list',
    {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    })
    .success(response => resolve(response))
  ));

export const loadReport = createAction(
  'REPORTS_LOAD_REPORT',
  reportId => () => new Promise(resolve => api
    .sendGet(
    `DP_API_OLD/reports/widget/${reportId}`,
    {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    })
    .success(response => resolve(response))
));

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
  data => () => new Promise((resolve) => {
    const dataToSend = {
      report: {
        title:         data.title,
        description:   data.description,
        display_types: data.display_types,
        variables:     data.vars,
      },
      parts: {
        select:  data.select,
        from:    data.from,
        where:   data.from,
        splitBy: data.splitBy,
        groupBy: data.groupBy,
        orderBy: data.orderBy,
        limit:   data.limit,
        offset:  data.offset
      }
    };

    const config = {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    };

    let promise;
    if (data.id) {
      promise = api.sendPost(`DP_API_OLD/reports/widget/${data.id}`, dataToSend, config);
    } else {
      promise = api.sendPut('DP_API_OLD/reports/widget', dataToSend, config);
    }

    return promise.success(response => resolve(response));
  }
));
