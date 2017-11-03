import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const getCountries = createAction(
  'EXTEND_GET_COUNTRIES',
  () => api.sendGet('DP_API/cloud/countries', { dataType: 'json' })
);
export const getStates = createAction(
  'EXTEND_GET_STATES',
  () => api.sendGet('DP_API/cloud/states', { dataType: 'json' })
);
export const getEuCountries = createAction(
  'EXTEND_GET_STATES',
  () => api.sendGet('DP_API/cloud/eu_countries', { dataType: 'json' })
);
export const forgotPassword = createAction(
  'EXTEND_FORGOT_PASSWORD',
  params => api.sendPost('DP_API/cloud/forgot_password', params)
);
export const extendTrial = createAction(
  'EXTEND_SUBMIT',
  params => api.sendPost('DP_API/cloud/submit_detail', params)
);
export const preserveData = createAction(
  'EXTEND_PRESERVE_DATA',
  () => api.sendPost('DP_API/cloud/preserve_data')
);
export const deleteFeedback = createAction(
  'EXTEND_DELETE_FEEDBACK',
  params => api.sendPost('DP_API/cloud/delete_feedback', params)
);
export const submitQuestion = createAction(
  'EXTEND_SUBMIT_QUESTION',
  params => api.sendPost('DP_API/cloud/question', params)
);
export const resetTrial = createAction(
  'EXTEND_RESET_TRIAL',
  () => api.sendPost('DP_API/cloud/reset_trial')
);
