import { createAction } from 'Ampliflux';
import { api } from 'DeskPRO/Bundle/AppBundle/DAL';

export const getCountries = createAction(
  'EXTEND_GET_COUNTRIES',
  () =>
    api.sendGet('/cloud/api/countries', { dataType: 'json' })
);
export const getStates = createAction(
  'EXTEND_GET_STATES',
  () =>
    api.sendGet('/cloud/api/states', { dataType: 'json' })
);
export const getEuCountries = createAction(
  'EXTEND_GET_STATES',
  () =>
    api.sendGet('/cloud/api/eu_countries', { dataType: 'json' })
);
export const forgotPassword = createAction(
  'EXTEND_FORGOT_PASSWORD',
  params =>
    api.sendPost('/cloud/api/forgot_password', params)
);
export const extendTrial = createAction(
  'EXTEND_SUBMIT',
  params =>
    api.sendPost('/cloud/api/submit_detail', params)
);
export const preserveData = createAction(
  'EXTEND_PRESERVE_DATA',
  () =>
    api.sendPost('/cloud/api/preserve_data')
);
