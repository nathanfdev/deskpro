import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { loadAll, addToCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const changeExistingNumbersFilter = createAction('TWILIO_CHANGE_EXISTING_NUMBERS_FILTER');
export const changeAvailableNumbersFilter = createAction('TWILIO_CHANGE_AVAILABLE_NUMBERS_FILTER');
export const expandNumber = createAction('TWILIO_EXPAND_NUMBER');
export const collapseNumber = createAction('TWILIO_COLLAPSE_NUMBER');

export const loadNumbers = createAction(
  'TWILIO_LOAD_NUMBERS',
  () => dispatch => dispatch(loadAll('TwilioNumber'))
);

export const editNumber = createAction(
  'TWILIO_EDIT_NUMBER',
  (id, data) => dispatch => repository('TwilioNumber').update(data, id).success(() => {
    const number = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('TwilioNumber', Immutable.List([number]), 'merge'));
  })
);

export const loadExistingNumbers = createAction(
  'TWILIO_LOAD_EXISTING_NUMBERS',
  (accountId, page = 0) => api.sendGet(`DP_API/twilio_accounts/${accountId}/existing_numbers?page=${page}`)
);

export const addExistingNumber = createAction(
  'TWILIO_ADD_EXISTING_NUMBER',
  number => (dispatch) => {
    const data = {
      sid:          number.get('sid'),
      account:      number.get('account'),
      number:       number.get('number'),
      nickname:     number.get('nickname'),
      country_code: number.get('country_code')
    };

    return repository('TwilioNumber').create(data).success((response) => {
      dispatch(addToCollection('TwilioNumber', 'all', Immutable.List([Immutable.fromJS(response.data)])));
      dispatch(expandNumber(response.data.id));
    });
  }
);

export const loadAvailableNumbers = createAction(
  'TWILIO_LOAD_AVAILABLE_NUMBERS',
  (accountId, params) => api.sendGet(`DP_API/twilio_accounts/${accountId}/available_numbers?${compileParams(params)}`)
);

export const addAvailableNumber = createAction(
  'TWILIO_LOAD_AVAILABLE_NUMBERS',
  number => (dispatch) => {
    const data = {
      sid:          number.get('number'), // fake sid for now
      account:      number.get('account'),
      number:       number.get('number'),
      nickname:     number.get('nickname'),
      country_code: number.get('country_code')
    };

    return repository('TwilioNumber').create(data).success((response) => {
      dispatch(addToCollection('TwilioNumber', 'all', Immutable.List([Immutable.fromJS(response.data)])));
      dispatch(expandNumber(response.data.id));
    });
  }
);
