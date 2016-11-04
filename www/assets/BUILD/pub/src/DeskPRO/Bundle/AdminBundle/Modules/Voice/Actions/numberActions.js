import { createAction } from 'DeskPRO/Component/Ampliflux';
import Immutable from 'immutable';
import toastr from 'toastr';
import { repository, api } from 'DeskPRO/Bundle/AppBundle/DAL';
import { compileParams } from 'DeskPRO/Bundle/AppBundle/DAL/Http/Helpers';
import { loadAll, addToCollection, updateCollection } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const changeExistingNumbersFilter = createAction('VOICE_CHANGE_EXISTING_NUMBERS_FILTER');
export const changeAvailableNumbersFilter = createAction('VOICE_CHANGE_AVAILABLE_NUMBERS_FILTER');
export const expandNumber = createAction('VOICE_EXPAND_NUMBER');
export const collapseNumber = createAction('VOICE_COLLAPSE_NUMBER');

export const loadNumbers = createAction(
  'VOICE_LOAD_NUMBERS',
  () => dispatch => dispatch(loadAll('VoiceNumber'))
);

export const editNumber = createAction(
  'VOICE_EDIT_NUMBER',
  (id, data) => dispatch => repository('VoiceNumber').update(data, id).success(() => {
    const number = Immutable.fromJS({ ...data, id });
    dispatch(updateCollection('VoiceNumber', Immutable.List([number]), 'merge'));
  })
);

export const loadExistingNumbers = createAction(
  'VOICE_LOAD_EXISTING_NUMBERS',
  (accountId, page = 0) => api.sendGet(`DP_API/voice_accounts/${accountId}/existing_numbers?page=${page}`)
);

export const addExistingNumber = createAction(
  'VOICE_ADD_EXISTING_NUMBER',
  number => (dispatch) => {
    const data = {
      sid:          number.get('sid'),
      account:      number.get('account'),
      number:       number.get('number'),
      nickname:     number.get('nickname'),
      country_code: number.get('country_code')
    };

    return repository('VoiceNumber').create(data).success((response) => {
      dispatch(addToCollection('VoiceNumber', 'all', Immutable.List([Immutable.fromJS(response.data)])));
      dispatch(expandNumber(response.data.id));
    }).error((response) => {
      if (response.errors && response.errors.errors && response.errors.errors[0]) {
        toastr.error(response.errors.errors[0].message);
      }
    });
  }
);

export const loadAvailableNumbers = createAction(
  'VOICE_LOAD_AVAILABLE_NUMBERS',
  (accountId, params) => api.sendGet(`DP_API/voice_accounts/${accountId}/available_numbers?${compileParams(params)}`)
);

export const addAvailableNumber = createAction(
  'VOICE_ADD_AVAILABLE_NUMBER',
  number => dispatch => api.sendPost(`DP_API/voice_accounts/${number.get('account')}/buy_number`, {
    number: number.get('number')
  }).success((response) => {
    dispatch(addToCollection('VoiceNumber', 'all', Immutable.List([Immutable.fromJS(response.data)])));
    dispatch(expandNumber(response.data.id));
  }).error((response) => {
    if (response.errors && response.errors.errors && response.errors.errors[0]) {
      toastr.error(response.errors.errors[0].message);
    }
  })
);
