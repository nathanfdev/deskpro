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
