import { createAction } from 'DeskPRO/Component/Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadCustomReports = createAction(
  'REPORTS_LOAD_CUSTOM_REPORTS',
  () => () => new Promise(resolve => api
    .sendGet(
    'DP_API_OLD/reports/widget/custom',
    {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    })
    .success(response => resolve(response.reports))
));

export const loadBuiltInReports = createAction(
  'REPORTS_LOAD_BUILT_IN_REPORTS',
  () => () => new Promise(resolve => api
    .sendGet(
    'DP_API_OLD/reports/widget/builtIn',
    {
      headers: {
        'X-DeskPRO-API-Token':     window.DP_API_TOKEN,
        'X-DeskPRO-Session-ID':    window.DP_SESSION_ID,
        'X-DeskPRO-Request-Token': window.DP_REQUEST_TOKEN,
      }
    })
    .success(response => resolve(response.reports))
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
